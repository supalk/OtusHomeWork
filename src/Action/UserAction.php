<?php

namespace App\Action;

use App\Library\Auth;
use App\Library\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;

final class UserAction extends BaseAction
{
    public function login(Request $request, Response $response): Response
    {
        /** @var Auth $auth */
        $auth = $this->container->get(Auth::class);
        $auth->login($request);

        return $this->apiResponse($response, ["message" => "Успешная аутентификация"]);
    }

    public function register(Request $request, Response $response): Response
    {
        $params = (array)$request->getParsedBody();
        $user = new User();
        // Валидация параметров
        $params_req = $user->getRequired();
        foreach ($params_req as $field) {
            if (!(isset($params[$field]) && !empty($params[$field]))) {
                $request = $request->withAttribute('http_code', 400);
                throw new HttpException($request,
                    "Невалидные данные ({$field})",
                    400
                );
            }
            if ($field == 'password') {
                $params[$field] = Auth::getHashPassword($params[$field]);
            }
        }
        // Проверка на существования логина (уникален)

        $user->load($params);

        $this->db->insert("users", $user->getArray());

        return $this->apiResponse($response, ["message" => "Успешная регистрация"]);
    }

    public function getItem(Request $request, Response $response): Response
    {
        $id = (int)$request->getAttribute('id');
        if ($id == 0) {
            $auth = $this->container->get(Auth::class);
            $id = $auth->user->user_id;
        }

        $user = $this->db->query_row(sprintf(/** @lang */
            "select user_id, name, surname, lastname, gender, biography, city 
            from users 
            where user_id=%d",
            $id
        ));

        if (!$user) {
            $request = $request->withAttribute('http_code', 404);
            throw new HttpException($request,"Анкета не найдена", 404);
        }

        return $this->apiResponse($response, $user);
    }

    public function searchUser(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        if (!isset($params['first_name']) || !isset($params['last_name'])){
            $request = $request->withAttribute('http_code', 400);
            throw new HttpException($request,"Невалидные данные", 400);
        }

        try {
            $user = $this->db->query_array(sprintf(/** @lang */
                "select user_id, name, surname, lastname, gender, biography, city 
                from users 
                where lower(name) like '%s%%'
                and lower(surname) like '%s%%'
                ",
                mb_strtolower($this->db->escape_string($params['first_name'])),
                mb_strtolower($this->db->escape_string($params['last_name']))
            ));

        }catch (\Exception $e){
            $request = $request->withAttribute('http_code', 500);
            throw new HttpException($request,"Ошибка выполнения запроса", 500);
        }

        return $this->apiResponse($response, $user??[]);
    }


}
