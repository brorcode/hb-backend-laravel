<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /** @var string */
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'integration_id' => Integration::factory(),
        ];
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
