<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\ApiBadRequest;
use App\Http\Requests\Api\v1\BudgetUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\Budget\BudgetResource;
use App\Services\Budget\BudgetListService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BudgetController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = BudgetListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $budgets = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(BudgetResource::collection($budgets));
    }

    /**
     * @throws ApiBadRequest
     */
    public function store(BudgetUpsertRequest $request, BudgetService $budgetService): JsonResponse
    {
        $budgetService->store($request);

        return response()->json(['message' => 'Шаблон бюджета создан'], Response::HTTP_CREATED);
    }

    public function show(int $date, BudgetService $budgetService): JsonResponse
    {
        $budget = $budgetService->show($date);

        return $this->response(BudgetResource::make($budget));
    }

    public function destroy(int $date, BudgetService $budgetService): JsonResponse
    {
        $budgetService->destroy($date);

        return response()->json(['message' => 'Шаблон бюджета удален']);
    }
}
