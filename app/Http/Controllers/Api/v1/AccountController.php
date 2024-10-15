<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Account\AccountTransactionsImportRequest;
use App\Http\Requests\Api\v1\Account\AccountUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\AccountResource;
use App\Jobs\TransactionsImportJob;
use App\Models\Account;
use App\Services\Account\AccountListService;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = AccountListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $accounts = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(AccountResource::collection($accounts));
    }

    public function store(AccountUpsertRequest $request): JsonResponse
    {
        $account = new Account();
        $account->name = $request->name;
        $account->save();

        return response()->json(['message' => 'Аккаунт создан'], Response::HTTP_CREATED);
    }

    public function show(Account $account): JsonResponse
    {
        return $this->response(AccountResource::make($account));
    }

    public function update(AccountUpsertRequest $request, Account $account): JsonResponse
    {
        $account->name = $request->name;
        $account->save();

        return $this->response(AccountResource::make($account), 'Аккаунт обновлен');
    }

    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json(['message' => 'Аккаунт удален']);
    }

    public function import(AccountTransactionsImportRequest $request, ImportService $service): JsonResponse
    {
        $transactionsImport = $service->createTransactionsImport($request);
        TransactionsImportJob::dispatch(Auth::user(), $transactionsImport, $request->account);

        return response()->json(['message' => 'Ожидайдение завершения импорта']);
    }

    public function checkImportStatus(ImportService $service): JsonResponse
    {
        $response = $service->checkTransactionsImportStatus();

        return response()->json($response);
    }
}
