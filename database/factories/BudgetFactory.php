<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    /** @var string */
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(100, 1000),
            'category_id' => Category::factory(),
            'period_on' => now()->subMonth(),
        ];
    }
}
