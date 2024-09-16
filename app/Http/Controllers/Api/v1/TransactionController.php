<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Requests\Api\v1\Transaction\TransactionDestroyManyRequest;
use App\Http\Requests\Api\v1\Transaction\TransactionUpsertRequest;
use App\Http\Resources\Api\v1\Transaction\TransactionCollectionResource;
use App\Http\Resources\Api\v1\Transaction\TransactionSingleResource;
use App\Models\Transaction;
use App\Services\Transaction\TransactionListService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = TransactionListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $transactions = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(TransactionCollectionResource::collection($transactions));
    }

    public function store(TransactionUpsertRequest $request): JsonResponse
    {
        $transaction = new Transaction();
        $transaction->amount = $request->amount;
        $transaction->category_id = $request->category_id;
        $transaction->account_id = $request->account_id;
        $transaction->created_at = $request->created_at;
        $transaction->is_debit = $request->is_debit;
        $transaction->is_transfer = $request->is_transfer;
        $transaction->save();

        return response()->json([], Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return $this->response(TransactionSingleResource::make($transaction));
    }

    public function update(TransactionUpsertRequest $request, Transaction $transaction): JsonResponse
    {
        $transaction->amount = $request->amount;
        $transaction->category_id = $request->category_id;
        $transaction->account_id = $request->account_id;
        $transaction->created_at = $request->created_at;
        $transaction->is_debit = $request->is_debit;
        $transaction->is_transfer = $request->is_transfer;
        $transaction->save();

        return $this->response(TransactionSingleResource::make($transaction), 'Транзакция обновлена');
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json(['message' => 'Транзакция удалена']);
    }

    public function destroyMany(TransactionDestroyManyRequest $request): JsonResponse
    {
        Transaction::destroy($request->selected_items);

        return response()->json(['message' => 'Транзакции удалены']);
    }
}
