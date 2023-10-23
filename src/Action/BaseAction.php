<?php

namespace App\Action;

use App\Library\DB;
use App\Library\DBRead;
use Nyholm\Psr7\Response;
use Psr\Container\ContainerInterface;

abstract class BaseAction
{
    protected ContainerInterface $container;
    protected DB $db;
    protected DBRead $db_read;

    public function __construct(ContainerInterface $container, DB $db, DBRead $db_read)
    {
        $this->container = $container;
        $this->db = $db;
        $this->db_read = $db_read;
    }

    public function apiResponse(Response $response, $data, int $status = 200)
    {
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

}
