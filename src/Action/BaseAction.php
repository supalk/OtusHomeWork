<?php

namespace App\Action;

use App\Library\DB;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;

abstract class BaseAction
{
    protected ContainerInterface $container;
    protected DB $db;

    public function __construct(ContainerInterface $container, DB $db)
    {
        $this->container = $container;
        $this->db = $db;
    }

    public function apiResponse(Response $response, $data, int $status = 200)
    {
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

}
