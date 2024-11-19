<?php

namespace Tests\Feature\Dictionary;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountForImportDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testAccountForImportDictionaryList(): void
    {
        Account::factory(3)->create([
            'is_archived' => true,
        ]);
        Account::factory(3)->create([
            'integration_id' => null,
            'is_archived' => false,
        ]);
        $accounts = Account::factory(3)->create([
            'is_archived' => false,
        ]);

        $response = $this->postJson(route('api.v1.dictionary.accounts-for-import'));

        $data = $accounts->map(function (Account $account) {
            return [
                'id' => $account->getKey(),
                'name' => $account->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testAccountForImportDictionaryListWithSearch(): void
    {
        $accounts = Account::factory(2)
            ->sequence(
                ['name' => 'Name 1', 'is_archived' => false],
                ['name' => 'Name 2', 'is_archived' => false],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.accounts-for-import'), [
            'q' => 'Name 1',
        ]);

        $data = $accounts->filter(function (Account $account) {
            return $account->name === 'Name 1';
        })->map(function (Account $account) {
            return [
                'id' => $account->getKey(),
                'name' => $account->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }
}
