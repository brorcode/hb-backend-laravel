<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransactionObserverTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    public function testCreditTransactionChangeAmountToNegative(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()->create([
            'is_debit' => false,
            'amount' => 100.50,
        ]);

        $this->assertEquals(-100.50, $transaction->amount);
    }

    public function testTransactionCanNotBeSavedWithOutCategory(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Поле Категория обязательно для заполнения.');
        $this->assertCount(0, Transaction::all());

        $transaction = new Transaction();
        $transaction->save();

        $this->assertCount(0, Transaction::all());
    }

    public function testTransactionCanNotBeSavedWithParentCategory(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Вы не можете создать транзакцию для родительской категории.');
        $this->assertCount(0, Transaction::all());

        /** @var Category $category */
        $category = Category::factory()->withParentCategory()->create();

        $transaction = new Transaction();
        $transaction->category_id = $category->parent_id;
        $transaction->save();

        $this->assertCount(0, Transaction::all());
    }

    public function testTransferTransactionCanNotTransferToSaveAccount(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Нельзя перевести на тот же счет.');
        $this->assertCount(0, Transaction::all());

        $account = Account::factory()->create();
        request()->merge(['account_to' => $account->getKey()]);
        Transaction::factory()->create([
            'is_transfer' => true,
            'is_debit' => false,
            'amount' => 10,
            'account_id' => $account->getKey(),
        ]);

        $this->assertCount(0, Transaction::all());
    }

    public function testTransferTransactionCanNotTransferForDebitTransaction(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Данная транзакция должна быть расходом.');
        $this->assertCount(0, Transaction::all());

        $accountTo = Account::factory()->create();
        request()->merge(['account_to' => $accountTo->getKey()]);
        Transaction::factory()->create([
            'is_transfer' => true,
            'is_debit' => true,
            'amount' => 10,
        ]);

        $this->assertCount(0, Transaction::all());
    }

    public function testTransferTransactionCreatesAnotherTransaction(): void
    {
        $this->assertCount(0, Transaction::all());

        $accountTo = Account::factory()->create();
        request()->merge(['account_to' => $accountTo->getKey()]);
        Transaction::factory()->create([
            'is_transfer' => true,
            'is_debit' => false,
            'amount' => 10,
        ]);
        $this->assertCount(2, Transaction::all());
    }

    public function testNotTransferTransactionDoesNotCreateAnotherTransaction(): void
    {
        $this->assertCount(0, Transaction::all());
        Transaction::factory()->create([
            'is_transfer' => false,
        ]);
        $this->assertCount(1, Transaction::all());
    }
}
