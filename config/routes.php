<?php

use App\Middleware\AppMiddleware;
use App\Middleware\AuthMiddleware;
use Slim\App;
//use Slim\Routing\RouteCollectorProxy;


return function (App $app) {

    $app->add(new AppMiddleware());
//    $app->group('/users', function (RouteCollectorProxy $group) {
//
//    });

    $app->post('/login', \App\Action\UserAction::class.":login")
        ->setName('login');

    $app->post('/user/register', \App\Action\UserAction::class.":register")
        ->setName('user.register');

    $app->get('/user/get[/{id:[0-9]+}]', \App\Action\UserAction::class.":getItem")
        ->add(AuthMiddleware::class)
        ->setName('user.get');

    $app->get('/', \App\Action\HomeAction::class);
    $app->get('/start', \App\Action\StartAction::class);

};
