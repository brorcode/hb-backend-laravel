<?php

namespace App\Http\Resources\Api\v1\Category;

use App\Models\Category;
use App\Models\CategoryPointer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryPointerResource extends JsonResource
{
    private function getResource(): CategoryPointer
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $categoryPointer = $this->getResource();

        return [
            'id' => $categoryPointer->getKey(),
            'name' => $categoryPointer->name,
            'is_parent' => $categoryPointer->is_parent,
            'tags_array' => $categoryPointer->categoryPointerTags->pluck('name')->toArray(),
        ];
    }
}
