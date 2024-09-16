<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /** @var string */
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => $this->faker->words(3, true),
            'is_manual_created' => $this->faker->boolean,
            'check_return' => $this->faker->boolean,
        ];
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }

    public function withParentCategory(Category $category = null): Factory
    {
        return $this->state([
            'parent_id' => $category ? $category->getKey() : Category::factory(),
        ]);
    }

    public function isManualCreated(bool $isManualCreated): Factory
    {
        return $this->state([
            'is_manual_created' => $isManualCreated,
        ]);
    }

    public function checkReturn(bool $checkReturn): Factory
    {
        return $this->state([
            'check_return' => $checkReturn,
        ]);
    }
}
