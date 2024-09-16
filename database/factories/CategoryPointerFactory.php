<?php

namespace Database\Factories;

use App\Models\CategoryPointer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryPointerFactory extends Factory
{
    /** @var string */
    protected $model = CategoryPointer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'is_parent' => $this->faker->boolean,
        ];
    }

    public function isParent(bool $value): Factory
    {
        return $this->state([
            'is_parent' => $value,
        ]);
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
