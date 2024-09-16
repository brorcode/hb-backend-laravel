<?php

namespace App\Services\ImportTransactions;

use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Services\ServiceInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryPointerService
{
    use ServiceInstance;

    private Collection $parentCategoryPointers;
    private Collection $childCategoryPointers;

    public function __construct()
    {
        $this->parentCategoryPointers = $this->getCategoryPointers(true);
        $this->childCategoryPointers = $this->getCategoryPointers(false);
    }

    private function getCategoryPointers(bool $isParent): Collection
    {
        return CategoryPointer::query()
            ->with('categoryPointerTags')
            ->where('is_parent', $isParent)
            ->get()
        ;
    }

    public function getChildCategoryName(string $childName): string
    {
        return $this->getCategoryName($childName, $this->childCategoryPointers) ?: $childName;
    }

    public function getParentCategoryName(string $parentCategoryName, string $childCategoryName): string
    {
        if ($name = $this->getCategoryName($parentCategoryName, $this->parentCategoryPointers)) {
            return $name;
        }

        return $this->getCategoryName($childCategoryName, $this->parentCategoryPointers) ?: $parentCategoryName;
    }

    private function getCategoryName(string $name, Collection $categoryPointers): ?string
    {
        /** @var CategoryPointer $categoryPointer */
        foreach ($categoryPointers as $categoryPointer) {
            /** @var CategoryPointerTag $categoryPointerTag */
            foreach ($categoryPointer->categoryPointerTags as $categoryPointerTag) {
                if (Str::is(Str::lower($categoryPointerTag->name), Str::lower($name))) {
                    return $categoryPointer->name;
                }
            }
        }

        return null;
    }
}
