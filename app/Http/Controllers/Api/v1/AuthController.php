<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $loginRequest): JsonResponse
    {
        if (Auth::attempt($loginRequest->toArray())) {
            $loginRequest->session()->regenerate();

            return response()->json([
                'message' => 'Вы успешно залогинились.',
            ]);
        }

        return response()->json([
            'message' => 'Ошибка входа.',
        ]);
    }
}
