<?php

namespace App\Services;

use App\Http\Requests\Api\v1\DashboardRequest;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    use ServiceInstance;

    public function getBalance(): float
    {
        return Transaction::query()->sum('amount') / 100;
    }

    public function getTransactionsByType(DashboardRequest $request, bool $isDebit): Collection
    {
        $builder = Transaction::query()
            ->where('is_debit', $isDebit)
            ->where('is_transfer', false)
            ->selectRaw('SUM(amount) as total, DATE_FORMAT(created_at, "%Y %M") as month')
            ->groupBy('month')
        ;

        if ($request->months) {
            $builder = $builder->whereBetween('created_at', $this->getDatesFilter($request));
        }

        $data = $builder->get()->keyBy('month');
        if (!$data->count()) {
            return $this->createResponse($data, 'total', true);
        }

        return $this->prepareResponse($data, $request);
    }

    public function getTotalByMonths(DashboardRequest $request): Collection
    {
        $builder = Transaction::query()
            ->selectRaw('SUM(amount) as total, DATE_FORMAT(created_at, "%Y %M") as month')
            ->groupBy('month')
        ;

        if ($request->months) {
            $builder = $builder->whereBetween('created_at', $this->getDatesFilter($request));
        }

        $data = $builder->get()->keyBy('month');

        if (!$data->count()) {
            return $this->createResponse($data, 'balance');
        }

        $initialBalance = 0;
        $months = $this->getMonths($request);

        $items = $months->map(function ($month, $key) use ($data, &$initialBalance) {
            $total = isset($data[$month]) ? $data[$month]->total : 0;
            $balance = $initialBalance + $total;
            $percentage = $this->getPercentageBetween($initialBalance, $balance);
            $initialBalance = $balance;

            return [
                'id' => $key,
                'total' => $total / 100,
                'month' => $this->getMonth($month),
                'percentage' => $percentage,
                'down' => $percentage < 0,
                'balance' => $balance / 100,
            ];
        });

        return $this->createResponse($items, 'balance');
    }

    private function getMonths(DashboardRequest $request): Collection
    {
        $dates = $this->getDatesFilter($request);
        $dateStart = $dates[0];
        $dateEnd = $dates[1];

        $months = collect();
        while ($dateStart->lte($dateEnd)) {
            $months->add($dateStart->format('Y F'));
            $dateStart->addMonth();
        }

        return $months;
    }

    private function getDatesFilter(DashboardRequest $request): array
    {
        // No transactions in the database
        if (!$lastTransaction = Transaction::query()->orderByDesc('created_at')->first()) {
            return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }

        // All transactions in the database
        if (!$request->months) {
            $startDate = Transaction::query()->orderBy('created_at')->first()->created_at->startOfMonth();
            $endDate = $lastTransaction->created_at->endOfMonth();

            return [$startDate, $endDate];
        }

        // Create dates by selected month count
        $startDate = Carbon::now()->subMonths($request->months)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($lastTransaction->created_at->endOfMonth()->isBefore($endDate)) {
            $endDate = $lastTransaction->created_at->endOfMonth();
        }

        return [$startDate, $endDate];
    }

    private function prepareResponse(Collection $data, DashboardRequest $request): Collection
    {
        $previousItemTotal = 0;
        $months = $this->getMonths($request);

        $items = $months->map(function ($month, $key) use ($data, &$previousItemTotal) {
            $total = isset($data[$month]) ? $data[$month]->total : 0;
            $percentage = $this->getPercentageBetween($previousItemTotal, $total);
            $previousItemTotal = $total;

            return [
                'id' => $key,
                'total' => $total / 100,
                'month' => $this->getMonth($month),
                'percentage' => $percentage,
                'down' => $percentage < 0,
            ];
        });

        return $this->createResponse($items, 'total', true);
    }

    private function getPercentageBetween(int $initAmount, int $endAmount): float
    {
        if ($initAmount === 0) {
            return 0;
        }

        $percentage = (($endAmount - $initAmount) / $initAmount) * 100;

        return round($percentage, 2);
    }

    private function getMonth(string $month): string
    {
        $date = Carbon::createFromFormat('Y F', $month);

        return $date->translatedFormat('Y F');
    }

    private function createResponse(Collection $items, string $chartDataKey, bool $positive = false): Collection
    {
        $total = round($items->sum('total'), 2);

        $response = collect();
        $chat = collect();
        $chat->put('labels', $items->pluck('month')->values());
        $chat->put('data', $items->pluck($chartDataKey)->map(function ($total) use ($positive) {
            return $positive ? abs($total) : $total;
        })->values());
        $response->put('data', $items->sortByDesc('id')->values());
        $response->put('chart', $chat);
        $response->put('total', $total);

        return $response;
    }

    public function totalByCategories(DashboardRequest $request, bool $isDebit): Collection
    {
        $builder = Category::query()
            ->select([
                'categories.id',
                'categories.name as title',
                DB::raw('sum(transactions.amount) as total'),
            ])
            ->where('transactions.is_debit', $isDebit)
            ->where('transactions.is_transfer', false)
            ->whereNotNull('parent_id')
            ->whereBetween('transactions.created_at', $this->getDatesFilter($request))
            ->join('transactions', 'categories.id', '=', 'transactions.category_id')
            ->groupBy(['categories.id', 'categories.name'])
            ->orderBy('total', $isDebit ? 'desc' : 'asc')
        ;

        if ($request->category_count) {
            $builder->limit($request->category_count);
        }

        return $builder->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'total' => $item->total / 100,
            ];
        });
    }
}
