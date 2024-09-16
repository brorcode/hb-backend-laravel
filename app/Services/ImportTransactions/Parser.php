<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Log;

abstract class Parser
{
    private CategoryPointerService $categoryPointerService;

    public function __construct(CategoryPointerService $service)
    {
        $this->categoryPointerService = $service;
    }

    protected function getDate(string $date, $fromFormat = null): ?Carbon
    {
        try {
            if ($fromFormat) {
                return Carbon::createFromFormat($fromFormat, $date);
            }

            return Carbon::parse($date);
        } catch (InvalidFormatException $exception) {
            Log::debug($exception->getMessage());

            return null;
        }
    }

    protected function getAmount(string $amount): float
    {
        $amount = str_replace(',', '.', $amount);

        return (float) $amount;
    }

    protected function getCategoryName(string $name, bool $isParent, ?string $childCategoryName = null): string
    {
        if ($isParent) {
            return $this->categoryPointerService->getParentCategoryName($name, $childCategoryName);
        }

        return $this->categoryPointerService->getChildCategoryName($name);
    }

    protected function trimValues(array $row): array
    {
        return array_map('trim', $row);
    }

    public function getLatestImportedDate(Account $account): ?Carbon
    {
        return Transaction::query()
            ->where('account_id', $account->getKey())
            ->latest()->first('created_at')->created_at ?? null
        ;
    }

    protected function isImportedAlready(Carbon $date, ?Carbon $latestImportedDate): bool
    {
        return $latestImportedDate && $date->lte($latestImportedDate);
    }
}
