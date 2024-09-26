<?php

namespace Tests\Unit\ListServices;

use App\Http\Requests\Api\v1\ListRequest;
use App\Models\Tag;
use App\Models\Transaction;
use App\Services\Transaction\TransactionListService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionListServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    #[DataProvider('transactionFiltersDataProvider')]
    public function testTransactionListServiceHandleFilters(int $count, array $sequence, string $filterKey, string $fieldKey, array $filters): void
    {
        $factory = Transaction::factory()
            ->count($count)
            ->has(Tag::factory())
        ;
        if (count($sequence) > 0) {
            $factory = $factory->sequence(...$sequence);
        }
        $factory->create();

        $service = TransactionListService::create();
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

        $this->assertCount($count, Transaction::all());
        $this->assertCount(1, $data);


        $expectedValue = match($filterKey) {
            'categories', 'accounts', 'tags' => $filters[$filterKey]['value'][0]['id'],
            'created_at_after' => Carbon::parse($filters[$filterKey]['value'])->addDay()->format('Y-m-d 00:00:00'),
            'created_at_before' => Carbon::parse($filters[$filterKey]['value'])->subDay()->format('Y-m-d 00:00:00'),
            default => $filters[$filterKey]['value'],
        };

        $actualValue = match($filterKey) {
            'tags' => $data[0]->tags[0][$fieldKey],
            'created_at_after', 'created_at_before' => Carbon::parse($data[0][$fieldKey])->format('Y-m-d 00:00:00'),
            default => $data[0][$fieldKey],
        };

        $this->assertEquals($expectedValue, $actualValue);
    }

    #[DataProvider('transactionSortingDataProvider')]
    public function testTransactionListServiceHandleSorting(int $count, array $sequence, array $sorting): void
    {
        Transaction::factory()
            ->count($count)
            ->sequence(...$sequence)
            ->create()
        ;

        $service = TransactionListService::create();
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

        $this->assertCount($count, Transaction::all());
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
                        'amount' => 10,
                        'is_debit' => true,
                    ],
                    [
                        'amount' => 20,
                        'is_debit' => true,
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
            'filter_4' => [
                'count' => 2,
                'sequence' => [],
                'filterKey' => 'accounts',
                'fieldKey' => 'account_id',
                'filters' => ['accounts' => ['value' => [['id' => 2]]]],
            ],
            'filter_5' => [
                'count' => 2,
                'sequence' => [],
                'filterKey' => 'tags',
                'fieldKey' => 'id',
                'filters' => ['tags' => ['value' => [['id' => 2]]]],
            ],
            'filter_6' => [
                'count' => 2,
                'sequence' => [
                    [
                        'created_at' => now()->subDay(),
                    ],
                    [
                        'created_at' => now()->addDay(),
                    ]
                ],
                'filterKey' => 'created_at_after',
                'fieldKey' => 'created_at',
                'filters' => ['created_at_after' => ['value' => now()]],
            ],
            'filter_7' => [
                'count' => 2,
                'sequence' => [
                    [
                        'created_at' => now()->subDay(),
                    ],
                    [
                        'created_at' => now()->addDay(),
                    ]
                ],
                'filterKey' => 'created_at_before',
                'fieldKey' => 'created_at',
                'filters' => ['created_at_before' => ['value' => now()]],
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
                        'is_debit' => true,
                    ],
                    [
                        'amount' => 20,
                        'is_debit' => true,
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
