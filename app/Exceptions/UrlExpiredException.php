<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UrlExpiredException extends ApiException
{
    protected $code = ResponseAlias::HTTP_NOT_FOUND;
    protected $message = 'Ссылка устарела. Запросите новую';
}
