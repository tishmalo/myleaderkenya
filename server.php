<?php

/**
 * Router script for the PHP built-in web server.
 *
 * Used by `php artisan serve` and by the Playwright e2e suite. Serving through
 * this script keeps the public directory as the document root, so Vite build
 * assets resolve while everything else falls through to Laravel.
 */

$publicPath = __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

$formattedDateTime = date('D M j:H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = ($_SERVER['REMOTE_ADDR'] ?? 'cli').':'.($_SERVER['REMOTE_PORT'] ?? '-');

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
