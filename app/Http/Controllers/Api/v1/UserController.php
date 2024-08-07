<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Requests\Api\v1\UserUpsertRequest;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $userService = UserService::create();
        $userService->setRequest($request);

        $builder = $userService->getListBuilder();
        $users = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(UserResource::collection($users));
    }

    public function store(UserUpsertRequest $request): JsonResponse
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;

        return response()->json([], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        return $this->response(UserResource::make($user));
    }

    public function update(UserUpsertRequest $request, User $user): JsonResponse
    {
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = $request->password;
        }
        $user->save();

        return $this->response(UserResource::make($user), 'Пользователь обновлен');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'Пользователь удален']);
    }
}
