<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\UserProfileRequest;
use App\Http\Resources\Api\v1\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->response(UserProfileResource::make($request->user()));
    }

    public function update(UserProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->name = $request->name;
        $user->email = $request->email;
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return $this->response(UserProfileResource::make($user), 'Пользователь обновлен');
    }

    public function emailVerification(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->response(
                UserProfileResource::make($user),
                'Ваша почта уже потверждена'
            );
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Вам отправлено письмо для потверждения электронной почты']);
    }
}
