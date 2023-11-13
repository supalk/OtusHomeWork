<?php

namespace App\Action;

use App\Library\Auth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;

final class PostAction extends BaseAction
{
    public function create(Request $request, Response $response, $args): Response
    {


        return $this->apiResponse($response, ["message" => "Пользователь успешно указал своего друга"]);
    }

    public function delete(Request $request, Response $response, $args): Response
    {


        return $this->apiResponse($response, ["message" => "Пользователь успешно удалил из друзей пользователя"]);
    }


    public function update(Request $request, Response $response, $args): Response
    {


        return $this->apiResponse($response, ["message" => "Пользователь успешно удалил из друзей пользователя"]);
    }


    public function get(Request $request, Response $response, $args): Response
    {


        return $this->apiResponse($response, ["message" => "Пользователь успешно удалил из друзей пользователя"]);
    }

    public function feed(Request $request, Response $response, $args): Response
    {


        return $this->apiResponse($response, ["message" => "Пользователь успешно удалил из друзей пользователя"]);
    }


}
