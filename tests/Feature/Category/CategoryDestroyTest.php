<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryDestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function testCategoryDestroy(): void
    {
        $this->userLogin();

        $categories = Category::factory(2)->create();
        $categoryToBeDeleted = $categories->last();

        $this->assertCount(2, Category::all());
        $this->assertDatabaseHas((new Category())->getTable(), [
            'name' => $categoryToBeDeleted->name,
        ]);
        $response = $this->deleteJson(route('api.v1.categories.destroy', $categoryToBeDeleted));

        $this->assertCount(1, Category::all());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => $categoryToBeDeleted->name,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Категория удалена',
        ]);
    }
}
