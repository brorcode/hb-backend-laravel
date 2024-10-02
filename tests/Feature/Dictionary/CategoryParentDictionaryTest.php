<?php

namespace Tests\Feature\Dictionary;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryParentDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryParentDictionaryList(): void
    {
        Category::factory(11)->withParentCategory()->create();
        $response = $this->postJson(route('api.v1.dictionary.categories.parent'));

        $categories = Category::query()->whereNull('parent_id')->limit(10)->get();
        $data = $categories->map(function (Category $category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testCategoryParentDictionaryListWithSearch(): void
    {
        $categories = Category::factory(2)
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.categories.parent'), [
            'q' => 'Name 1',
        ]);

        $data = $categories->filter(function (Category $category) {
            return $category->name === 'Name 1';
        })->map(function (Category $category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }
}
