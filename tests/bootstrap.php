<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$environment = $_SERVER['APP_ENV']
    ?? $_ENV['APP_ENV']
    ?? getenv('APP_ENV');

if ($environment !== 'test') {
    throw new RuntimeException(
        'Les tests PHPUnit doivent utiliser APP_ENV=test.',
    );
}
