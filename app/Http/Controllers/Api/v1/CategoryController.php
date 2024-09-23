<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Category\CategoryUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\Category\CategoryResource;
use App\Models\Category;
use App\Services\Category\CategoryListService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = CategoryListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $categories = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(CategoryResource::collection($categories));
    }

    public function store(CategoryUpsertRequest $request): JsonResponse
    {
        $category = new Category();
        $category->name = $request->name;
        $category->is_manual_created = true;
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
        $category->save();

        return $this->response(CategoryResource::make($category), 'Категория обновлена');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Категория удалена']);
    }
}
