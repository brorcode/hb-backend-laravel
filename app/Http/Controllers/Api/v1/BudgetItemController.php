<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\BudgetItemUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\Budget\BudgetItemResource;
use App\Models\Budget;
use App\Services\Budget\BudgetItemListService;
use App\Services\Budget\BudgetService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BudgetItemController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = BudgetItemListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $budgets = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(
            BudgetItemResource::collection($budgets),
            ['sum' => $service->getBudgetSum()],
        );
    }

    public function store(BudgetItemUpsertRequest $request): JsonResponse
    {
        $budgetItem = new Budget();
        $budgetItem->amount = $request->amount * 100;
        $budgetItem->category_id = $request->category_id;
        $budgetItem->period_on = BudgetService::getPeriodOnFromInt($request->period_on)->toDateString();
        $budgetItem->save();

        return response()->json(['message' => 'Элемент бюджета создан'], Response::HTTP_CREATED);
    }

    public function show(Budget $budgetItem): JsonResponse
    {
        return $this->response(BudgetItemResource::make($budgetItem));
    }

    public function update(BudgetItemUpsertRequest $request, Budget $budgetItem): JsonResponse
    {
        $budgetItem->amount = $request->amount * 100;
        $budgetItem->category_id = $request->category_id;
        $budgetItem->save();

        return $this->response(BudgetItemResource::make($budgetItem), 'Элемент бюджета обновлен');
    }

    public function destroy(Budget $budgetItem): JsonResponse
    {
        $budgetItem->delete();

        return response()->json(['message' => 'Элемент бюджета удален']);
    }
}
