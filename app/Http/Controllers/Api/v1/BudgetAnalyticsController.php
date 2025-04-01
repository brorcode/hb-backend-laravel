<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\BudgetAnalyticsChartRequest;
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

    public function monthly(BudgetAnalyticsRequest $request): JsonResponse
    {
        $budget = $this->service->getBudget($request);

        return response()->json([
            'data' => [
                'period_on' => BudgetService::getPeriodOnFromArray($request->period_on)->translatedFormat('Y F'),
                'total_spent' => $this->service->getBudgetTotalSpent($budget),
                'total_budget' =>  $this->service->getTotalBudget($budget),
                'budget_planned' =>  $this->service->getPlannedBudget($budget),
                'budget_not_planned' =>  $this->service->getNotPlannedBudget($budget),
            ]
        ]);
    }

    public function childCategories(BudgetAnalyticsChildCategoryRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getChildCategories($request),
        ]);
    }

    public function categoryChart(BudgetAnalyticsChartRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getChart($request),
        ]);
    }
}
