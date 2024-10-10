<?php

namespace Tests\Feature\Dashboard;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanSeeBalanceTotal(): void
    {
        $this->userLogin();

        Transaction::factory(4)
            ->sequence(
                ['amount' => 200.20, 'is_debit' => true, 'is_transfer' => false],
                ['amount' => -100.00, 'is_debit' => false, 'is_transfer' => false],
                ['amount' => 100.50, 'is_debit' => true, 'is_transfer' => false],
                ['amount' => -150.22, 'is_debit' => false, 'is_transfer' => true],
            )
            ->create()
        ;

        $response = $this->getJson(route('api.v1.dashboard.balance'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => 50.48,
        ]);
    }
}
