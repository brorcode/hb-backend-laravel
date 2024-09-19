<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\v1\ApiController;
use App\Http\Requests\EmailVerificationRequest;
use App\Http\Resources\Api\v1\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends ApiController
{
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->response(
                UserProfileResource::make($user),
                'Ваша почта уже потверждена'
            );
        }

        $user->markEmailAsVerified();

        return $this->response(
            UserProfileResource::make($user),
            'Ваша почта потверждена'
        );
    }
}
