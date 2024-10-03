<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureEmailIsVerifiedTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Route::middleware([EnsureEmailIsVerified::class])
            ->get('/test', function () {
                return response()->json(['message' => 'Success']);
            });
    }

    public function testItReturnsErrorIfUserIsNotAuthenticated()
    {
        $response = $this->get('/test');
        $response->assertForbidden();
        $response->assertJson(['message' => 'Ваша почта не подтверждена']);
    }

    public function testItReturnsErrorIfEmailIsNotVerifiedAnd()
    {
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        $response = $this->get('/test');

        $response->assertForbidden();
        $response->assertJson(['message' => 'Ваша почта не подтверждена']);
    }

    public function testItAllowsAccessIfEmailIsVerified()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->get('/test');

        $response->assertOk();
        $response->assertJson(['message' => 'Success']);
    }
}
