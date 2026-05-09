<?php

/**
 * Router for `php artisan serve`.
 *
 * Only serve real files from /public; directories (e.g. upload folders) must not
 * shadow Laravel routes. Base path is derived from this file so it does not depend on getcwd().
 */
$publicPath = realpath(__DIR__.'/public') ?: __DIR__.'/public';

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$requested = $publicPath.$uri;

if ($uri !== '/' && $uri !== '' && is_file($requested)) {
    return false;
}

$formattedDateTime = date('D M j H:i:s Y');

$requestMethod = $_SERVER['REQUEST_METHOD'];
$remoteAddress = $_SERVER['REMOTE_ADDR'].':'.$_SERVER['REMOTE_PORT'];

file_put_contents('php://stdout', "[$formattedDateTime] $remoteAddress [$requestMethod] URI: $uri\n");

require_once $publicPath.'/index.php';
