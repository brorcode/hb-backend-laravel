<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\BudgetTemplateUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\Budget\BudgetTemplateResource;
use App\Models\BudgetTemplate;
use App\Services\Budget\BudgetTemplateListService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BudgetTemplateController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = BudgetTemplateListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $budgetTemplate = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(BudgetTemplateResource::collection($budgetTemplate));
    }

    public function store(BudgetTemplateUpsertRequest $request): JsonResponse
    {
        $budgetTemplate = new BudgetTemplate();
        $budgetTemplate->amount = $request->amount * 100;
        $budgetTemplate->category_id = $request->category_id;
        $budgetTemplate->save();

        return response()->json(['message' => 'Шаблон бюджета создан'], Response::HTTP_CREATED);
    }

    public function show(BudgetTemplate $budgetTemplate): JsonResponse
    {
        return $this->response(BudgetTemplateResource::make($budgetTemplate));
    }

    public function update(BudgetTemplateUpsertRequest $request, BudgetTemplate $budgetTemplate): JsonResponse
    {
        $budgetTemplate->amount = $request->amount * 100;
        $budgetTemplate->category_id = $request->category_id;
        $budgetTemplate->save();

        return $this->response(BudgetTemplateResource::make($budgetTemplate), 'Шаблон бюджета обновлен');
    }

    public function destroy(BudgetTemplate $budgetTemplate): JsonResponse
    {
        $budgetTemplate->delete();

        return response()->json(['message' => 'Шаблон бюджета удален']);
    }
}
