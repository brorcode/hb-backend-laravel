<?php

namespace Tests\Unit\Services;

use App\Http\Requests\Api\v1\DashboardRequest;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsEmptyResponseIfNoTransactionsInDatabase(): void
    {
        $this->userLogin();
        $request = new DashboardRequest();
        $request->merge([
            'months' => 1,
            'category_count' => 20,
        ]);
        $service = DashboardService::create();

        $response1 = $service->getTransactionsByType($request, true);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
            'total' => 0,
        ], $response1->toArray());

        $response2 = $service->getTransactionsByType($request, false);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
            'total' => 0,
        ], $response2->toArray());

        $response3 = $service->getTotalByMonths($request);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
            'total' => 0,
        ], $response3->toArray());

        $response4 = $service->totalByCategories($request, true);
        $this->assertEquals([], $response4->toArray());

        $response5 = $service->totalByCategories($request, false);
        $this->assertEquals([], $response5->toArray());
    }
}
