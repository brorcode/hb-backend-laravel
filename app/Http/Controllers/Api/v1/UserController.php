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
        $builder = User::query();
        if ($userRequest->getSortingColumn() && $userRequest->getSortingDirection()) {
            $builder->orderBy($userRequest->getSortingColumn(), $userRequest->getSortingDirection());
        }
        $users = $builder->simplePaginate($userRequest->limit);

        return $this->paginatedResponse(UserResource::collection($users));
    }
}
