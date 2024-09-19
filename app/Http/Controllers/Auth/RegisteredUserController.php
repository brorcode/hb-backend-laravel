<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Api\v1\ApiController;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\Api\v1\UserProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends ApiController
{
    public function store(RegisterRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        event(new UserRegistered($user));

        Auth::login($user);

        return $this->response(
            UserProfileResource::make($user),
            'Ссылка для потверждения была отправлена на вашу почту'
        );
    }
}
