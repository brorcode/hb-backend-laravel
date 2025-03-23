<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\BudgetTemplate;
use App\Services\Budget\BudgetTemplateListService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetTemplateListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('transactionFiltersDataProvider')]
    public function testBudgetTemplateListServiceHandleFilters(int $count, array $sequence, string $filterKey, string $fieldKey, array $filters): void
    {
        $factory = BudgetTemplate::factory()
            ->count($count)
        ;
        if (count($sequence) > 0) {
            $factory = $factory->sequence(...$sequence);
        }
        $factory->create();

        $service = BudgetTemplateListService::create();
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

        $this->assertCount($count, BudgetTemplate::all());
        $this->assertCount(1, $data);

        $expectedValue = match($filterKey) {
            'amount' =>  $filters[$filterKey]['value'] * 100,
            'categories' => $filters[$filterKey]['value'][0]['id'],
            default => $filters[$filterKey]['value'],
        };

        $actualValue = $data[0][$fieldKey];

        $this->assertEquals($expectedValue, $actualValue);
    }

    #[DataProvider('transactionSortingDataProvider')]
    public function testBudgetTemplateListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        BudgetTemplate::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = BudgetTemplateListService::create();
        $request = new ListRequest();
        $request->merge([
            'page' => 1,
            'limit' => 10,
            'sorting' => $sorting,
        ]);
        $service->setRequest($request);
        $builder = $service->getListBuilder();
        $paginator = $builder->simplePaginate($request->limit);
        $data = $paginator->items();

        $this->assertCount($count, BudgetTemplate::all());
        $this->assertCount($count, $data);

        // Check sorting based on direction
        if ($sorting['direction'] === 'DESC') {
            // For descending order, the last item should be first
            $this->assertEquals($sequence[$count - 1][$sorting['column']], $data[0][$sorting['column']]);
        } elseif ($sorting['direction'] === 'ASC') {
            // For ascending order, the first item should be first
            $this->assertEquals($sequence[0][$sorting['column']], $data[0][$sorting['column']]);
        }
    }

    public static function transactionFiltersDataProvider(): array
    {
        return [
            'filter_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ]
                ],
                'filterKey' => 'id',
                'fieldKey' => 'id',
                'filters' => ['id' => ['value' => 1]],
            ],
            'filter_2' => [
                'count' => 2,
                'sequence' => [
                    [
                        'amount' => 1000,
                    ],
                    [
                        'amount' => 2000,
                    ]
                ],
                'filterKey' => 'amount',
                'fieldKey' => 'amount',
                'filters' => ['amount' => ['value' => 20]],
            ],
            'filter_3' => [
                'count' => 2,
                'sequence' => [],
                'filterKey' => 'categories',
                'fieldKey' => 'category_id',
                'filters' => ['categories' => ['value' => [['id' => 2]]]],
            ],
        ];
    }

    public static function transactionSortingDataProvider(): array
    {
        return [
            'sorting_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 1,
                    ],
                    [
                        'id' => 2,
                    ]
                ],
                'sorting' => [
                    'column' => 'id',
                    'direction' => 'DESC',
                ],
            ],
            'sorting_2' => [
                'count' => 2,
                'sequence' => [
                    [
                        'amount' => 10,
                    ],
                    [
                        'amount' => 20,
                    ]
                ],
                'sorting' => [
                    'column' => 'amount',
                    'direction' => 'DESC',
                ],
            ],
        ];
    }
}
