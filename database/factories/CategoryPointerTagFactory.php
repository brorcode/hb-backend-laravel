<?php

namespace Database\Factories;

use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryPointerTagFactory extends Factory
{
    /** @var string */
    protected $model = CategoryPointerTag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'category_pointer_id' => CategoryPointer::factory(),
        ];
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }

    public function withCategoryPointer(CategoryPointer $categoryPointer): Factory
    {
        return $this->state([
            'category_pointer_id' => $categoryPointer->getKey(),
        ]);
    }
}
