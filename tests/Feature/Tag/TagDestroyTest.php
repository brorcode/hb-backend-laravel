<?php

namespace Tests\Feature\Tag;

use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TagDestroyTest extends TestCase
{
    use DatabaseMigrations;

    public function testTagDestroy(): void
    {
        $this->userLogin();

        $tags = Tag::factory(2)->create();
        $tagToBeDeleted = $tags->last();

        $this->assertCount(2, Tag::all());
        $this->assertDatabaseHas((new Tag())->getTable(), [
            'name' => $tagToBeDeleted->name,
        ]);
        $response = $this->deleteJson(route('api.v1.tags.destroy', $tagToBeDeleted));

        $this->assertCount(1, Tag::all());
        $this->assertDatabaseMissing((new Tag())->getTable(), [
            'name' => $tagToBeDeleted->name,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Тег удален',
        ]);
    }
}
