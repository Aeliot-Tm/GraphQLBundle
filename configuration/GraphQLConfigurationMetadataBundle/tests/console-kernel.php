<?php

declare(strict_types=1);

use Overblog\GraphQLConfigurationMetadataBundle\Tests\Stub\TestKernel;

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'test';
$debug = $_SERVER['APP_DEBUG'] ?? true;

return new TestKernel($env, (bool) $debug);
