<?php

namespace App\Services\Budget;

use App\Exceptions\SystemException;
use App\Http\Requests\Api\v1\BudgetUpsertRequest;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Services\ServiceInstance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BudgetService
{
    use ServiceInstance;

    /**
     * @throws SystemException
     */
    public function store(BudgetUpsertRequest $request): void
    {
        if (BudgetTemplate::query()->doesntExist()) {
            throw new SystemException('Нужно создать хотя бы один шаблон бюджета.');
        }

        BudgetTemplate::query()
            ->chunkById(config('homebudget.chunk'), function (Collection $templates) use ($request) {
                $templates->each(function (BudgetTemplate $budgetTemplate) use ($request) {
                    $budget = new Budget();
                    $budget->period_on = self::getPeriodOnFromArray($request->period_on);
                    $budget->amount = $budgetTemplate->amount;
                    $budget->category_id = $budgetTemplate->category_id;
                    $budget->save();
                });
            })
        ;
    }

    public function show(int $date): Budget
    {
        return Budget::query()
            ->where('period_on', self::getPeriodOnFromInt($date)->toDateString())
            ->selectRaw('SUM(amount) as total, period_on')
            ->groupBy('period_on')
            ->first()
        ;
    }

    /**
     * @throws SystemException
     */
    public function destroy(int $date): void
    {
        $periodOn = self::getPeriodOnFromInt($date);
        if ($periodOn->lte(now())) {
            throw new SystemException('Нельзя удалить активный или завершенные бюджеты.');
        }

        Budget::query()
            ->where('period_on', $periodOn->toDateString())
            ->delete()
        ;
    }

    /**
     * Converts an integer representation of a period into a date string.
     *
     * @param int $date Integer representation of a period in format YYYYMM (e.g., 202501 for January 2025)
     *
     * @return Carbon Date, representing the first day of the specified month
     */
    public static function getPeriodOnFromInt(int $date): Carbon
    {
        $year = (int)($date / 100);
        $month = $date % 100;

        return Carbon::createFromDate($year, $month, 1);
    }

    /**
     * Converts a period data array into a date string.
     *
     * @param array $periodOnArray Period data array, must contain:
     *                            - 'year' (int): Year of the period
     *                            - 'month' (int): Month of the period (0-11, where 0 is January)
     * @return Carbon Date, representing the first day of the specified month
     */
    public static function getPeriodOnFromArray(array $periodOnArray): Carbon
    {
        return Carbon::createFromDate(
            $periodOnArray['year'],
            $periodOnArray['month'] + 1,
            1
        );
    }
}
