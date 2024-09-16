<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SystemException extends ApiException
{
    protected $code = ResponseAlias::HTTP_BAD_REQUEST;
}
