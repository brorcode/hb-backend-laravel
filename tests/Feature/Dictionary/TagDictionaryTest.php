<?php

namespace Tests\Feature\Dictionary;

use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TagDictionaryTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTagDictionaryList(): void
    {
        $tags = Tag::factory(11)->create();
        $response = $this->postJson(route('api.v1.dictionary.tags'));

        $data = $tags->take(10)->map(function (Tag $tag) {
            return [
                'id' => $tag->getKey(),
                'name' => $tag->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testTagDictionaryListWithSearch(): void
    {
        $tags = Tag::factory(2)
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.tags'), [
            'q' => 'Name 1',
        ]);

        $data = $tags->filter(function (Tag $tag) {
            return $tag->name === 'Name 1';
        })->map(function (Tag $tag) {
            return [
                'id' => $tag->getKey(),
                'name' => $tag->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }
}
