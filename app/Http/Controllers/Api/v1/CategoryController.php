<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Category\CategoryUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\Category\CategoryChildResource;
use App\Http\Resources\Api\v1\Category\CategoryResource;
use App\Models\Category;
use App\Services\Category\CategoryChildListService;
use App\Services\Category\CategoryParentListService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends ApiController
{
    public function parent(ListRequest $request): JsonResponse
    {
        $service = CategoryParentListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $categories = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(CategoryResource::collection($categories));
    }

    public function child(ListRequest $request): JsonResponse
    {
        $service = CategoryChildListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $categories = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(CategoryChildResource::collection($categories));
    }

    public function store(CategoryUpsertRequest $request): JsonResponse
    {
        $category = new Category();
        $category->name = $request->name;
        $category->is_manual_created = true;

        if ($request->parent_id) {
            $category->parent_id = $request->parent_id;
        }

        $category->save();

        return response()->json(['message' => 'Категория создана'], Response::HTTP_CREATED);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->response(CategoryResource::make($category));
    }

    public function update(CategoryUpsertRequest $request, Category $category): JsonResponse
    {
        $category->name = $request->name;

        if ($request->parent_id) {
            $category->parent_id = $request->parent_id;
        }

        $category->save();

        return $this->response(CategoryResource::make($category), 'Категория обновлена');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Категория удалена']);
    }
}
