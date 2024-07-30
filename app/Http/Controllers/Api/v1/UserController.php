<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UserRequest;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function index(UserRequest $hotelRequest): JsonResource
    {
        $users = User::query()
            ->offset(($hotelRequest->page - 1) * $hotelRequest->limit)
            ->limit($hotelRequest->limit + 1)
            ->get();

        return UserResource::collection($users)->additional([
            'count' => User::query()->count(),
        ]);
    }
}
