<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Account;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    private function getResource(): Notification
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $notification = $this->getResource();

        return [
            'message' => $notification->message,
            'is_viewed' => $notification->is_viewed,
            'created_at' => $notification->created_at->diffForHumans(),
        ];
    }
}
