<?php

require __DIR__.'/../vendor/autoload.php';

global $caRoot;
$caRoot = __DIR__.'/..';

if (file_exists($caRoot.'/vendor/autoload.php')) {
    $autoload = include_once $caRoot.'/vendor/autoload.php';
} elseif (file_exists($caRoot.'/../../autoload.php')) {
    $autoload = include_once $caRoot.'/../../autoload.php';
} else {
    echo 'Something goes wrong with your archive'.PHP_EOL.'Try downloading again'.PHP_EOL;
    exit(1);
}

use Legovaer\CoverageRunner\Application;

$application = new Application();
$application->run();
