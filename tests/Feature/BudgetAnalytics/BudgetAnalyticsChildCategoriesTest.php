<?php

namespace Tests\Feature\BudgetAnalytics;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAnalyticsChildCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetAnalyticsChildCategories(): void
    {
        $date = now();
        $parentCategory = Category::factory()->create();

        /** @var Category $category1 */
        $category1 = Category::factory()->withParentCategory($parentCategory)->create();
        /** @var Category $category2 */
        $category2 = Category::factory()->withParentCategory($parentCategory)->create();

        $transactions1 = Transaction::factory(5)
            ->create([
                'category_id' => $category1->getKey(),
                'is_debit' => false,
                'is_transfer' => false,
                'created_at' => $date,

            ])
        ;
        $transactions2 = Transaction::factory(5)
            ->create([
                'category_id' => $category2->getKey(),
                'is_debit' => false,
                'is_transfer' => false,
                'created_at' => $date,

            ])
        ;

        $response = $this->postJson(route('api.v1.budget-analytics.child-categories'), [
            'parent_category_id' => $parentCategory->getKey(),
            'period_on' => [
                'year' => $date->year,
                'month' => $date->month - 1,
            ],
        ]);

        $data = collect([
            [
                'id' => $category1->getKey(),
                'name' => $category1->name,
                'total_spent' => abs($transactions1->sum('amount')) / 100,
            ],
            [
                'id' => $category2->getKey(),
                'name' => $category2->name,
                'total_spent' => abs($transactions2->sum('amount')) / 100,
            ],
        ])->sortByDesc('total_spent');

        $response->assertOk();
        $response->assertExactJson([
            'data' => $data->values()->all(),
        ]);
    }
}
