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

    public function debitByMonth(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTransactionsByType($request, true),
        ]);
    }

    public function creditByMonth(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTransactionsByType($request, false),
        ]);
    }

    public function totalByMonth(DashboardRequest $request, DashboardService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->getTotalByMonth($request),
        ]);
    }
}
