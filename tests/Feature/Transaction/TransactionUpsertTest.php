<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTransactionShow(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->hasAttached(Tag::factory()->count(2))
            ->create([
                'amount' => 123,
                'is_debit' => true,
            ])
        ;
        $response = $this->getJson(route('api.v1.transactions.show', $transaction));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $transaction->getKey(),
                'amount' => 1.23,
                'category' => $transaction->category->only(['id', 'name']),
                'account' => $transaction->account->only(['id', 'name']),
                'loan' => null,
                'tags' => $transaction->tags?->map(function (Tag $tag) {
                    return $tag->only(['id', 'name']);
                }),
                'is_debit' => true,
                'is_transfer' => $transaction->is_transfer,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ],
        ]);
    }

    public function testTransactionStore(): void
    {
        $this->assertCount(0, Transaction::all());
        $category = Category::factory()->withParentCategory()->create();
        $account = Account::factory()->create();
        $loan = Loan::factory()->create();

        $response = $this->postJson(route('api.v1.transactions.store'), [
            'amount' => 1000,
            'category_id' => $category->getKey(),
            'account_id' => $account->getKey(),
            'loan_id' => $loan->getKey(),
            'created_at' => now(),
            'is_debit' => false,
            'is_transfer' => false,
        ]);

        $this->assertCount(1, Transaction::all());
        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'amount' => -100000,
            'category_id' => $category->getKey(),
            'account_id' => $account->getKey(),
            'loan_id' => $loan->getKey(),
            'is_debit' => false,
            'is_transfer' => false,
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Транзакция создана',
        ]);
    }

    #[DataProvider('invalidTransactionDataProvider')]
    public function testTransactionCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.transactions.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testTransactionUpdate(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()->create();

        $category = Category::factory()->withParentCategory()->create();
        $account = Account::factory()->create();
        $loan = Loan::factory()->create();

        $this->assertCount(1, Transaction::all());
        $this->assertDatabaseMissing((new Transaction())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
            'account_id' => $account->getKey(),
            'loan_id' => $loan->getKey(),
            'is_debit' => true,
            'is_transfer' => false,
        ]);

        $response = $this->putJson(route('api.v1.transactions.update', $transaction), [
            'amount' => 100.12,
            'category_id' => $category->getKey(),
            'account_id' => $account->getKey(),
            'loan_id' => $loan->getKey(),
            'created_at' => now(),
            'is_debit' => true,
            'is_transfer' => false,
        ]);

        $this->assertCount(1, Transaction::all());
        $this->assertDatabaseHas((new Transaction())->getTable(), [
            'amount' => 10012,
            'category_id' => $category->getKey(),
            'account_id' => $account->getKey(),
            'loan_id' => $loan->getKey(),
            'is_debit' => true,
            'is_transfer' => false,
        ]);

        $response->assertOk();

        $freshTransaction = $transaction->fresh();
        $response->assertExactJson([
            'message' => 'Транзакция обновлена',
            'data' => [
                'id' => $transaction->getKey(),
                'amount' => 100.12,
                'category' => $category->only(['id', 'name']),
                'account' => $account->only(['id', 'name']),
                'loan' => $loan->only(['id', 'name']),
                'tags' => [],
                'is_debit' => true,
                'is_transfer' => false,
                'created_at' => $freshTransaction->created_at,
                'updated_at' => $freshTransaction->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidTransactionDataProvider')]
    public function testTransactionCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->putJson(route('api.v1.transactions.update', $transaction), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidTransactionDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'amount' => ['Поле amount обязательно.'],
                    'category_id' => ['Поле category id обязательно.'],
                    'account_id' => ['Поле account id обязательно.'],
                    'created_at' => ['Поле created at обязательно.'],
                    'is_debit' => ['Поле is debit обязательно.'],
                    'is_transfer' => ['Поле is transfer обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'amount' => 'test',
                    'category_id' => 99,
                    'account_id' => 99,
                    'created_at' => false,
                    'is_debit' => 'test',
                    'is_transfer' => 'test',
                ],
                'errors' => [
                    'amount' => ['Значение поля amount должно быть числом.'],
                    'category_id' => ['Такого значения не существует.'],
                    'account_id' => ['Такого значения не существует.'],
                    'created_at' => ['Значение поля created at должно быть корректной датой.'],
                    'is_debit' => ['Значение поля is debit должно быть логического типа.'],
                    'is_transfer' => ['Значение поля is transfer должно быть логического типа.'],
                ],
            ],
        ];
    }
}
