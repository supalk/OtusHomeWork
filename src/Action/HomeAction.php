<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HomeAction extends BaseAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $this->db->query(/** @lang */ 'select * from users limit 1');
        $response->getBody()->write(json_encode(['hello' => 'world']));

        return $response->withHeader('Content-Type', 'application/json');
    }

}
