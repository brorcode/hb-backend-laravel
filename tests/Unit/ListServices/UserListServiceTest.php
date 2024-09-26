<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\User;
use App\Services\User\UserListService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('userFiltersDataProvider')]
    public function testUserListServiceHandleFilters(int $count, array $sequence, string $filterKey, array $filters): void
    {
        User::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = UserListService::create();
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

        $this->assertCount($count + 1, User::all());
        $this->assertCount(1, $data);
        $this->assertEquals($filters[$filterKey]['value'], $data[0][$filterKey]);
    }

    #[DataProvider('userSortingDataProvider')]
    public function testUserListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        User::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = UserListService::create();
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

        $this->assertCount($count + 1, User::all());
        $this->assertCount($count + 1, $data);

        // Check sorting based on direction
        if ($sorting['direction'] === 'DESC') {
            // For descending order, the last item should be first
            $this->assertEquals($sequence[$count - 1][$sorting['column']], $data[0][$sorting['column']]);
        } elseif ($sorting['direction'] === 'ASC') {
            // For ascending order, the first item should be first
            $this->assertEquals($sequence[0][$sorting['column']], $data[0][$sorting['column']]);
        }
    }

    public static function userFiltersDataProvider(): array
    {
        return [
            'filter_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 2,
                    ],
                    [
                        'id' => 3,
                    ]
                ],
                'filterKey' => 'id',
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
                'filters' => ['name' => ['value' => 'name 2']],
            ],
            'filter_3' => [
                'count' => 2,
                'sequence' => [
                    [
                        'email' => 'email1@example.com',
                    ],
                    [
                        'email' => 'email2@example.com',
                    ]
                ],
                'filterKey' => 'email',
                'filters' => ['email' => ['value' => 'email2@example.com']],
            ],
        ];
    }

    public static function userSortingDataProvider(): array
    {
        return [
            'sorting_1' => [
                'count' => 2,
                'sequence' => [
                    [
                        'id' => 2,
                    ],
                    [
                        'id' => 3,
                    ]
                ],
                'sorting' => [
                    'column' => 'id',
                    'direction' => 'DESC',
                ],
            ],
        ];
    }
}
