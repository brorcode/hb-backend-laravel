<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\UserRequest;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    public function index(UserRequest $userRequest): JsonResponse
    {
        $users = User::query()->simplePaginate($userRequest->limit);

        return $this->paginatedResponse(UserResource::collection($users));
    }
}
