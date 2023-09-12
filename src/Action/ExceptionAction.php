<?php

namespace App\Action;

use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class ExceptionAction
{
    protected ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function notFound(Request $request)
    {
        $response = new Response();
        $data['code'] = 404;
        $data['message'] = 'Неизвестный запрос!';
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'application/json');
    }

    public function notAuth()
    {
        $response = new Response();
        $data['code'] = 401;
        $data['message'] = 'Not Authorization';
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($data));
        return $response->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    public function error(Request $request, Throwable $exception)
    {
        $response = new Response();
        $data['code'] = $exception->getCode();
        $data['message'] = $exception->getMessage();
        if ($_ENV['APP_DEBUG']){
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
        }

        $http_code = $request->getAttribute('http_code', 422);
        $response->getBody()->rewind();
        $response->getBody()->write(json_encode($data));

        return $response->withStatus($http_code)
            ->withHeader('Content-Type', 'application/json');
    }
}
