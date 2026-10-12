<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Serve From The Project Root
|--------------------------------------------------------------------------
|
| When the whole project folder is published (e.g. http://host/bcs/) the
| root .htaccess rewrites requests into public/, so SCRIPT_NAME becomes
| "/bcs/public/index.php" while the URL has no "/public". Point it at the
| project root so Laravel detects "/bcs" as the base URL.
|
*/

if (isset($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'])) {
    $publicPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $requestPath = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');

    if (substr($publicPath, -7) === '/public'
        && $requestPath !== $publicPath
        && strpos($requestPath.'/', $publicPath.'/') !== 0) {
        $_SERVER['SCRIPT_NAME'] = substr($publicPath, 0, -7).'/index.php';
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    }

    unset($publicPath, $requestPath);
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
