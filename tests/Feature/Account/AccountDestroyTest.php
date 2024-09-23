<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AccountDestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function testAccountDestroy(): void
    {
        $this->userLogin();

        $accounts = Account::factory(2)->create();
        $accountToBeDeleted = $accounts->last();

        $this->assertCount(2, Account::all());
        $this->assertDatabaseHas((new Account())->getTable(), [
            'name' => $accountToBeDeleted->name,
        ]);
        $response = $this->deleteJson(route('api.v1.accounts.destroy', $accountToBeDeleted));

        $this->assertCount(1, Account::all());
        $this->assertDatabaseMissing((new Account())->getTable(), [
            'name' => $accountToBeDeleted->name,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Аккаунт удален',
        ]);
    }
}
