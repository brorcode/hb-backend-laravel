<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\CategoryResource;
use App\Http\Resources\Api\v1\UserResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class CategoryController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = CategoryService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $categories = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(CategoryResource::collection($categories));
    }
}
