<?php

namespace App\Middleware;

use App\Library\Auth;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    protected ContainerInterface $container;
    protected Auth $auth;

    public function __construct(ContainerInterface $container, Auth $auth)
    {
        $this->container = $container;
        $this->auth = $auth;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $this->auth->authorized($request);

        return $handler->handle($request);
    }
}
