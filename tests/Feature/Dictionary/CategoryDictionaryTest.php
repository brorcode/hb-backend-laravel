<?php

namespace Tests\Feature\Dictionary;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryDictionaryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCategoryDictionaryList(): void
    {
        $categories = Category::factory(11)->withParentCategory()->create();
        $response = $this->postJson(route('api.v1.dictionary.categories'));

        $data = $categories->take(10)->map(function (Category $category) {
            return [
                'id' => $category->getKey(),
                'name' => $category->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testCategoryDictionaryListWithSearch(): void
    {
        $categories = Category::factory(2)->withParentCategory()
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.categories'), [
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
