<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Budget;
use App\Services\Budget\BudgetListService;
use App\Services\Budget\BudgetService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('budgetFiltersDataProvider')]
    public function testBudgetListServiceHandleFilters(int $count, array $sequence, string $filterKey, string $fieldKey, array $filters): void
    {
        $factory = Budget::factory()
            ->count($count)
        ;
        if (count($sequence) > 0) {
            $factory = $factory->sequence(...$sequence);
        }
        $factory->create();

        $service = BudgetListService::create();
        $request = new ListRequest();
        $request->merge([
            'page' => 1,
            'limit' => 10,
            'filters' => $filters,
        ]);
        $service->setRequest($request);
        $builder = $service->getListBuilder();
        $paginator = $builder->simplePaginate($request->limit);
        $data = $paginator->items();

        $this->assertCount($count, Budget::all());
        $this->assertCount(1, $data);

        $expectedValue = BudgetService::getPeriodOnFromArray($filters[$filterKey]['value'])->toDateString();
        $actualValue = $data[0][$fieldKey]->toDateString();

        $this->assertEquals($expectedValue, $actualValue);
    }

    public static function budgetFiltersDataProvider(): array
    {
        return [
            'filter_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'period_on' => '2025-01-01',
                    ],
                    [
                        'period_on' => '2025-02-01',
                    ]
                ],
                'filterKey' => 'period_on',
                'fieldKey' => 'period_on',
                'filters' => ['period_on' => ['value' => [
                    'year' => 2025,
                    'month' => 1
                ]]],
            ],
        ];
    }
}
