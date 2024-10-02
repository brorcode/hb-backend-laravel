<?php

namespace Tests\Unit;

use App\Exceptions\SystemException;
use App\Models\User;
use App\Services\OwnerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetUserReturnsUserThatWaSet(): void
    {
        $user = User::factory()->create();

        $service = OwnerService::make();
        $service->setUser($user);
        try {
            $sameUser = $service->getUser();
        } catch (SystemException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue($user->is($sameUser));
    }

    public function testGetUserReturnsAuthUserIfUserWasNotSet(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = OwnerService::make();
        try {
            $authUser = $service->getUser();
        } catch (SystemException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue($user->is($authUser));
    }

    public function testGetUserCanThrowLogicException(): void
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('User doesn\'t set.');
        $service = OwnerService::make();
        $service->getUser();
    }
}
