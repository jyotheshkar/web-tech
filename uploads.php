<?php
// Re-encode accepted images rather than trusting client filenames or MIME declarations.
const MAX_IMAGE_BYTES = 2097152;
const MAX_IMAGE_PIXELS = 12000000;
const MAX_IMAGE_DIMENSION = 5000;

function inspect_image_file(string $path): array {
    clearstatcache(true, $path);
    $bytes = is_file($path) ? filesize($path) : false;
    if ($bytes === false || $bytes < 1 || $bytes > MAX_IMAGE_BYTES) {
        throw new InvalidArgumentException('Image must be between 1 byte and 2 MiB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $size = @getimagesize($path);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)
        || $size === false || ($size['mime'] ?? '') !== $mime) {
        throw new InvalidArgumentException('Only valid JPEG, PNG or GIF images are accepted.');
    }
    [$width, $height] = $size;
    if ($width < 1 || $height < 1 || $width > MAX_IMAGE_DIMENSION
        || $height > MAX_IMAGE_DIMENSION || $width * $height > MAX_IMAGE_PIXELS) {
        throw new InvalidArgumentException('Image dimensions exceed the allowed limit.');
    }
    return [$width, $height, $mime];
}

function upload_directory(bool $create = true): string {
    $configured = getenv('UPLOAD_DIRECTORY') ?: dirname(__DIR__) . '/friendzone-uploads';
    if (!is_dir($configured) && (!$create || !mkdir($configured, 0700, true))) {
        throw new RuntimeException('Upload storage is unavailable.');
    }
    $directory = realpath($configured);
    $webRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) ?: __DIR__;
    if ($directory === false || $directory === $webRoot
        || str_starts_with($directory, $webRoot . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('Upload storage must be outside the web root.');
    }
    return $directory;
}

// Track only files created in this PHP request; caller input cannot delete older media.
function &new_uploads(): array {
    static $created = [];
    return $created;
}

function discard_new_uploaded_image(string $uri): void {
    $created = &new_uploads();
    if (!isset($created[$uri])) return;
    $path = $created[$uri];
    unset($created[$uri]);
    if (is_file($path) && !is_link($path)) unlink($path);
}

function store_uploaded_image(string $field): string {
    if (!isset($_FILES[$field])) {
        return 'images/default.png';
    }
    if (is_array($_FILES[$field]) && ($_FILES[$field]['error'] ?? null) === UPLOAD_ERR_NO_FILE) {
        return 'images/default.png';
    }
    $upload = $_FILES[$field];
    if (!is_array($upload) || ($upload['error'] ?? null) !== UPLOAD_ERR_OK
        || !isset($upload['tmp_name']) || !is_string($upload['tmp_name'])
        || !is_uploaded_file($upload['tmp_name'])) {
        throw new InvalidArgumentException('Image upload was not completed.');
    }
    inspect_image_file($upload['tmp_name']);
    if (!function_exists('imagecreatefromstring')) {
        throw new RuntimeException('Image decoding support is unavailable.');
    }
    $image = @imagecreatefromstring(file_get_contents($upload['tmp_name']));
    if ($image === false) {
        throw new InvalidArgumentException('Image could not be decoded.');
    }
    $path = null;
    try {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $filename = bin2hex(random_bytes(16)) . '.png';
        $path = upload_directory() . DIRECTORY_SEPARATOR . $filename;
        $stream = fopen($path, 'xb');
        if ($stream === false) throw new RuntimeException('Unable to save image.');
        try {
            if (!imagepng($image, $stream)) throw new RuntimeException('Unable to encode image.');
        } finally { fclose($stream); }
        chmod($path, 0600);
        $uri = 'media.php?file=' . $filename;
        $created = &new_uploads();
        $created[$uri] = $path;
        return $uri;
    } catch (Throwable $error) {
        if ($path !== null && is_file($path)) unlink($path);
        throw $error;
    } finally { imagedestroy($image); }
}
