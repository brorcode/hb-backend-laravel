<?php

namespace Tests\Feature\BudgetItem;

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetItemDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetItemDestroy(): void
    {
        $budgetItem = Budget::factory(2)->create();

        /** @var Budget $budgetItemToBeDeleted */
        $budgetItemToBeDeleted = $budgetItem->last();

        $this->assertCount(2, Budget::all());
        $this->assertDatabaseHas((new Budget())->getTable(), [
            'id' => $budgetItemToBeDeleted->getKey(),
        ]);
        $response = $this->deleteJson(route('api.v1.budget-items.destroy', $budgetItemToBeDeleted));

        $this->assertCount(1, Budget::all());
        $this->assertDatabaseMissing((new Budget())->getTable(), [
            'id' => $budgetItemToBeDeleted->getKey(),
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Элемент бюджета удален',
        ]);
    }
}
