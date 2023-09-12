<?php

namespace App\Library;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Exception\HttpUnauthorizedException;

class Auth
{
    protected ContainerInterface $container;
    public $user;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->user = new User();
    }

    public function authorized(Request $request)
    {
        if (Session::exists('user')) {
            $this->user->load(Session::get('user'));

            return true;
        } else {
            throw new HttpUnauthorizedException($request);
        }
    }

    public function login(Request $request)
    {
        $params = $request->getServerParams();
        $login = $params['PHP_AUTH_USER'] ?? null;
        $psw = $params['PHP_AUTH_PW'] ?? null;
        if (!empty($login) && !empty($psw)) {
            /** @var DB $db */
            $db = $this->container->get(DB::class);
            $user = $db->query_row(sprintf( /** @lang */
                "select user_id, name, surname, lastname, gender, biography, city 
                from users 
                where login='%s' and password='%s'",
                $login,
                self::getHashPassword($psw)
            ));
            if ($user) {
                Session::set('user', $user);

                return true;
            }

            $request = $request->withAttribute('http_code', 404);
            throw new HttpException($request,
                'Пользователь не найден',
                404
            );
        }

        $request = $request->withAttribute('http_code', 400);
        throw new HttpException($request,
            'Невалидные данные',
            400
        );
    }

    public function logout(Request $request)
    {
        if (Session::exists('user')) {
            Session::destroy('user');
        }
    }

    public static function getHashPassword($password)
    {
        return hash('sha1', trim($password));
    }

}
