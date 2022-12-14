<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;

require __DIR__ . '/bootstrap.php';

return new Application(require __DIR__ . '/console-kernel.php');
