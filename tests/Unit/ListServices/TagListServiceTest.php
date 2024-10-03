<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Tag;
use App\Services\Tag\TagListService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TagListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('tagFiltersDataProvider')]
    public function testTagListServiceHandleFilters(int $count, array $sequence, string $filterKey, array $filters): void
    {
        Tag::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = TagListService::create();
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

        $this->assertCount($count, Tag::all());
        $this->assertCount(1, $data);
        $this->assertEquals($filters[$filterKey]['value'], $data[0][$filterKey]);
    }

    #[DataProvider('tagSortingDataProvider')]
    public function testTagListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        Tag::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = TagListService::create();
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

        $this->assertCount($count, Tag::all());
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

    public static function tagFiltersDataProvider(): array
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
                'filters' => ['id' => ['value' => 1]],
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
                'filters' => ['name' => ['value' => 'name 2']],
            ],
        ];
    }

    public static function tagSortingDataProvider(): array
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
