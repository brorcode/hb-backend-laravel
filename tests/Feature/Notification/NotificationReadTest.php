<?php

namespace Tests\Feature\Notification;

use App\Models\Account;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    public function testNotificationReadApiCanMarkNotificationAsViewed(): void
    {
        $notifications = Notification::factory()
            ->count(10)
            ->for($this->user)
            ->create([
                'is_viewed' => false,
            ])
        ;

        $data = $notifications->map(function (Notification $notification) {
            return [
                'message' => $notification->message,
                'is_viewed' => true,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });

        $response = $this->postJson(route('api.v1.notifications.read'));

        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertExactJson([
            'data' => $data->toArray(),
            'has_new' => false,
        ]);
    }
}
