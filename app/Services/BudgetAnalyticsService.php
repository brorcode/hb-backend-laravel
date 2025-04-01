<?php

namespace App\Services;

use App\Http\Requests\Api\v1\BudgetAnalyticsChartRequest;
use App\Http\Requests\Api\v1\BudgetAnalyticsChildCategoryRequest;
use App\Http\Requests\Api\v1\BudgetAnalyticsRequest;
use App\Models\Transaction;
use App\Services\Budget\BudgetService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BudgetAnalyticsService
{
    use ServiceInstance;

    public function getBudget(BudgetAnalyticsRequest $request): Collection
    {
        $periodOn = BudgetService::getPeriodOnFromArray($request->period_on);
        $startDate = $periodOn->clone()->startOfMonth()->startOfDay();
        $endDate = $periodOn->clone()->endOfMonth()->endOfDay();

        return Transaction::query()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->leftJoin('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
            ->leftJoin('budgets', function($join) use ($periodOn) {
                $join->on('budgets.category_id', '=', 'parent_categories.id')
                    ->where('budgets.period_on', $periodOn->toDateString());
            })
            ->where('transactions.is_debit', false)
            ->where('transactions.is_transfer', false)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->groupBy([
                'parent_categories.id',
                'parent_categories.name',
                'budgets.amount',
            ])
            ->orderBy('total_spent')
            ->select([
                'parent_categories.id as category_id',
                'parent_categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total_spent'),
                DB::raw('COALESCE(budgets.amount, 0) as budget_amount'),
            ])
            ->get()
            ->map(function($row) {
                $totalSpent = abs($row->total_spent);

                return [
                    'id' => $row->category_id,
                    'name' => $row->category_name,
                    'total_spent' => $totalSpent / 100,
                    'budget_amount' => $row->budget_amount / 100,
                    'difference' => ($totalSpent - $row->budget_amount) / 100,
                    'execution_rate' => $row->budget_amount > 0
                        ? round(($totalSpent / $row->budget_amount) * 100, 2)
                        : 0,
                ];
            })
        ;
    }

    public function getChildCategories(BudgetAnalyticsChildCategoryRequest $request): Collection
    {
        $periodOn = BudgetService::getPeriodOnFromArray($request->period_on);
        $startDate = $periodOn->clone()->startOfMonth()->startOfDay();
        $endDate = $periodOn->clone()->endOfMonth()->endOfDay();

        return Transaction::query()
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->leftJoin('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
            ->where('transactions.is_debit', false)
            ->where('transactions.is_transfer', false)
            ->where('parent_categories.id', $request->parent_category_id)
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->groupBy([
                'categories.id',
                'categories.name',
            ])
            ->orderBy('total_spent')
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total_spent'),
            ])
            ->get()
            ->map(function($row) {
                return [
                    'id' => $row->category_id,
                    'name' => $row->category_name,
                    'total_spent' => abs($row->total_spent) / 100,
                ];
            })
        ;
    }

    public function getChart(BudgetAnalyticsChartRequest $request): Collection
    {
        $builder = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->join('categories as parent_categories', 'categories.parent_id', '=', 'parent_categories.id')
            ->where('transactions.is_debit', false)
            ->where('transactions.is_transfer', false)
            ->whereBetween('transactions.created_at', [
                now()->subYear()->startOfMonth()->startOfDay(),
                now()->endOfMonth()->endOfDay(),
            ])
            ->groupBy([
                'parent_categories.id',
                'parent_categories.name',
                'month',
            ])
            ->select(
                'parent_categories.id as category_id',
                'parent_categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total_spent'),
                DB::raw('DATE_FORMAT(transactions.created_at, "%Y %M") as month'),
            )
            ->orderBy('transactions.created_at')
            ->orderBy('total_spent')
        ;

        $categoryTable = $request->is_child ? 'categories.id' : 'parent_categories.id';
        $builder->where($categoryTable, $request->category_id);

        $data = $builder->get();

        $months = $data->pluck('month')->unique()->values();
        $categories = $data->pluck('category_name')->unique()->values();

        $response = $categories->map(function ($categoryName) use ($data, $months) {
            // Get all data points for this category
            $categoryData = $data->where('category_name', $categoryName);

            // Extract amounts for each month in order
            $amounts = $months->map(function ($month) use ($categoryData) {
                $dataPoint = $categoryData->firstWhere('month', $month);
                return $dataPoint ? abs($dataPoint->total_spent) / 100 : 0;
            })->values();

            $total = round($amounts->sum(), 2);
            $average = round($amounts->sum() / $amounts->count(), 2);

            return [
                'name' => "$categoryName (в среднем: $average ₽, всего: $total ₽)",
                'color' => '#4F46E5',
                'data' => $amounts,
            ];
        })->values();

        return collect([
            'labels' => $months->map(function ($month) {
                $date = Carbon::createFromFormat('Y F', $month);

                return $date->translatedFormat('Y F');
            }),
            'data' => $response->all(),
        ]);
    }

    public function getBudgetTotalSpent(Collection $budget): float
    {
        return round($budget->sum('total_spent'), 2);
    }
    public function getTotalBudget(Collection $budget): float
    {
        return round($budget->where('budget_amount', '>', '0')->sum('budget_amount'), 2);
    }

    public function getPlannedBudget(Collection $budget): array
    {
        $budgetCollection = $budget->where('budget_amount', '>', '0');

        return [
            'data' => $budgetCollection->values()->all(),
            'total_budget' => $total = round($budgetCollection->sum('budget_amount'), 2),
            'total_spent' => $totalSpent =round($budgetCollection->sum('total_spent'), 2),
            'difference' => round($totalSpent - $total, 2),
            'execution_rate' => $total > 0
                ? round(($totalSpent / $total) * 100, 2)
                : 0
            ,
        ];
    }

    public function getNotPlannedBudget(Collection $budget): array
    {
        $budgetCollection = $budget->where('budget_amount', '=', '0');

        return [
            'data' => $budgetCollection->values()->all(),
            'total_spent' => round($budgetCollection->sum('total_spent'), 2),
        ];
    }
}
