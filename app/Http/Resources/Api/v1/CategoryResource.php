<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    private function getResource(): Category
    {
        return $this->resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $category = $this->getResource();

        return [
            'id' => $category->getKey(),
            'name' => $category->name,
            'description' => $category->description,
            'createdAt' => $category->created_at,
            'updatedAt' => $category->updated_at,
        ];
    }
}
