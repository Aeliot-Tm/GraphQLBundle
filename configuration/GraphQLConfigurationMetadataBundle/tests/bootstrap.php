<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test';
$paths = [sprintf('.env.%s.local', $env), sprintf('.env.%s', $env), '.env'];
$paths = array_map(static fn (string $x): string => dirname(__DIR__) . \DIRECTORY_SEPARATOR . $x, $paths);
$paths = array_filter($paths, static fn (string $x): bool => is_readable($x) && is_file($x));
$dotenv = new Dotenv('APP_ENV', 'APP_DEBUG');
array_walk($paths, static fn (string $x) => $dotenv->bootEnv($x, 'test'));
