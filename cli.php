<?php

require __DIR__.'/vendor/autoload.php';

use App\Command;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

$dotenv = Dotenv::createImmutable(dirname(__FILE__));
$dotenv->safeLoad();

$application = new Application();

// Тестирование
$application->add(new Command\Test());


$application->run();
