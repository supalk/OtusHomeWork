<?php

namespace App\Action;

use App\Library\Auth;
use App\Library\DB;
use App\Library\DBRead;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Container\ContainerInterface;

abstract class BaseAction
{
    protected ContainerInterface $container;
    protected DB $db;
    protected DBRead $db_read;
    protected Auth $auth;

    public function __construct(ContainerInterface $container, DB $db, DBRead $db_read, Auth $auth)
    {
        $this->container = $container;
        $this->db = $db;
        $this->db_read = $db_read;
        $this->auth = $auth;
    }

    public function apiResponse(Response $response, $data, int $status = 200)
    {
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

}
