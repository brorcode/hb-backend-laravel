<?php

namespace App\Services\Category;

use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Services\ServiceInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryPointerService
{
    use ServiceInstance;

    public function getPointers(bool $isParent): Collection
    {
        return CategoryPointer::query()
            ->with([
                'categoryPointerTags' => function(HasMany $query) {
                    $query
                        ->select([
                            'id',
                            'name',
                            'category_pointer_id',
                        ])
                        ->orderBy('name')
                    ;
                }
            ])
            ->select([
                'id',
                'name',
                'is_parent',
            ])
            ->where('is_parent', $isParent)
            ->orderBy('name')
            ->get()
        ;
    }

    public function createPointersTree(array $pointers, bool $isParent): void
    {
        DB::transaction(function () use ($pointers, $isParent) {
            $this->removeOldTree($isParent);
            $this->saveNewTree($pointers, $isParent);
        });
    }

    /**
     * @throws ValidationException
     */
    private function saveNewTree(array $pointers, bool $isParent): void
    {
        foreach ($pointers as $pointer) {
            $categoryPointer = $this->createCategoryPointer($pointer['name'], $isParent);
            $this->createCategoryPointerTags($pointer['tags_array'], $categoryPointer);
        }
    }

    /**
     * @throws ValidationException
     */
    private function createCategoryPointer(string $name, bool $isParent): CategoryPointer
    {
        if (CategoryPointer::findByName($name)) {
            throw ValidationException::withMessages(
                ['category_pointer_id' => "Каждая категория должна иметь уникальное имя. Дубликат: {$name}."],
            );
        }

        $categoryPointer = new CategoryPointer();
        $categoryPointer->name = $name;
        $categoryPointer->is_parent = $isParent;
        $categoryPointer->save();

        return $categoryPointer;
    }

    /**
     * @throws ValidationException
     */
    private function createCategoryPointerTags(array $tags, CategoryPointer $categoryPointer): void
    {
        foreach ($tags as $name) {
            if (CategoryPointerTag::findByName($name, $categoryPointer->is_parent)) {
                throw ValidationException::withMessages(
                    ['category_pointer_tag_id' => "Каждый тег должен иметь уникальное имя. Дубликат: {$name}."],
                );
            }

            $categoryPointerTag = new CategoryPointerTag();
            $categoryPointerTag->name = $name;
            $categoryPointerTag->categoryPointer()->associate($categoryPointer);
            $categoryPointerTag->save();
        }
    }

    private function removeOldTree(bool $isParent): void
    {
        CategoryPointerTag::query()
            ->whereHas('categoryPointer', function (Builder $query) use ($isParent) {
                $query->where('is_parent', $isParent);
            })
            ->delete()
        ;
        CategoryPointer::query()->where('is_parent', $isParent)->delete();
    }
}
