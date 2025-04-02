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

        $transactionCategories = $categories->take(2)->map(function (Category $category) {
            return [
                'category_id' => $category->getKey(),
            ];
        });

        $transactionForPlannedBudget = Transaction::factory(2)
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

        $plannedBudgetCollection = $budgets->map(function (Budget $budget) use ($transactionForPlannedBudget) {
            $parentCategory = $budget->category;

            // Get all transactions for this category
            $categoryTransactions = $transactionForPlannedBudget->filter(function (Transaction $transaction) use ($parentCategory) {
                $transactionParentCategory = $transaction->category->parentCategory()->first();
                return $transactionParentCategory->getKey() === $parentCategory->getKey();
            });

            // Calculate total spent for this category
            $totalSpent = $categoryTransactions->sum(function (Transaction $transaction) {
                return abs($transaction->amount);
            });

            return [
                'id' => $parentCategory->getKey(),
                'name' => $parentCategory->name,
                'total_spent' => $totalSpent / 100,
                'budget_amount' => $budget->amount / 100,
                'difference' => ($totalSpent - $budget->amount) / 100,
                'execution_rate' => $budget->amount > 0
                    ? round(($totalSpent / $budget->amount) * 100, 2)
                    : 0,
            ];
        })->sortByDesc('budget_amount');


        $notPlannedBudgetCollection = $transactionForNotPlannedBudget->map(function (Transaction $transaction) {
            $parentCategory = $transaction->category->parentCategory()->first();

            return [
                'id' => $parentCategory->getKey(),
                'name' => $parentCategory->name,
                'total_spent' => abs($transaction->amount) / 100,
                'budget_amount' => 0,
                'difference' => abs($transaction->amount) / 100,
                'execution_rate' => 0,
            ];
        })->sortByDesc('total_spent');

        $response->assertExactJson([
            'data' => [
                'period_on' => $date->translatedFormat('Y F'),
                'total_spent' => round((
                        abs($transactionForPlannedBudget->sum('amount')) + abs($transactionForNotPlannedBudget->sum('amount'))
                    ) / 100, 2)
                ,
                'total_budget' => round($budgets->sum('amount') / 100, 2),
                'budget_planned' => [
                    'data' => $plannedBudgetCollection->values()->all(),
                    'total_budget' => $total = round($plannedBudgetCollection->sum('budget_amount'), 2),
                    'total_spent' => $totalSpent =round($plannedBudgetCollection->sum('total_spent'), 2),
                    'difference' => round($totalSpent - $total, 2),
                    'execution_rate' => $total > 0
                        ? round(($totalSpent / $total) * 100, 2)
                        : 0
                    ,
                ],
                'budget_not_planned' => [
                    'data' => $notPlannedBudgetCollection->values()->all(),
                    'total_spent' => round($notPlannedBudgetCollection->sum('total_spent'), 2),
                ],
            ],
        ]);
    }
}
