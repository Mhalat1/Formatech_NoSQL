<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charge les variables d'environnement pour les tests
if (file_exists(dirname(__DIR__).'/.env.test')) {
    (new Dotenv())->load(dirname(__DIR__).'/.env.test');
}

// Spécifique à Symfony
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env.test');
}