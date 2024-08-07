<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\CategoryUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    public function store(CategoryUpsertRequest $request): JsonResponse
    {
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return response()->json([], Response::HTTP_CREATED);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->response(CategoryResource::make($category));
    }

    public function update(CategoryUpsertRequest $request, Category $category): JsonResponse
    {
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return $this->response(CategoryResource::make($category), 'Категория обновлена');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Категория удалена']);
    }
}
