<?php

namespace Tests\Unit\Services;

use App\Exceptions\SystemException;
use App\Http\Requests\Api\v1\DashboardRequest;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\OwnerService;
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
        ]);
        $service = DashboardService::create();

        $response1 = $service->getTransactionsByType($request, true);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
        ], $response1->toArray());

        $response2 = $service->getTransactionsByType($request, false);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
        ], $response2->toArray());

        $response3 = $service->getTotalByMonth($request);
        $this->assertEquals([
            'data' => [],
            'chart' => [
                'labels' => [],
                'data' => [],
            ],
        ], $response3->toArray());
    }
}
