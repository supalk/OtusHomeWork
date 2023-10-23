<?php

use App\Action;
use App\Middleware\AppMiddleware;
use App\Middleware\AuthMiddleware;
use Slim\App;
//use Slim\Routing\RouteCollectorProxy;


return function (App $app) {

    $app->add(new AppMiddleware());
//    $app->group('/users', function (RouteCollectorProxy $group) {
//
//    });

    $app->post('/login', Action\UserAction::class.":login")
        ->setName('login');

    $app->post('/user/register', Action\UserAction::class.":register")
        ->setName('user.register');

    $app->get('/user/get[/{id:[0-9]+}]', Action\UserAction::class.":getItem")
        ->add(AuthMiddleware::class)
        ->setName('user.get');

    $app->get('/user/search', Action\UserAction::class.":searchUser")
      //  ->add(AuthMiddleware::class)
        ->setName('user.get');

    $app->get('/', Action\HomeAction::class);

};
