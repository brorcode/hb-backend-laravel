<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiBadRequest extends ApiException
{
    protected $code = ResponseAlias::HTTP_BAD_REQUEST;
}
