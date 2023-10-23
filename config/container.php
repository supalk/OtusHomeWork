<?php

use App\Library\Auth;
use App\Library\DB;
use App\Library\DBRead;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },
    DB::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['db'];

        return new DB($config);
    },
    DBRead::class => function (ContainerInterface $container) {
        $config = $container->get('settings')['db-read'];

        return new DBRead($config);
    },
    Auth::class => function (ContainerInterface $container) {
        return new Auth($container);
    }
];
