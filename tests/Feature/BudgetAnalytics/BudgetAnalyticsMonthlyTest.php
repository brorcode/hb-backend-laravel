<?php

namespace Tests\Feature\BudgetAnalytics;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetAnalyticsMonthlyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetAnalyticsMonthly(): void
    {
        $date = now();

        $categories = Category::factory(5)->withParentCategory()->create();
        $budgetCategories = $categories->map(function (Category $category) {
           return [
               'category_id' => $category->parent_id,
           ];
        });

        $budgets = Budget::factory(5)
            ->sequence(...$budgetCategories)
            ->create([
                'period_on' => $date->clone()->startOfMonth(),
            ])
        ;

        $transactionCategories = $categories->map(function (Category $category) {
            return [
                'category_id' => $category->getKey(),
            ];
        });

        $transactionForPlannedBudget = Transaction::factory(5)
            ->sequence(...$transactionCategories)
            ->create([
                'is_debit' => false,
                'is_transfer' => false,
                'created_at' => $date,

            ])
        ;
        $transactionForNotPlannedBudget = Transaction::factory(5)
            ->create([
                'is_debit' => false,
                'is_transfer' => false,
                'created_at' => $date,

            ])
        ;

        $response = $this->postJson(route('api.v1.budget-analytics.monthly'), [
            'period_on' => [
                'year' => $date->year,
                'month' => $date->month - 1,
            ],
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'period_on' => $date->translatedFormat('Y F'),
                'budget_planned' => $transactionForPlannedBudget->map(function (Transaction $transaction) use ($budgets) {
                    $parentCategory = $transaction->category->parentCategory()->first();

                    /** @var Budget $budget */
                    $budget = $budgets->where('category_id', $parentCategory->getKey())->first();

                    $transactionAmount = abs($transaction->amount);

                    return [
                        'id' => $parentCategory->getKey(),
                        'name' => $parentCategory->name,
                        'total_spent' => $transactionAmount / 100,
                        'budget_amount' => $budget->amount / 100,
                        'difference' => ($transactionAmount - $budget->amount) / 100,
                        'execution_rate' => $budget->amount > 0
                            ? round(($transactionAmount / $budget->amount) * 100, 2)
                            : 0,
                    ];
                })->sortByDesc('total_spent')->values()->all(),
                'budget_not_planned' => $transactionForNotPlannedBudget->map(function (Transaction $transaction) {
                    $parentCategory = $transaction->category->parentCategory()->first();

                    return [
                        'id' => $parentCategory->getKey(),
                        'name' => $parentCategory->name,
                        'total_spent' => abs($transaction->amount) / 100,
                        'budget_amount' => 0,
                        'difference' => abs($transaction->amount) / 100,
                        'execution_rate' => 0,
                    ];
                })->sortByDesc('total_spent')->values()->all(),
                'total_spent' => round((
                    abs($transactionForPlannedBudget->sum('amount')) + abs($transactionForNotPlannedBudget->sum('amount'))
                    ) / 100, 2)
                ,
                'total_budget' => round($budgets->sum('amount') / 100, 2),
            ],
        ]);
    }
}
