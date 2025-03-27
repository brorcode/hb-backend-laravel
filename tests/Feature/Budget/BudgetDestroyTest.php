<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetDestroy(): void
    {
        /** @var Budget $budgetToBeDeleted */
        $budgetToBeDeleted = Budget::factory()->create();

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseHas((new Budget())->getTable(), [
            'id' => $budgetToBeDeleted->getKey(),
        ]);
        $response = $this->deleteJson(route('api.v1.budgets.destroy', $budgetToBeDeleted->period_on->format('Ym')));

        $this->assertCount(0, Budget::all());

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Шаблон бюджета удален',
        ]);
    }
}
