<?php

namespace Tests\Feature\Dictionary;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testAccountDictionaryList(): void
    {
        $accounts = Account::factory(11)->create();
        $response = $this->postJson(route('api.v1.dictionary.accounts'));

        $data = $accounts->take(10)->map(function (Account $account) {
            return [
                'id' => $account->getKey(),
                'name' => $account->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testAccountDictionaryListWithSearch(): void
    {
        $accounts = Account::factory(2)
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.accounts'), [
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
