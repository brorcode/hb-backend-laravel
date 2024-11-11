<?php

namespace Tests\Feature\Notification;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationListTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    public function testNotificationListApiReturnsCorrectResponseForAuthenticatedUser(): void
    {
        $notifications = Notification::factory()
            ->count(50)
            ->for($this->user)
            ->create([
                'is_viewed' => false,
            ])
        ;

        $data = $notifications->take(20)->map(function (Notification $notification) {
            return [
                'message' => $notification->message,
                'is_viewed' => false,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        $response = $this->getJson(route('api.v1.notifications.index'));

        $response->assertOk();
        $response->assertJsonCount(20, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'has_new' => true,
        ]);
    }

    public function testNotificationListApiDoesNotShowNotificationForAnotherUser(): void
    {
        $user = User::factory()->create();
        Notification::factory()
            ->count(1)
            ->for($user)
            ->create()
        ;

        $response = $this->getJson(route('api.v1.notifications.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $response->assertExactJson([
            'data' => [],
            'has_new' => false,
        ]);
    }
}
