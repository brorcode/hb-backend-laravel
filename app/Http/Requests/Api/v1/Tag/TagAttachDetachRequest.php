<?php

namespace App\Http\Requests\Api\v1\Tag;

use App\Http\Requests\Api\v1\ApiRequest;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * @property-read int|null $tag_id
 * @property-read array<int, int> $selected_items
 */
class TagAttachDetachRequest extends ApiRequest
{
    public Tag $tag;

    public function rules(): array
    {
        return [
            'selected_items' => ['required', 'array'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function rulesPassed(): void
    {
        try {
            $this->tag = Tag::findOrFail($this->tag_id);
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'tag_id' => 'Тег не найден',
            ]);
        }
    }
}
