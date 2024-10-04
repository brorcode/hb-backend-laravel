<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    public function balance(): JsonResponse
    {
        return response()->json([
            'data' => Transaction::query()->sum('amount') / 100,
        ]);
    }

    public function debitByMonth(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => Transaction::query()
                ->where('is_debit', true)
                ->where('is_transfer', false)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount') / 100
            ,
        ]);
    }

    public function creditByMonth(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => Transaction::query()
                ->where('is_debit', false)
                    ->where('is_transfer', false)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount') / 100
            ,
        ]);
    }

    public function totalByMonth(): JsonResponse
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return response()->json([
            'data' => Transaction::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount') / 100
            ,
            // 'total' => Transaction::query()->select(DB::raw('SUM(amount) as total_amount, MONTH(created_at) as month, YEAR(created_at) as year'))
            //     ->groupBy('year', 'month')
            //     ->orderBy('year', 'desc')
            //     ->orderBy('month', 'desc')
            //     ->limit(10)
            //     ->get(),
        ]);
    }
}
