<?php

namespace Database\Factories;

use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /** @var string */
    protected $model = Loan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'type_id' => $this->faker->randomElement(array_keys(Loan::TYPES)),
            'amount' => $this->faker->numberBetween(100, 1000),
            'deadline_on' => now()->addMonth(),
        ];
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }

    public function withTypeId(int $typeId): Factory
    {
        return $this->state([
            'type_id' => $typeId,
        ]);
    }

    public function withAmount(int $amount): Factory
    {
        return $this->state([
            'amount' => $amount,
        ]);
    }

    public function withDeadlineOn(Carbon $date): Factory
    {
        return $this->state([
            'deadline_on' => $date,
        ]);
    }
}
