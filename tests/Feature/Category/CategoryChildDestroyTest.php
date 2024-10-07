<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryChildDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function testCategoryDestroy(): void
    {
        $this->userLogin();

        $childCategories = Category::factory(2)->withParentCategory()->create();

        /** @var Category $categoryToBeDeleted */
        $categoryToBeDeleted = $childCategories->last();

        $this->assertCount(4, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => $categoryToBeDeleted->name,
        ]);
        $response = $this->deleteJson(route('api.v1.categories.destroy.child', [
            $categoryToBeDeleted->parentCategory,
            $categoryToBeDeleted,
        ]));

        $this->assertCount(3, Category::all());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => $categoryToBeDeleted->name,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Категория удалена',
        ]);
    }
}
