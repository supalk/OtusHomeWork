<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AppMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        session_start(["name"=>"sak-token"]);
        //$request = $request->withAttribute('session', $_SESSION);

        return $handler->handle($request);
    }
}
