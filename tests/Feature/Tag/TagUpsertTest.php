<?php

namespace Tests\Feature\Tag;

use App\Models\Scopes\OwnerScope;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TagUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTagShow(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $response = $this->getJson(route('api.v1.tags.show', $tag));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $tag->getKey(),
                'name' => $tag->name,
                'amount' => $tag->transactions->sum('amount'),
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
            ],
        ]);
    }

    public function testTagStore(): void
    {
        $this->assertCount(0, Tag::all());

        $response = $this->postJson(route('api.v1.tags.store'), [
            'name' => 'test',
        ]);

        $this->assertCount(1, Tag::all());
        $this->assertDatabaseHas((new Tag())->getTable(), [
            'name' => 'test',
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Тег создан',
        ]);
    }

    #[DataProvider('invalidTagDataProvider')]
    public function testTagCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        Tag::factory()->create(['name' => 'existing tag name']);

        $response = $this->postJson(route('api.v1.tags.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testTagUpdate(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create(['name' => 'test tag name']);
        $this->assertCount(1, Tag::all());
        $this->assertDatabaseMissing((new Tag())->getTable(), [
            'name' => 'new tag name',
        ]);

        $response = $this->putJson(route('api.v1.tags.update', $tag), [
            'name' => 'new tag name',
        ]);

        $this->assertCount(1, Tag::all());
        $this->assertDatabaseHas((new Tag())->getTable(), [
            'name' => 'new tag name',
        ]);

        $response->assertOk();

        $freshTag = $tag->fresh();
        $response->assertExactJson([
            'message' => 'Тег обновлен',
            'data' => [
                'id' => $freshTag->getKey(),
                'name' => 'new tag name',
                'amount' => $tag->transactions->sum('amount'),
                'created_at' => $freshTag->created_at,
                'updated_at' => $freshTag->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidTagDataProvider')]
    public function testUserCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        Tag::factory()->create(['name' => 'existing tag name']);
        $tagForUpdate = Tag::factory()->create(['name' => 'test tag name']);

        $response = $this->putJson(route('api.v1.tags.update', $tagForUpdate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testTagCanBeUpdatedWithOutNameChange(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create(['name' => 'existing tag name']);

        $response = $this->putJson(route('api.v1.tags.update', $tag), [
            'name' => 'existing tag name',
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Тег обновлен',
            'data' => [
                'id' => $tag->getKey(),
                'name' => 'existing tag name',
                'amount' => 0,
                'created_at' => $tag->created_at,
                'updated_at' => $tag->updated_at,
            ],
        ]);
    }

    public function testTagCanBeCreatedIfAnotherUserHasTheSameTagName(): void
    {
        $this->userLogin();
        Tag::factory()->create(['name' => 'tag 1']);

        $this->userLogin();
        $response = $this->postJson(route('api.v1.tags.store'), [
            'name' => 'tag 1',
        ]);

        $this->assertCount(
            2,
            Tag::query()->withoutGlobalScope(OwnerScope::class)->where('name', 'tag 1')->get()
        );

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Тег создан',
        ]);

        // This line added because when down migrations are executed
        // it returns an error because in the 2020_11_23_010507_drop_tag_name_unique.php
        // down tries to return back unique tag name key. And as in the test we check two
        // users can create same tag name we have an issue:
        // Duplicate entry 'tag 1' for key 'tags_name_unique'
        // (Connection: mariadb, SQL: alter table `tags` add unique `tags_name_unique`(`name`))
        Tag::query()->delete();
    }

    public static function invalidTagDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'existing tag name',
                ],
                'errors' => [
                    'name' => ['Такое название уже существует.'],
                ],
            ],
        ];
    }
}
