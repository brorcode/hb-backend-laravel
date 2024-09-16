<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Category\CategoryPointerRequest;
use App\Http\Resources\Api\v1\Category\CategoryPointerResource;
use App\Jobs\UpdateTransactionCategoriesJob;
use App\Services\Category\CategoryPointerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryPointerController extends ApiController
{
    public function index(CategoryPointerService $service): JsonResponse
    {
        return response()->json([
            'parent' => CategoryPointerResource::collection($service->getPointers(true)),
            'child' => CategoryPointerResource::collection($service->getPointers(false)),
        ]);

    }

    public function save(CategoryPointerRequest $request, CategoryPointerService $service): JsonResponse
    {
        $service->createPointersTree($request->child, false);
        $service->createPointersTree($request->parent, true);

        UpdateTransactionCategoriesJob::dispatch(Auth::user());

        return response()->json([
            'message' => 'Указатели категорий обновлены',
            'parent' => CategoryPointerResource::collection($service->getPointers(true)),
            'child' => CategoryPointerResource::collection($service->getPointers(false)),

        ]);
    }
}
