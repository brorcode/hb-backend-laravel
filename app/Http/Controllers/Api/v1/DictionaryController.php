<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Account;
use App\Models\Category;
use App\Models\Tag;
use App\Services\DictionaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends ApiController
{
    public function categories(Request $request, DictionaryService $service): JsonResponse
    {
        $builder = Category::query()
            ->select(['id', 'name'])
            ->whereNotNull('parent_id')
        ;

        return $service->getItems($builder, $request);
    }

    public function categoriesParent(Request $request, DictionaryService $service): JsonResponse
    {
        $builder = Category::query()
            ->select(['id', 'name'])
            ->whereNull('parent_id')
        ;

        return $service->getItems($builder, $request);
    }

    public function accounts(Request $request, DictionaryService $service): JsonResponse
    {
        $builder = Account::query()->select(['id', 'name']);

        return $service->getItems($builder, $request);
    }

    public function tags(Request $request, DictionaryService $service): JsonResponse
    {
        $builder = Tag::query()->select(['id', 'name']);

        return $service->getItems($builder, $request);
    }
}
