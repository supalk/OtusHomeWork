<?php

namespace App\Exceptions;

use Exception;

class AuthException extends Exception
{
    public function __construct($message)
    {
        $this->code = 401;
        $this->message = 'Not Authorization';
        parent::__construct($message, 401);
    }



}
