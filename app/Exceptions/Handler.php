<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    public function render($request, Throwable $e): Response
    {
        return match (true) {
            $e instanceof ValidationException => $this->validationError($e),
            $e instanceof ApiBadRequest => $this->badApiRequest($e),
            $e instanceof TokenMismatchException, $e instanceof AuthenticationException => $this->notAuthenticated(),
            default => parent::render($request, $e),
        };
    }

    private function badApiRequest(ApiBadRequest $e): JsonResponse
    {
        return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
    }

    protected function validationError(ValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Заполните форму правильно.',
            'errors' => $e->errors(),
        ], $e->status);
    }

    private function notAuthenticated(): JsonResponse
    {
        return response()->json([
            'message' => 'Запрос не авторизован.'
        ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
    }
}
