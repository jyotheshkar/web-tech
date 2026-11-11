<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require dirname(__DIR__) . '/security.php';
require dirname(__DIR__) . '/uploads.php';
$checks = 0;
function check($condition, string $description): void {
    global $checks;
    if (!$condition) throw new RuntimeException($description);
    $checks++;
}
function rejects(callable $operation, string $description): void {
    try { $operation(); }
    catch (InvalidArgumentException | RuntimeException $error) { check(true, $description); return; }
    throw new RuntimeException($description);
}
$token = csrf_token();
check(strlen($token) === 64 && ctype_xdigit($token), 'Random CSRF token contract');
check(csrf_token() === $token, 'Token stable within a session');
check(csrf_matches($token), 'Matching token accepted');
foreach ([null, '', 0, [], [$token], str_repeat('f', 63), $token . 'x'] as $candidate) {
    check(!csrf_matches($candidate), 'Malformed or mismatched token rejected');
}
check(str_contains(csrf_field(), 'name="_csrf"'), 'Hidden field uses agreed request name');
$directory = sys_get_temp_dir() . '/friendzone-contract-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$path = $directory . '/fixture.png';
try {
    $image = imagecreatetruecolor(2, 2);
    imagepng($image, $path);
    imagedestroy($image);
    check(inspect_image_file($path) === [2, 2, 'image/png'], 'Actual raster MIME accepted');
    $_FILES['test'] = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $path, 'name' => 'evil.php.png'];
    rejects(fn() => store_uploaded_image('test'), 'Ordinary local files cannot impersonate HTTP uploads');
    $_FILES['test'] = ['error' => UPLOAD_ERR_NO_FILE];
    check(store_uploaded_image('test') === 'images/default.png', 'Optional upload supports text-only posts');
    $_FILES['test'] = 'malformed';
    rejects(fn() => store_uploaded_image('test'), 'Malformed multipart metadata rejected');
    file_put_contents($path, '<?php echo "bad"; ?>');
    rejects(fn() => inspect_image_file($path), 'Renaming executable text does not make an image');
    file_put_contents($path, str_repeat('x', MAX_IMAGE_BYTES + 1));
    rejects(fn() => inspect_image_file($path), 'Byte ceiling enforced from real temporary-file size');
    $image = imagecreatetruecolor(MAX_IMAGE_DIMENSION + 1, 1);
    imagepng($image, $path);
    imagedestroy($image);
    rejects(fn() => inspect_image_file($path), 'Decoded dimensions are bounded');
    putenv('UPLOAD_DIRECTORY=' . dirname(__DIR__));
    rejects(fn() => upload_directory(), 'Web-root upload storage rejected');
    putenv('UPLOAD_DIRECTORY=' . $directory);
    check(upload_directory() === realpath($directory), 'Private external storage accepted');
} finally {
    unlink($path); rmdir($directory); putenv('UPLOAD_DIRECTORY');
}
echo "Passed $checks PHP security contracts\n";
