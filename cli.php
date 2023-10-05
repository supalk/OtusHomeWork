<?php

require __DIR__.'/vendor/autoload.php';

use App\Command;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;
use Slim\App;

$dotenv = Dotenv::createImmutable(dirname(__FILE__));
$dotenv->safeLoad();

$application = new Application();

$containerBuilder = new ContainerBuilder();
// Add DI container definitions
$containerBuilder->addDefinitions(config_path( 'container.php'));

// Create DI container instance
$container = $containerBuilder->build();

// Create Slim App instance
$app = $container->get(App::class);

// Тестирование
$application->add(new Command\Test());
// Создание таблиц
$application->add(new Command\CreateTables($app->getContainer()));
// Генерация 1 млн Анкет пользователей
$application->add(new Command\FillingUsers($app->getContainer()));
// Создание индексов
$application->add(new Command\CreateIndex($app->getContainer()));
// Удаление индексов
$application->add(new Command\DeleteIndex($app->getContainer()));


$application->run();
