<?php

namespace App\Http\Requests;

use App\Exceptions\UrlExpiredException;
use App\Http\Requests\Api\v1\ApiRequest;

class EmailVerificationRequest extends ApiRequest
{
    /**
     * @throws UrlExpiredException
     */
    public function rulesPassed(): void
    {
        if (! hash_equals((string) $this->user()->getKey(), (string) $this->route('id'))) {
            throw new UrlExpiredException();
        }

        if (! hash_equals(sha1($this->user()->getEmailForVerification()), (string) $this->route('hash'))) {
            throw new UrlExpiredException();
        }
    }
}
