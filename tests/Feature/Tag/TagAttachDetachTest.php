<?php

namespace Tests\Feature\Tag;

use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TagAttachDetachTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTagAttach(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $transactions = Transaction::factory(2)->create();

        $this->assertCount(0, $tag->transactions);
        $response = $this->postJson(route('api.v1.tags.attach', $tag), [
            'selected_items' => $transactions->pluck('id')->toArray(),
        ]);

        $this->assertCount(2, $tag->fresh()->transactions);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Тег добавлен к выбранным транзакциям',
        ]);
    }

    public function testTagDetach(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()
            ->has(Transaction::factory()->count(2))
            ->create()
        ;

        $this->assertCount(2, $tag->transactions);
        $response = $this->postJson(route('api.v1.tags.detach', $tag), [
            'selected_items' => $tag->transactions->pluck('id')->toArray(),
        ]);

        $this->assertCount(0, $tag->fresh()->transactions);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Тег откреплен от выбранных транзакций',
        ]);
    }

    public function testTagCanNotBeAttachedWithoutTransactions(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $response = $this->postJson(route('api.v1.tags.attach', $tag));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'selected_items' => ['Поле selected items обязательно.'],
            ],
        ]);
    }

    public function testTagCanNotBeDetachedWithoutTransactions(): void
    {
        /** @var Tag $tag */
        $tag = Tag::factory()->create();
        $response = $this->postJson(route('api.v1.tags.detach', $tag));

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'selected_items' => ['Поле selected items обязательно.'],
            ],
        ]);
    }

    public function testTagCanNotBeAttachedWithoutTag(): void
    {
        $response = $this->postJson(route('api.v1.tags.attach', 2), [
            'selected_items' => [1,2],
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'tag_id' => ['Тег не найден'],
            ],
        ]);
    }

    public function testTagCanNotBeDetachedWithoutTag(): void
    {
        $response = $this->postJson(route('api.v1.tags.detach', 2), [
            'selected_items' => [1,2],
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'tag_id' => ['Тег не найден'],
            ],
        ]);
    }
}
