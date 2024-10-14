<?php

namespace App\Http\Resources\Api\v1;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    private function getResource(): Tag
    {
        return $this->resource;
    }

    public function toArray(Request $request): array
    {
        $tag = $this->getResource();

        return [
            'id' => $tag->getKey(),
            'name' => $tag->name,
            'amount' => $tag->transactions->sum('amount') / 100,
            'created_at' => $tag->created_at,
            'updated_at' => $tag->updated_at,
        ];
    }
}
