<?php

namespace Tests\Feature\Dashboard;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCreditByMonthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCanSeeCreditAmountGroupedByMonth(): void
    {
        $now = Carbon::now();
        $oneMonthAgo = Carbon::now()->subMonth();
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        Transaction::factory(4)
            ->sequence(
                ['amount' => -200.20, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => 100.00, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -100.50, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $oneMonthAgo],
                ['amount' => 150.22, 'is_debit' => true, 'is_transfer' => true, 'created_at' => $now],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-month'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'data' => [
                    [
                        'id' => 2,
                        'total' => 0,
                        'month' => $now->translatedFormat('Y F'),
                        'percentage' => -100,
                        'down' => true,
                    ],
                    [
                        'id' => 1,
                        'total' => -100.50,
                        'month' => $oneMonthAgo->translatedFormat('Y F'),
                        'percentage' => -49.8,
                        'down' => true,
                    ],
                    [
                        'id' => 0,
                        'total' => -200.20,
                        'month' => $twoMonthsAgo->translatedFormat('Y F'),
                        'percentage' => 0,
                        'down' => false,
                    ],
                ],
                'chart' => [
                    'labels' => [
                        $twoMonthsAgo->translatedFormat('Y F'),
                        $oneMonthAgo->translatedFormat('Y F'),
                        $now->translatedFormat('Y F'),
                    ],
                    'data' => [200.20, 100.50, 0],
                ],
            ],
        ]);
    }

    public function testItReturnsNoDataWhenNoCreditTransactionsForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        Transaction::factory(3)
            ->sequence(
                ['amount' => -200.20, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 100.00, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -100.50, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-month'), ['months' => 1]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'data' => [],
                'chart' => [
                    'labels' => [],
                    'data' => [],
                ],
            ],
        ]);
    }

    public function testItReturnsFilteredCreditDataForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        Transaction::factory(3)
            ->sequence(
                ['amount' => -200.20, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 100.00, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -100.00, 'is_debit' => false, 'is_transfer' => true, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-month'), ['months' => 3]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'data' => [
                    [
                        'id' => 1,
                        'total' => 0,
                        'month' => $twoMonthsAgo->translatedFormat('Y F'),
                        'percentage' => -100,
                        'down' => true,
                    ],
                    [
                        'id' => 0,
                        'total' => -200.20,
                        'month' => $threeMonthsAgo->translatedFormat('Y F'),
                        'percentage' => 0,
                        'down' => false,
                    ],
                ],
                'chart' => [
                    'labels' => [
                        $threeMonthsAgo->translatedFormat('Y F'),
                        $twoMonthsAgo->translatedFormat('Y F'),
                    ],
                    'data' => [200.20, 0],
                ],
            ],
        ]);
    }
}
