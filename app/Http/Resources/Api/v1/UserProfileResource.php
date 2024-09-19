<?php

namespace App\Http\Resources\Api\v1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    private function getResource(): User
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $user = $this->getResource();

        return [
            'id' => $user->getKey(),
            'name' => $user->name,
            'email' => $user->email,
            'has_verified_email' => $user->hasVerifiedEmail(),

        ];
    }
}
