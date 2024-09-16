<?php

namespace App\Http\Middleware;

use App\Http\Requests\LoginRequest;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ApiGuestMiddleware
{
    public function handle(Request $request, Closure $next, string ...$guards): JsonResponse
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // @todo think of a better way. Maybe we don't need to validate here or remove this middleware at all
                // user can send POST to register form when login and these rules will be wrong
                // $request->validate(
                //     (new LoginRequest())->rules()
                // );
                //
                // return response()->json([
                //     'data' => $request->user()->only('name', 'email'),
                // ], HttpFoundationResponse::HTTP_OK);
            }
        }

        return $next($request);
    }
}
