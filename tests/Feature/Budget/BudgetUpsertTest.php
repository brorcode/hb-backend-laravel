<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use App\Models\BudgetTemplate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BudgetUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testBudgetShow(): void
    {
        $budgets = Budget::factory(2)
            ->sequence(
                ['amount' => 100, 'period_on' => now()->subMonth()->startOfMonth()],
                ['amount' => 100, 'period_on' => now()->subMonth()->startOfMonth()],
            )
            ->create()
        ;

        /** @var Budget $budget */
        $budget = $budgets->last();
        $response = $this->getJson(route('api.v1.budgets.show', $budget->period_on->format('Ym')));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $budget->period_on->format('Ym'),
                'total' => (100+100) / 100,
                'period_on_for_list' => $budget->period_on->translatedFormat('Y F'),
                'period_on' => $budget->period_on->toDateString(),
            ],
        ]);
    }

    public function testBudgetShowNotFoundModelReturnForbidden(): void
    {
        $response = $this->getJson(route('api.v1.budgets.show', 202501));
        $response->assertForbidden();
    }

    public function testBudgetStore(): void
    {
        $this->assertCount(0, Budget::all());
        $templates = BudgetTemplate::factory(5)->create();

        $response = $this->postJson(route('api.v1.budgets.store'), [
            'period_on' => [
                'month' => 1,
                'year' => 2025,
            ],
        ]);

        $this->assertCount(5, Budget::all());

        /** @var BudgetTemplate $template */
        $template = $templates->random(1)->first();

        $this->assertDatabaseHas((new Budget())->getTable(), [
            'amount' => $template->amount,
            'category_id' => $template->category_id,
            'period_on' => Carbon::createFromDate(2025, 1+1, 1)->toDateString(),
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Шаблон бюджета создан',
        ]);
    }

    public function testBudgetCannotBeCreatedWithoutTemplate(): void
    {
        $this->assertCount(0, Budget::all());

        $response = $this->postJson(route('api.v1.budgets.store'), [
            'period_on' => [
                'month' => 1,
                'year' => 2025,
            ],
        ]);

        $this->assertCount(0, Budget::all());

        $response->assertBadRequest();
        $response->assertExactJson([
            'message' => 'Нужно создать хотя бы один шаблон бюджета.',
        ]);
    }

    #[DataProvider('invalidBudgetDataProvider')]
    public function testBudgetCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.budgets.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testTwoBudgetsCanNotBeCreatedWithOnSameDate(): void
    {
        /** @var Budget $budget */
        $budget = Budget::factory()->create();

        $response = $this->postJson(route('api.v1.budgets.store'), [
            'period_on' => [
                'year' => $budget->period_on->year,
                'month' => $budget->period_on->month - 1,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => [
                'period_on' => ['Бюждет на эту дату уже существует.'],
            ],
        ]);
    }

    public static function invalidBudgetDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'period_on' => [
                        'Поле Дата обязательно.',
                        'Поле Дата обязательно.',
                        'Поле Дата обязательно.',
                    ],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'period_on' => 123,
                ],
                'errors' => [
                    'period_on' => [
                        'Значение поля Дата должно быть массивом.',
                        'Поле Дата обязательно.',
                        'Поле Дата обязательно.',
                    ],
                ],
            ],
            'wrong_data_3' => [
                'request' => [
                    'period_on' => [
                        'test' => 'test',
                    ],
                ],
                'errors' => [
                    'period_on' => [
                        'Поле Дата обязательно.',
                        'Поле Дата обязательно.',
                    ],
                ],
            ],
            'wrong_data_4' => [
                'request' => [
                    'period_on' => [
                        'month' => 'test',
                        'year' => 'test',
                    ],
                ],
                'errors' => [
                    'period_on' => [
                        'Значение поля Дата должно быть целым числом.',
                        'Значение поля Дата должно быть целым числом.',
                        'Количество символов в поле Дата должно быть равным 4.',
                        'Значение поля Дата должно быть от 1975 до 2075.',
                    ],
                ],
            ],
        ];
    }
}
