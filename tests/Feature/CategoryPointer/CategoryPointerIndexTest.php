<?php

namespace Tests\Feature\CategoryPointer;

use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPointerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function testCategoryPointerIndex(): void
    {
        $this->userLogin();

        /** @var CategoryPointer $categoryPointerParent */
        $categoryPointerParent = CategoryPointer::factory()
            ->has(CategoryPointerTag::factory())
            ->isParent(true)
            ->create()
        ;

        /** @var CategoryPointer $categoryPointerChild */
        $categoryPointerChild = CategoryPointer::factory()
            ->has(CategoryPointerTag::factory())
            ->isParent(false)
            ->create()
        ;

        $response = $this->getJson(route('api.v1.category-pointers.index'));

        $response->assertOk();
        $response->assertExactJson([
            'parent' => [
                [
                    'name' => $categoryPointerParent->name,
                    'is_parent' => $categoryPointerParent->is_parent,
                    'tags_array' => $categoryPointerParent->categoryPointerTags->pluck('name')->toArray(),
                ],
            ],
            'child' => [
                [
                    'name' => $categoryPointerChild->name,
                    'is_parent' => $categoryPointerChild->is_parent,
                    'tags_array' => $categoryPointerChild->categoryPointerTags->pluck('name')->toArray(),
                ]
            ],
        ]);
    }
}
