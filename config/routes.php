<?php

use App\Action;
use App\Middleware\AppMiddleware;
use App\Middleware\AuthMiddleware;
use Slim\App;
//use Slim\Routing\RouteCollectorProxy;


return function (App $app) {

    $app->add(new AppMiddleware());
//    $app->group('/api', function (RouteCollectorProxy $group) {
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
        ->add(AuthMiddleware::class)
        ->setName('user.search');

    $app->put('/friend/set/{id:[0-9]+}', Action\FriendAction::class.":set")
        ->add(AuthMiddleware::class)
        ->setName('friend.set');

    $app->put('/friend/delete/{id:[0-9]+}', Action\FriendAction::class.":delete")
        ->add(AuthMiddleware::class)
        ->setName('friend.delete');

    $app->post('/post/create', Action\PostAction::class.":create")
        ->add(AuthMiddleware::class)
        ->setName('post.create');

    $app->put('/post/update', Action\PostAction::class.":update")
        ->add(AuthMiddleware::class)
        ->setName('post.update');

    $app->put('/post/delete', Action\PostAction::class.":delete")
        ->add(AuthMiddleware::class)
        ->setName('post.delete');

    $app->get('/post/get/{id}', Action\PostAction::class.":get")
        ->add(AuthMiddleware::class)
        ->setName('post.get');

    $app->get('/post/feed', Action\PostAction::class.":feed")
        ->add(AuthMiddleware::class)
        ->setName('post.feed');

    $app->get('/', Action\HomeAction::class);

};
