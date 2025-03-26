<?php

namespace App\Services;

use App\Http\Requests\Api\v1\BudgetAnalyticsChildCategoryRequest;
use App\Http\Requests\Api\v1\BudgetAnalyticsRequest;
use App\Models\Transaction;
use App\Services\Budget\BudgetService;
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
}
