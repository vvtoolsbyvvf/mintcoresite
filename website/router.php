<?php
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path)) {
    http_response_code(400);
    exit;
}

$path = rawurldecode($path);
$segments = explode('/', $path);
if (
    str_contains($path, "\0")
    || str_contains($path, '\\')
    || in_array('..', $segments, true)
    || in_array('storage', $segments, true)
    || array_filter($segments, static fn(string $segment): bool => str_starts_with($segment, '.')) !== []
) {
    http_response_code(404);
    exit;
}

if ($path === '/bridge.php') {
    require __DIR__ . '/bridge.php';
    exit;
}

if ($path === '/') {
    $path = '/index.html';
}

$file = realpath(__DIR__ . $path);
$root = realpath(__DIR__);
if (
    $file === false
    || $root === false
    || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)
    || !is_file($file)
    || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php'
) {
    http_response_code(404);
    exit;
}

return false;
