<?php

namespace App\Http\Resources\Api\v1\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    private function getResource(): Category
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $category = $this->getResource();

        return [
            'id' => $category->getKey(),
            'name' => $category->name,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    }
}
