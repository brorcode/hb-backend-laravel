<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\BudgetAnalyticsChildCategoryRequest;
use App\Http\Requests\Api\v1\BudgetAnalyticsRequest;
use App\Services\Budget\BudgetService;
use App\Services\BudgetAnalyticsService;
use Illuminate\Http\JsonResponse;

class BudgetAnalyticsController extends ApiController
{
    private BudgetAnalyticsService $service;

    public function __construct()
    {
        $this->service = BudgetAnalyticsService::create();
    }

    public function getMonthlyAnalytics(BudgetAnalyticsRequest $request): JsonResponse
    {
        $budget = $this->service->getBudget($request);

        return response()->json([
            'data' => [
                'period_on' => BudgetService::getPeriodOnFromArray($request->period_on)->translatedFormat('Y F'),
                'budget_planned' => $budget->where('budget_amount', '>', '0')->values()->all(),
                'budget_not_planned' => $budget->where('budget_amount', '=', '0')->values()->all(),
                'total_spent' => $budget->sum('total_spent'),
                'total_budget' => $budget->where('budget_amount', '>', '0')->sum('budget_amount'),
            ]
        ]);
    }

    public function getChildCategories(BudgetAnalyticsChildCategoryRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getChildCategories($request),
        ]);
    }
}
