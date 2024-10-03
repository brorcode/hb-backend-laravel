<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            return response()->json([
                'message' => 'Ваша почта не подтверждена',
            ], HttpFoundationResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
