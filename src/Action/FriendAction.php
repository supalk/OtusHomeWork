<?php

namespace App\Action;

use App\Library\Auth;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;

final class FriendAction extends BaseAction
{
    public function set(Request $request, Response $response, $args): Response
    {
        $friend_id = $args['id'];
        // Проверка существования пользователя
        $friend = $this->getUserFriend($friend_id);
        if (!$friend) {
            $request = $request->withAttribute('http_code', 400);
            throw new HttpException($request,
                "Пользователь не найден!",
                400
            );
        }

        if (!$friend['friend_id']) {
            $this->db->insert('friends',
                ["user_id" => $this->auth->user->user_id, 'friend_id' => $friend_id]
            );
        }

        return $this->apiResponse($response, ["message" => "Пользователь успешно указал своего друга"]);
    }

    public function delete(Request $request, Response $response, $args): Response
    {
        $friend_id = $args['id'];
        // Проверка существования пользователя
        $friend = $this->getUserFriend($friend_id);
        if (!$friend) {
            $request = $request->withAttribute('http_code', 400);
            throw new HttpException($request,
                "Пользователь не найден!",
                400
            );
        }

        if ($friend['friend_id']) {
            $this->db->exec('delete from friends where user_id=? and friend_id=?',
                [$this->auth->user->user_id, $friend_id]
            );
        }

        return $this->apiResponse($response, ["message" => "Пользователь успешно удалил из друзей пользователя"]);
    }

    public function getUserFriend(int $friend_id): array
    {
        $auth = $this->container->get(Auth::class);
        return $this->db->query_row(sprintf(/** @lang */
            "select a.user_id, b.friend_id 
            from users a
            left join friends b on a.user_id=b.friend_id and b.user_id=%d 
            where a.user_id=%d",
            $auth->user->user_id,
            $friend_id
        ));

    }
}
