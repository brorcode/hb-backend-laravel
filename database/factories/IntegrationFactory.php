<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    /** @var string */
    protected $model = Integration::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'code_id' => $this->faker->randomElement(Integration::CODES),
        ];
    }
}
