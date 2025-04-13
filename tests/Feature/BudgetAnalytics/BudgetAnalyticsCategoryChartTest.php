<?php

namespace Tests\Feature\BudgetAnalytics;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAnalyticsCategoryChartTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private Collection $transactions1;
    private Collection $transactions2;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();

        $this->category = Category::factory()->withParentCategory()->create();
        $this->transactions1 = Transaction::factory(2)
            ->create([
                'is_debit' => false,
                'is_transfer' => false,
                'category_id' => $this->category->getKey(),
                'created_at' => now(),
            ])
        ;
        $this->transactions2 = Transaction::factory(2)
            ->create([
                'is_debit' => false,
                'is_transfer' => false,
                'category_id' => $this->category->getKey(),
                'created_at' => now()->subMonth(),
            ])
        ;
    }

    public function testBudgetAnalyticsParentCategoryChart(): void
    {
        $response = $this->postJson(route('api.v1.budget-analytics.category-chart'), [
            'category_id' => $this->category->parent_id,
            'is_child' => false,
        ]);
        $response->assertOk();

        /** @var Transaction $transaction1 */
        $transaction1 = $this->transactions1->first();
        /** @var Transaction $transaction2 */
        $transaction2 = $this->transactions2->first();

        $response->assertExactJson([
            'data' => [
                'labels' => [
                    $transaction2->created_at->locale('ru')->translatedFormat('Y F'),
                    $transaction1->created_at->locale('ru')->translatedFormat('Y F'),
                ],
                'data' => [
                    [
                        'name' => $this->getChartName(),
                        'color' => '#4F46E5',
                        'data' => [
                            round(abs($this->transactions2->sum('amount') / 100), 2),
                            round(abs($this->transactions1->sum('amount') / 100), 2),
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testBudgetAnalyticsChildCategoryChart(): void
    {
        $response = $this->postJson(route('api.v1.budget-analytics.category-chart'), [
            'category_id' => $this->category->getKey(),
            'is_child' => true,
        ]);
        $response->assertOk();

        /** @var Transaction $transaction1 */
        $transaction1 = $this->transactions1->first();
        /** @var Transaction $transaction2 */
        $transaction2 = $this->transactions2->first();

        $response->assertExactJson([
            'data' => [
                'labels' => [
                    $transaction2->created_at->locale('ru')->translatedFormat('Y F'),
                    $transaction1->created_at->locale('ru')->translatedFormat('Y F'),
                ],
                'data' => [
                    [
                        'name' => $this->getChartName(true),
                        'color' => '#4F46E5',
                        'data' => [
                            round(abs($this->transactions2->sum('amount') / 100), 2),
                            round(abs($this->transactions1->sum('amount') / 100), 2),
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getChartName(bool $is_child = false): string
    {
        $categoryName = $is_child ? $this->category->name : $this->category->parentCategory->name;
        $total = (abs($this->transactions1->sum('amount')) + abs($this->transactions2->sum('amount'))) / 100;
        $average = round($total / 2, 2);

        return "$categoryName (в среднем: $average ₽, всего: $total ₽)";
    }
}
