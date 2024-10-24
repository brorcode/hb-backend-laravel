<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Account;
use App\Services\Account\AccountListService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('accountFiltersDataProvider')]
    public function testAccountListServiceHandleFilters(int $count, array $sequence, string $filterKey, array $filters): void
    {
        Account::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = AccountListService::create();
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

        $this->assertCount($count, Account::all());
        $this->assertCount(1, $data);
        $this->assertEquals($filters[$filterKey]['value'], $data[0][$filterKey]);
    }

    public function testAccountListServiceByDefaultShowsNotArchivedAccountsOnly(): void
    {
        Account::factory()
            ->count(3)
            ->sequence(
                ['is_archived' => true],
                ['is_archived' => false],
                ['is_archived' => true],
            )
            ->create()
        ;

        $service = AccountListService::create();
        $request = new ListRequest();
        $request->merge([
            'page' => 1,
            'limit' => 10,
        ]);
        $service->setRequest($request);
        $builder = $service->getListBuilder();
        $paginator = $builder->simplePaginate($request->limit);
        $data = $paginator->items();

        $this->assertCount(3, Account::all());
        $this->assertCount(1, $data);
        $this->assertFalse($data[0]['is_archived']);
    }

    public function testAccountListServiceHandleShowArchivedFilter(): void
    {
        Account::factory()
            ->count(3)
            ->sequence(
                ['is_archived' => true],
                ['is_archived' => false],
                ['is_archived' => true],
            )
            ->create()
        ;

        $service = AccountListService::create();
        $request = new ListRequest();
        $request->merge([
            'page' => 1,
            'limit' => 10,
            'filters' => ['show_archived' => true],
        ]);
        $service->setRequest($request);
        $builder = $service->getListBuilder();
        $paginator = $builder->simplePaginate($request->limit);
        $data = $paginator->items();

        $this->assertCount(3, Account::all());
        $this->assertCount(3, $data);
    }

    #[DataProvider('accountSortingDataProvider')]
    public function testAccountListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        Account::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = AccountListService::create();
        $request = new ListRequest();
        $request->merge([
            'page' => 1,
            'limit' => 10,
            'filters' => ['show_archived' => true],
            'sorting' => $sorting,
        ]);
        $service->setRequest($request);
        $builder = $service->getListBuilder();
        $paginator = $builder->simplePaginate($request->limit);
        $data = $paginator->items();

        $this->assertCount($count, Account::all());
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

    public static function accountFiltersDataProvider(): array
    {
        return [
            'filter_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 1,
                        'is_archived' => false,
                    ],
                    [
                        'id' => 2,
                        'is_archived' => false,
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
                        'is_archived' => false,
                    ],
                    [
                        'name' => 'name 2',
                        'is_archived' => false,
                    ]
                ],
                'filterKey' => 'name',
                'filters' => ['name' => ['value' => 'name 2']],
            ],
        ];
    }

    public static function accountSortingDataProvider(): array
    {
        return [
            'sorting_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 1,
                        'is_archived' => false,
                    ],
                    [
                        'id' => 2,
                        'is_archived' => false,
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
                        'is_archived' => false,
                    ],
                    [
                        'name' => 'b name',
                        'is_archived' => false,
                    ]
                ],
                'sorting' => [
                    'column' => 'name',
                    'direction' => 'DESC',
                ],
            ],
            'sorting_3' => [
                'count' => 2,
                'sequence' => [
                    [
                        'is_archived' => false,
                    ],
                    [
                        'is_archived' => true,
                    ]
                ],
                'sorting' => [
                    'column' => 'is_archived',
                    'direction' => 'DESC',
                ],
            ],
        ];
    }
}
