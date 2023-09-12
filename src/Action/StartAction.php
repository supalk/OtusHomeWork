<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class StartAction extends BaseAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $this->db->logEnable(false);
        foreach (get_file_list(config_path('db'), 'sql') as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/(^-{2,}.+)/iD','',$content);
            foreach (explode(';', $content) as $sql)
                if (strlen($sql) > 2) {
                    try {
                        $this->db->exec($sql . ";");
                    } catch (\Exception $e) {
                        return $this->apiResponse($response, [
                                "code" => $e->getCode(),
                                "error" => "Инициализация данных. Error: " . $e->getMessage()
                            ],
                            422
                        );

                    }
                }
        }

        return $this->apiResponse($response, ["message" => "Инициализация данных. Успешно"]);
    }


}
