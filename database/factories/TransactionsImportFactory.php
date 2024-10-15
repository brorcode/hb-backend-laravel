<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\TransactionsImport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionsImportFactory extends Factory
{
    /** @var string */
    protected $model = TransactionsImport::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'status_id' => $this->faker->randomElement(array_keys(TransactionsImport::STATUSES)),
            'file_name' => $this->faker->word(),
            'file_path' => $this->faker->filePath(),
            'imported_count' => $this->faker->numberBetween(1, 1000),
            'error' => $this->faker->text(10),
            'started_at' => now(),
            'finished_at' => now()->addMinutes(5),
        ];
    }
}
