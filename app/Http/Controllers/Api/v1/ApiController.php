<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class ApiController
{
    public function paginatedResponse(ResourceCollection $collection, array $with = [], string $wrap = ''): JsonResponse
    {
        $responseData = array_merge($with, [
            $wrap ?: $collection::$wrap => $collection,
            'meta' => [
                'perPage' => $collection->resource->perPage(),
                'currentPage' => $collection->resource->currentPage(),
                'hasNextPage' => $collection->resource->hasMorePages(),
            ]
        ]);

        return response()->json($responseData);
    }
}
