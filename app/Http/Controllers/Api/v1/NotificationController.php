<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\Account\AccountTransactionsImportRequest;
use App\Http\Requests\Api\v1\Account\AccountUpsertRequest;
use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Resources\Api\v1\AccountResource;
use App\Http\Resources\Api\v1\NotificationResource;
use App\Jobs\TransactionsImportJob;
use App\Models\Account;
use App\Services\Account\AccountListService;
use App\Services\ImportTransactions\ImportService;
use App\Services\ImportTransactions\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends ApiController
{
    public function index(NotificationService $service): JsonResponse
    {
        $notifications = $service->getMessages();

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'has_new' => $notifications->contains('is_viewed', false),
        ]);
    }

    public function read(NotificationService $service): JsonResponse
    {
        $service->readMessages();
        $notifications = $service->getMessages();

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'has_new' => $notifications->contains('is_viewed', false),
        ]);
    }
}
