<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardDebitByCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCanSeeDebitAmountGroupedByCategories(): void
    {
        $now = Carbon::now();
        $oneMonthAgo = Carbon::now()->subMonth();
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        /** @var Category $debitCategory1 */
        $debitCategory1 = Category::factory()->withParentCategory()->create(['name' => 'Debit1']);
        /** @var Category $debitCategory2 */
        $debitCategory2 = Category::factory()->withParentCategory()->create(['name' => 'Debit2']);

        Transaction::factory(4)
            ->sequence(
                ['amount' => 20020, 'is_debit' => true, 'category_id' => $debitCategory1->getKey(), 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -10000, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => 10050, 'is_debit' => true, 'category_id' => $debitCategory2->getKey(), 'is_transfer' => false, 'created_at' => $oneMonthAgo],
                ['amount' => -15022, 'is_debit' => false, 'is_transfer' => true, 'created_at' => $now],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.debit-by-categories'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                [
                    'id' => $debitCategory1->getKey(),
                    'title' => $debitCategory1->name,
                    'total' => 200.20,
                ],
                [
                    'id' => $debitCategory2->getKey(),
                    'title' => $debitCategory2->name,
                    'total' => 100.50,
                ],
            ],
        ]);
    }

    public function testItReturnsNoDataWhenNoDebitTransactionsForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        Transaction::factory(3)
            ->sequence(
                ['amount' => 20020, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -10000, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 10050, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.debit-by-categories'), ['months' => 1, 'category_count' => 20]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [],
        ]);
    }

    public function testItReturnsFilteredDebitCategoriesForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        /** @var Category $debitCategory1 */
        $debitCategory1 = Category::factory()->withParentCategory()->create(['name' => 'Debit1']);
        $debitCategory2 = Category::factory()->withParentCategory()->create(['name' => 'Debit2']);

        Transaction::factory(3)
            ->sequence(
                ['amount' => 20020, 'is_debit' => true, 'category_id' => $debitCategory1->getKey(), 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -10000, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => 10000, 'is_debit' => true, 'category_id' => $debitCategory2->getKey(), 'is_transfer' => true, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.debit-by-categories'), ['months' => 3, 'category_count' => 20]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                [
                    'id' => $debitCategory1->getKey(),
                    'title' => $debitCategory1->name,
                    'total' => 200.20,
                ],
            ],
        ]);
    }
}
