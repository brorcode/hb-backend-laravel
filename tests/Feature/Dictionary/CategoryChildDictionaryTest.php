<?php

namespace Tests\Feature\Dictionary;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryChildDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryChildDictionaryList(): void
    {
        $categories = Category::factory(11)->withParentCategory()->create();
        $response = $this->postJson(route('api.v1.dictionary.categories.child'));

        $data = $categories->take(10)->map(function (Category $category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testCategoryChildDictionaryListWithSearch(): void
    {
        $categories = Category::factory(2)->withParentCategory()
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.categories.child'), [
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
