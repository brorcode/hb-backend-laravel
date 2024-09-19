<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiException extends Exception
{
    protected $code = ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;
    protected $message = 'Ошибка сервера. Попробуйте еще раз';

    final public function getStatusCode(): int
    {
        return $this->code;
    }
}
