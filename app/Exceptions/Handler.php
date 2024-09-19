<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    public function render($request, Throwable $e): JsonResponse|HttpFoundationResponse
    {
        logger()->error($e);

        return match (true) {
            $e instanceof ValidationException => $this->validationError($e),
            $e instanceof ApiBadRequest => $this->badApiRequest($e),
            $e instanceof ModelNotFoundException => $this->modelNotFound(),
            $e instanceof NotFoundHttpException => $this->routeNotFound(),
            $e instanceof InvalidSignatureException => $this->urlExpired(),
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
            'message' => 'Заполните форму правильно',
            'errors' => $e->errors(),
        ], $e->status);
    }

    private function notAuthenticated(): JsonResponse
    {
        return response()->json([
            'message' => 'Ваша сессия истекла. Пожалуйста, войдите снова'
        ], HttpFoundationResponse::HTTP_UNAUTHORIZED);
    }

    private function modelNotFound(): JsonResponse
    {
        return response()->json(['message' => 'Запись не найдена'], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    private function routeNotFound(): JsonResponse
    {
        return response()->json([
            'message' => 'URL не существует'
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }

    private function urlExpired(): JsonResponse
    {
        return response()->json([
            'message' => 'Ссылка устарела. Запросите новую'
        ], HttpFoundationResponse::HTTP_NOT_FOUND);
    }
}
