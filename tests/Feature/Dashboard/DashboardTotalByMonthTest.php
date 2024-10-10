<?php

namespace Tests\Feature\Dashboard;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTotalByMonthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCanSeeTotalAmountGroupedByMonth(): void
    {
        $now = Carbon::now();
        $oneMonthAgo = Carbon::now()->subMonth();
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        Transaction::factory(4)
            ->sequence(
                ['amount' => 200.20, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -100.00, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => 100.50, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $oneMonthAgo],
                ['amount' => -150.22, 'is_debit' => false, 'is_transfer' => true, 'created_at' => $now],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.total-by-month'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'data' => [
                    [
                        'id' => 2,
                        'total' => -150.22,
                        'month' => $now->translatedFormat('Y F'),
                        'percentage' => -74.85,
                        'down' => true,
                        'balance' => 50.48,
                    ],
                    [
                        'id' => 1,
                        'total' => 100.50,
                        'month' => $oneMonthAgo->translatedFormat('Y F'),
                        'percentage' => 100.30,
                        'down' => false,
                        'balance' => 200.70,
                    ],
                    [
                        'id' => 0,
                        'total' => 100.20,
                        'month' => $twoMonthsAgo->translatedFormat('Y F'),
                        'percentage' => 0,
                        'down' => false,
                        'balance' => 100.20,
                    ],
                ],
                'chart' => [
                    'labels' => [
                        $twoMonthsAgo->translatedFormat('Y F'),
                        $oneMonthAgo->translatedFormat('Y F'),
                        $now->translatedFormat('Y F'),
                    ],
                    'data' => [100.20, 200.70, 50.48],
                ],
            ],
        ]);
    }

    public function testItReturnsNoDataWhenNoTotalTransactionsForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        Transaction::factory(3)
            ->sequence(
                ['amount' => 200.20, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -100.00, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 100.50, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.total-by-month'), ['months' => 1]);

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

    public function testItReturnsFilteredTotalDataForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $fourMonthsAgo = Carbon::now()->subMonths(4);

        Transaction::factory(5)
            ->sequence(
                ['amount' => 1000.32, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $fourMonthsAgo],
                ['amount' => 540.25, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $fourMonthsAgo],
                ['amount' => 500.20, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -600.90, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 200.00, 'is_debit' => true, 'is_transfer' => true, 'created_at' => $twoMonthsAgo],
                ['amount' => 2500.70, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -100.00, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.total-by-month'), ['months' => 4]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'data' => [
                    [
                        'id' => 2,
                        'total' => 200,
                        'month' => $twoMonthsAgo->translatedFormat('Y F'),
                        'percentage' => 55.65,
                        'down' => false,
                        'balance' => 559.37,
                    ],
                    [
                        'id' => 1,
                        'total' => -100.70,
                        'month' => $threeMonthsAgo->translatedFormat('Y F'),
                        'percentage' => -21.89,
                        'down' => true,
                        'balance' => 359.37,
                    ],
                    [
                        'id' => 0,
                        'total' => 460.07,
                        'month' => $fourMonthsAgo->translatedFormat('Y F'),
                        'percentage' => 0,
                        'down' => false,
                        'balance' => 460.07,
                    ],
                ],
                'chart' => [
                    'labels' => [
                        $fourMonthsAgo->translatedFormat('Y F'),
                        $threeMonthsAgo->translatedFormat('Y F'),
                        $twoMonthsAgo->translatedFormat('Y F'),
                    ],
                    'data' => [460.07, 359.37, 559.37],
                ],
            ],
        ]);
    }
}
