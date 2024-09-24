<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Requests\Api\v1\ListRequest;
use App\Http\Requests\Api\v1\Tag\TagAttachDetachRequest;
use App\Http\Requests\Api\v1\Tag\TagUpsertRequest;
use App\Http\Resources\Api\v1\TagResource;
use App\Models\Tag;
use App\Services\Tag\TagListService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TagController extends ApiController
{
    public function index(ListRequest $request): JsonResponse
    {
        $service = TagListService::create();
        $service->setRequest($request);

        $builder = $service->getListBuilder();
        $transactions = $builder->simplePaginate($request->limit);

        return $this->paginatedResponse(TagResource::collection($transactions));
    }

    public function store(TagUpsertRequest $request): JsonResponse
    {
        $transaction = new Tag();
        $transaction->name = $request->name;
        $transaction->save();

        return response()->json(['message' => 'Тег создан'], Response::HTTP_CREATED);
    }

    public function show(Tag $tag): JsonResponse
    {
        return $this->response(TagResource::make($tag));
    }

    public function update(TagUpsertRequest $request, Tag $tag): JsonResponse
    {
        $tag->name = $request->name;
        $tag->save();

        return $this->response(TagResource::make($tag), 'Тег обновлен');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json(['message' => 'Тег удален']);
    }

    public function attach(TagAttachDetachRequest $request): JsonResponse
    {
        $request->tag->transactions()->syncWithoutDetaching($request->selected_items);

        return response()->json(['message' => 'Тег добавлен к выбранным транзакциям']);
    }

    public function detach(TagAttachDetachRequest $request): JsonResponse
    {
        $request->tag->transactions()->detach($request->selected_items);

        return response()->json(['message' => 'Тег откреплен от выбранных транзакций']);
    }
}
