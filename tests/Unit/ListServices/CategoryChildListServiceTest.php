<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Category;
use App\Services\Category\CategoryChildListService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CategoryChildListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('categoryChildFiltersDataProvider')]
    public function testChildCategoryListServiceHandleFilters(int $count, array $sequence, string $filterKey, string $fieldKey, array $filters): void
    {
        $factory = Category::factory()->withParentCategory()->count($count);
        if (count($sequence) > 0) {
            $factory = $factory->sequence(...$sequence);
        }
        $factory->create();

        $service = CategoryChildListService::create();
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

        $this->assertCount($count * 2, Category::all());
        $this->assertCount(1, $data);

        $expectedValue = match($filterKey) {
            'parent_categories' => $filters[$filterKey]['value'][0]['id'],
            default => $filters[$filterKey]['value'],
        };

        $this->assertEquals($expectedValue, $data[0][$fieldKey]);
    }

    #[DataProvider('categoryChildSortingDataProvider')]
    public function testChildCategoryListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        Category::factory()
            ->count($count)
            ->withParentCategory()
            ->sequence(...$sequence)
            ->create()
        ;

        $service = CategoryChildListService::create();
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

        $this->assertCount($count * 2, Category::all());
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

    public static function categoryChildFiltersDataProvider(): array
    {
        return [
            'filter_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 3,
                    ],
                    [
                        'id' => 4,
                    ]
                ],
                'filterKey' => 'id',
                'fieldKey' => 'id',
                'filters' => ['id' => ['value' => 3]],
            ],
            'filter_2' => [
                'count' => 2,
                'sequence' => [
                    [
                        'name' => 'name 1',
                    ],
                    [
                        'name' => 'name 2',
                    ]
                ],
                'filterKey' => 'name',
                'fieldKey' => 'name',
                'filters' => ['name' => ['value' => 'name 2']],
            ],
            'filter_3' => [
                'count' => 2,
                'sequence' => [],
                'filterKey' => 'parent_categories',
                'fieldKey' => 'parent_id',
                'filters' => ['parent_categories' => ['value' => [['id' => 2]]]],
            ],
        ];
    }

    public static function categoryChildSortingDataProvider(): array
    {
        return [
            'sorting_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 3,
                    ],
                    [
                        'id' => 4,
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
                        'name' => 'a name',
                    ],
                    [
                        'name' => 'b name',
                    ]
                ],
                'sorting' => [
                    'column' => 'name',
                    'direction' => 'DESC',
                ],
            ],
        ];
    }
}
