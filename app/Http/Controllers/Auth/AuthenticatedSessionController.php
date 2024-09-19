<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\v1\ApiController;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\Api\v1\UserProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthenticatedSessionController extends ApiController
{
    /**
     * Handle an incoming authentication request.
     * @throws ValidationException
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return $this->response(UserProfileResource::make($request->user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([], ResponseAlias::HTTP_NO_CONTENT);
    }
}
