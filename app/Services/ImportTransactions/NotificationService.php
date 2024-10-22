<?php

namespace App\Services\ImportTransactions;

use App\Models\Notification;
use App\Models\User;
use App\Services\ServiceSingleton;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    use ServiceSingleton;

    public function addMessage(User $user, string $message): void
    {
        $notification = new Notification();
        $notification->user_id = $user->getKey();
        $notification->message = $message;
        $notification->save();
    }

    public function getMessages(): Collection
    {
        return Notification::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
        ;
    }

    public function readMessages(): void
    {
        Notification::query()
            ->where('user_id', Auth::id())
            ->where('is_viewed',false)
            ->update(['is_viewed' => true])
        ;
    }
}
