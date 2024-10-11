<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\DashboardRequest;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    public function balance(DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getBalance(),
        ]);
    }

    public function debitByMonths(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTransactionsByType($request, true),
        ]);
    }

    public function creditByMonths(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTransactionsByType($request, false),
        ]);
    }

    public function totalByMonths(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTotalByMonths($request),
        ]);
    }

    public function debitByCategories(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->totalByCategories($request, true),
        ]);
    }

    public function creditByCategories(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->totalByCategories($request, false),
        ]);
    }
}
