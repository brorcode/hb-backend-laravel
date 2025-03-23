<?php

namespace Tests\Feature\BudgetTemplate;

use App\Models\BudgetTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTemplateDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetTemplateDestroy(): void
    {
        $budgetTemplate = BudgetTemplate::factory(2)->create();

        /** @var BudgetTemplate $budgetTemplateToBeDeleted */
        $budgetTemplateToBeDeleted = $budgetTemplate->last();

        $this->assertCount(2, BudgetTemplate::all());
        $this->assertDatabaseHas((new BudgetTemplate())->getTable(), [
            'id' => $budgetTemplateToBeDeleted->getKey(),
        ]);
        $response = $this->deleteJson(route('api.v1.budget-templates.destroy', $budgetTemplateToBeDeleted));

        $this->assertCount(1, BudgetTemplate::all());
        $this->assertDatabaseMissing((new BudgetTemplate())->getTable(), [
            'id' => $budgetTemplateToBeDeleted->getKey(),
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Шаблон бюджета удален',
        ]);
    }
}
