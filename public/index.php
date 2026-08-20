<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Let PHP's built-in development server serve public static assets directly.
// This keeps `php -S ... -t public public/index.php` compatible with Vite builds
// and local images while all application routes continue through Laravel.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $requestedFile = realpath(__DIR__.DIRECTORY_SEPARATOR.ltrim($requestPath, '/\\'));
    $publicPath = realpath(__DIR__);

    if (
        $requestedFile
        && $publicPath
        && str_starts_with($requestedFile, $publicPath.DIRECTORY_SEPARATOR)
        && is_file($requestedFile)
        && pathinfo($requestedFile, PATHINFO_EXTENSION) !== 'php'
    ) {
        return false;
    }
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
