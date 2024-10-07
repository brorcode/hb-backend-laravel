<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Requests\Api\v1\LoanUpsertRequest;
use App\Http\Resources\Api\v1\LoanResource;
use App\Models\Loan;
use App\Services\Loan\LoanListService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoanController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = LoanListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $accounts = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(LoanResource::collection($accounts));
    }

    public function store(LoanUpsertRequest $request): JsonResponse
    {
        $loan = new Loan();
        $loan->name = $request->name;
        $loan->type_id = $request->type_id;
        $loan->amount = $request->amount;
        $loan->deadline_on = Carbon::parse($request->deadline_on);
        $loan->save();

        return response()->json(['message' => 'Долг создан'], Response::HTTP_CREATED);
    }

    public function show(Loan $loan): JsonResponse
    {
        return $this->response(LoanResource::make($loan));
    }

    public function update(LoanUpsertRequest $request, Loan $loan): JsonResponse
    {
        $loan->name = $request->name;
        $loan->type_id = $request->type_id;
        $loan->amount = $request->amount;
        $loan->deadline_on = Carbon::parse($request->deadline_on);
        $loan->save();

        return $this->response(LoanResource::make($loan), 'Долг обновлен');
    }

    public function destroy(Loan $loan): JsonResponse
    {
        $loan->delete();

        return response()->json(['message' => 'Долг удален']);
    }
}
