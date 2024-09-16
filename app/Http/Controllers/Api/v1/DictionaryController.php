<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Account;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryController extends ApiController
{
    public function categories(Request $request): JsonResponse
    {
        $builder = Category::query()
            ->select(['id', 'name'])
            ->whereNotNull('parent_id')
        ;

        if ($request->get('q')) {
            $builder->whereLike('name', '%'.$request->get('q').'%');
        } else {
            $builder->limit(10);
        }

        $items = $builder->get();

        return response()->json($items);
    }

    public function accounts(Request $request): JsonResponse
    {
        $builder = Account::query()->select(['id', 'name']);

        if ($request->get('q')) {
            $builder->whereLike('name', '%'.$request->get('q').'%');
        } else {
            $builder->limit(10);
        }

        $items = $builder->get();

        return response()->json($items);
    }

    public function tags(Request $request): JsonResponse
    {
        $builder = Tag::query()->select(['id', 'name']);

        if ($request->get('q')) {
            $builder->whereLike('name', '%'.$request->get('q').'%');
        } else {
            $builder->limit(10);
        }

        $items = $builder->get();

        return response()->json($items);
    }
}
