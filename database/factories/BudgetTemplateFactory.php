<?php

namespace Database\Factories;

use App\Models\BudgetTemplate;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetTemplateFactory extends Factory
{
    /** @var string */
    protected $model = BudgetTemplate::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(100, 1000),
            'category_id' => Category::factory(),
        ];
    }
}
