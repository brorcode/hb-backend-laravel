<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /** @var string */
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory()->withParentCategory(),
            'amount' => $this->faker->numberBetween(100, 1000),
            'account_id' => Account::factory(),
            'loan_id' => null,
            'is_debit' => $this->faker->boolean,
            'is_transfer' => $this->faker->boolean,
            'created_at' => now(),
        ];
    }

    public function debit(): Factory
    {
        return $this->state([
            'is_debit' => true,
        ]);
    }

    public function credit(): Factory
    {
        return $this->state([
            'is_debit' => false,
        ]);
    }

    public function transfer(): Factory
    {
        return $this->state([
            'is_transfer' => true,
        ]);
    }

    public function notTransfer(): Factory
    {
        return $this->state([
            'is_transfer' => false,
        ]);
    }

    public function withAmount(int $amount): Factory
    {
        return $this->state([
            'amount' => $amount,
        ]);
    }

    public function withCategory(Category $category = null): Factory
    {
        return $this->state([
            'category_id' => $category ? $category->getKey() : Category::factory()->withParentCategory(),
        ]);
    }

    public function withAccount(Account $account = null): Factory
    {
        return $this->state([
            'account_id' => $account ? $account->getKey() : Account::factory(),
        ]);
    }

    public function withLoan(Loan $loan = null): Factory
    {
        return $this->state([
            'loan_id' => $loan ? $loan->getKey() : Loan::factory(),
        ]);
    }
}
