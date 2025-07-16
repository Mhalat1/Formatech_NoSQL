<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload.php';

// Au lieu d'utiliser autoload_runtime.php, faites ceci :
if (file_exists(dirname(__DIR__).'/.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'prod', (bool) ($_SERVER['APP_DEBUG'] ?? false));
$request = Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);