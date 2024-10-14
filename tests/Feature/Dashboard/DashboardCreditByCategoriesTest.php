<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCreditByCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCanSeeCreditAmountGroupedByCategories(): void
    {
        $now = Carbon::now();
        $oneMonthAgo = Carbon::now()->subMonth();
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        /** @var Category $creditCategory1 */
        $creditCategory1 = Category::factory()->withParentCategory()->create(['name' => 'Credit1']);
        /** @var Category $creditCategory2 */
        $creditCategory2 = Category::factory()->withParentCategory()->create(['name' => 'Credit2']);

        Transaction::factory(4)
            ->sequence(
                ['amount' => -20020, 'is_debit' => false, 'category_id' => $creditCategory1->getKey(), 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => 10000, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -10050, 'is_debit' => false, 'category_id' => $creditCategory2->getKey(), 'is_transfer' => false, 'created_at' => $oneMonthAgo],
                ['amount' => 15022, 'is_debit' => true, 'is_transfer' => true, 'created_at' => $now],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-categories'));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                [
                    'id' => $creditCategory1->getKey(),
                    'title' => $creditCategory1->name,
                    'total' => -200.20,
                ],
                [
                    'id' => $creditCategory2->getKey(),
                    'title' => $creditCategory2->name,
                    'total' => -100.50,
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
                ['amount' => -20020, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 10000, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => -10050, 'is_debit' => false, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-categories'), ['months' => 1, 'category_count' => 20]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [],
        ]);
    }

    public function testItReturnsFilteredCreditDataForSelectedPeriod(): void
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $threeMonthsAgo = Carbon::now()->subMonths(3);

        /** @var Category $creditCategory1 */
        $creditCategory1 = Category::factory()->withParentCategory()->create(['name' => 'Credit1']);
        $creditCategory2 = Category::factory()->withParentCategory()->create(['name' => 'Credit2']);

        Transaction::factory(3)
            ->sequence(
                ['amount' => -20020, 'is_debit' => false, 'category_id' => $creditCategory1->getKey(), 'is_transfer' => false, 'created_at' => $threeMonthsAgo],
                ['amount' => 10000, 'is_debit' => true, 'is_transfer' => false, 'created_at' => $twoMonthsAgo],
                ['amount' => -10000, 'is_debit' => false, 'category_id' => $creditCategory2->getKey(), 'is_transfer' => true, 'created_at' => $twoMonthsAgo],
            )
            ->create()
        ;

        $response = $this->postJson(route('api.v1.dashboard.credit-by-categories'), ['months' => 3, 'category_count' => 20]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                [
                    'id' => $creditCategory1->getKey(),
                    'title' => $creditCategory1->name,
                    'total' => -200.20,
                ],
            ],
        ]);
    }
}
