<?php
// One session/CSRF contract for forms and JSON endpoints. No database is required.
function reject_request(string $message, int $status): void {
    http_response_code($status);
    $json = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
        || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    if ($json) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit;
}

function csrf_token(): string {
    if (!isset($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_matches($candidate): bool {
    return is_string($candidate) && isset($_SESSION['_csrf'])
        && is_string($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $candidate);
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function require_post_request(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        reject_request('POST required.', 405);
    }
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || getenv('SESSION_COOKIE_SECURE') === '1';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure,
        'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $candidate = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);
    if (!csrf_matches($candidate)) {
        reject_request('Invalid request token.', 403);
    }
}
