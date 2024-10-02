<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testAccountShow(): void
    {
        /** @var Account $account */
        $account = Account::factory()
            ->has(Transaction::factory()->count(10))
            ->create()
        ;

        $response = $this->getJson(route('api.v1.accounts.show', $account));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $account->getKey(),
                'name' => $account->name,
                'amount' => $account->transactions->sum('amount'),
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ],
        ]);
    }

    public function testAccountStore(): void
    {
        $this->assertCount(0, Account::all());

        $response = $this->postJson(route('api.v1.accounts.store'), [
            'name' => 'test',
        ]);

        $this->assertCount(1, Account::all());
        $this->assertDatabaseHas((new Account())->getTable(), [
            'name' => 'test',
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Аккаунт создан',
        ]);
    }

    #[DataProvider('invalidAccountDataProvider')]
    public function testAccountCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        Account::factory()->create(['name' => 'existing account name']);

        $response = $this->postJson(route('api.v1.accounts.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testAccountUpdate(): void
    {
        /** @var Account $account */
        $account = Account::factory()
            ->has(Transaction::factory()->count(10))
            ->create(['name' => 'test account name'])
        ;

        $this->assertCount(1, Account::all());
        $this->assertDatabaseMissing((new Account())->getTable(), [
            'name' => 'new account name',
        ]);

        $response = $this->putJson(route('api.v1.accounts.update', $account), [
            'name' => 'new account name',
        ]);

        $this->assertCount(1, Account::all());
        $this->assertDatabaseHas((new Account())->getTable(), [
            'name' => 'new account name',
        ]);

        $response->assertOk();

        $freshAccount = $account->fresh();
        $response->assertExactJson([
            'message' => 'Аккаунт обновлен',
            'data' => [
                'id' => $freshAccount->getKey(),
                'name' => 'new account name',
                'amount' => $freshAccount->transactions->sum('amount'),
                'created_at' => $freshAccount->created_at,
                'updated_at' => $freshAccount->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidAccountDataProvider')]
    public function testAccountCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        Account::factory()->create(['name' => 'existing account name']);
        $accountForUpdate = Account::factory()->create(['name' => 'test account name']);

        $response = $this->putJson(route('api.v1.accounts.update', $accountForUpdate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testAccountCanBeUpdatedWithOutNameChange(): void
    {
        /** @var Account $account */
        $account = Account::factory()->create(['name' => 'existing account name']);

        $response = $this->putJson(route('api.v1.accounts.update', $account), [
            'name' => 'existing account name',
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Аккаунт обновлен',
            'data' => [
                'id' => $account->getKey(),
                'name' => 'existing account name',
                'amount' => 0,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ],
        ]);
    }

    public function testAccountCanBeCreatedIfAnotherUserHasTheSameAccountName(): void
    {
        $this->userLogin();
        Account::factory()->create(['name' => 'account 1']);

        $this->userLogin();
        $response = $this->postJson(route('api.v1.accounts.store'), [
            'name' => 'account 1',
        ]);

        $this->assertCount(
            2,
            Account::query()->withoutGlobalScope(OwnerScope::class)->where('name', 'account 1')->get()
        );

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Аккаунт создан',
        ]);
    }

    public static function invalidAccountDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'existing account name',
                ],
                'errors' => [
                    'name' => ['Такое название уже существует.'],
                ],
            ],
        ];
    }
}
