<?php

use Slim\App;

return function (App $app) {
    // Parse json, form data and xml
    $app->addBodyParsingMiddleware();

    // Add the Slim built-in routing middleware
    $app->addRoutingMiddleware();

    $container = $app->getContainer();
    // Handle exceptions
    $app->addErrorMiddleware(
        $_ENV['APP_DEBUG'] ?? true,
        true,
        true
    )->setErrorHandler(
        Slim\Exception\HttpNotFoundException::class,
        function (Psr\Http\Message\ServerRequestInterface $request) use ($container) {
            $controller = new \App\Action\ExceptionAction($container);
            return $controller->notFound($request);
        }
    )->setErrorHandler(
        Slim\Exception\HttpUnauthorizedException::class,
        function (Psr\Http\Message\ServerRequestInterface $request) use ($container) {
            $controller = new \App\Action\ExceptionAction($container);
            return $controller->notAuth($request);
        }
    )->setErrorHandler(
        Slim\Exception\HttpException::class,
        function (Psr\Http\Message\ServerRequestInterface $request, Throwable $exception) use ($container) {
            $controller = new \App\Action\ExceptionAction($container);
            return $controller->error($request, $exception);
        }
    );

};
