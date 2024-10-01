<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryService
{
    use ServiceInstance;

    public function getItems(Builder $builder, Request $request): JsonResponse
    {
        if ($request->get('q')) {
            $builder->whereLike('name', '%'.$request->get('q').'%');
        } else {
            $builder->limit(10);
        }

        return response()->json($builder->get());
    }
}
