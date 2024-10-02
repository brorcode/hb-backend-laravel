<?php

namespace Tests\Feature\Auth;

use App\Exceptions\UrlExpiredException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function testEmailCanBeVerified(): void
    {
        $user = $this->userLogin([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->email)]
        );

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response = $this->actingAs($user)->get($verificationUrl);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertExactJson([
            'message' => 'Ваша почта потверждена',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'has_verified_email' => $user->hasVerifiedEmail(),
                'permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    public function testEmailCannotBeVerifiedTwice(): void
    {
        $user = $this->userLogin();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->email)]
        );

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response = $this->actingAs($user)->get($verificationUrl);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertExactJson([
            'message' => 'Ваша почта уже потверждена',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'has_verified_email' => $user->hasVerifiedEmail(),
                'permissions' => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
            ],
        ]);
    }

    public function testEmailIsNotVerifiedWithInvalidId(): void
    {
        $user = $this->userLogin([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => 111, 'hash' => sha1($user->email)]
        );

        Exceptions::fake();

        $this->actingAs($user)->getJson($verificationUrl);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        Exceptions::assertReported(UrlExpiredException::class);
        Exceptions::assertReported(function (UrlExpiredException $e) {
            return $e->getMessage() === 'Ссылка устарела. Запросите новую';
        });
    }

    public function testEmailIsNotVerifiedWithInvalidHash(): void
    {
        $user = $this->userLogin([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1('wrong-email')]
        );

        Exceptions::fake();

        $this->actingAs($user)->getJson($verificationUrl);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        Exceptions::assertReported(UrlExpiredException::class);
        Exceptions::assertReported(function (UrlExpiredException $e) {
            return $e->getMessage() === 'Ссылка устарела. Запросите новую';
        });
    }
}
