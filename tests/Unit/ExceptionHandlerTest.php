<?php

namespace Tests\Unit;

use App\Exceptions\ApiBadRequest;
use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testValidationException()
    {
        $this->assertExceptionResponse(
            ValidationException::withMessages([
                'field' => ['Error message'],
            ]),
            HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY,
            [
                'message' => 'Заполните форму правильно',
                'errors' => ['field' => ['Error message']],
            ],
        );
    }

    public function testApiBadRequest()
    {
        $this->assertExceptionResponse(
            new ApiBadRequest('Bad request error'),
            HttpFoundationResponse::HTTP_BAD_REQUEST,
            ['message' => 'Bad request error'],
        );
    }

    public function testModelNotFoundException()
    {
        $this->assertExceptionResponse(
            new ModelNotFoundException(),
            HttpFoundationResponse::HTTP_FORBIDDEN,
            ['message' => 'Доступ запрещен'],
        );
    }

    public function testUnauthorizedException()
    {
        $this->assertExceptionResponse(
            new UnauthorizedException(HttpFoundationResponse::HTTP_FORBIDDEN),
            HttpFoundationResponse::HTTP_FORBIDDEN,
            ['message' => 'Доступ запрещен'],
        );
    }

    public function testNotFoundHttpException()
    {
        $this->assertExceptionResponse(
            new NotFoundHttpException(),
            HttpFoundationResponse::HTTP_NOT_FOUND,
            ['message' => 'URL не существует'],
        );
    }

    public function testInvalidSignatureException()
    {
        $this->assertExceptionResponse(
            new InvalidSignatureException(),
            HttpFoundationResponse::HTTP_NOT_FOUND,
            ['message' => 'Ссылка устарела. Запросите новую'],
        );
    }

    public function testTokenMismatchException()
    {
        $this->assertExceptionResponse(
            new TokenMismatchException(),
            HttpFoundationResponse::HTTP_UNAUTHORIZED,
            ['message' => 'Ваша сессия истекла. Пожалуйста, войдите снова'],
        );
    }

    public function testAuthenticationException()
    {
        $this->assertExceptionResponse(
            new AuthenticationException(),
            HttpFoundationResponse::HTTP_UNAUTHORIZED,
            ['message' => 'Ваша сессия истекла. Пожалуйста, войдите снова'],
        );
    }

    private function assertExceptionResponse($exception, $expectedStatus, $expectedResponse): void
    {
        $request = Request::create('/');
        $handler = new Handler($this->app);
        $response = $handler->render($request, $exception);
        $responseArray = json_decode($response->getContent(), true);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($expectedStatus, $response->status());
        $this->assertEquals($expectedResponse, $responseArray);
    }
}
