<?php

namespace Tests\Feature\Loan;

use App\Models\Loan;
use App\Models\Transaction;
use App\Services\Loan\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoanUpsertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testLoanShow(): void
    {
        /** @var Loan $loan */
        $loan = Loan::factory()
            ->has(Transaction::factory()->count(10))
            ->create()
        ;

        $response = $this->getJson(route('api.v1.loans.show', $loan));

        $response->assertOk();
        $response->assertExactJson([
            'data' => [
                'id' => $loan->getKey(),
                'name' => $loan->name,
                'type' => [
                    'id' => $loan->type_id,
                    'name' => Loan::TYPES[$loan->type_id],
                ],
                'amount' => $loan->amount / 100,
                'amount_left' => LoanService::create()->getAmountLeft($loan),
                'deadline_on' => $loan->deadline_on->toDateString(),
                'created_at' => $loan->created_at,
                'updated_at' => $loan->updated_at,
            ],
        ]);
    }

    public function testLoanStore(): void
    {
        $this->assertCount(0, Loan::all());

        $deadLineOn = now()->addMonth();
        $response = $this->postJson(route('api.v1.loans.store'), [
            'name' => 'test',
            'amount' => 100,
            'type_id' => Loan::TYPE_ID_CREDIT,
            'deadline_on' => $deadLineOn->toDateString(),
        ]);

        $this->assertCount(1, Loan::all());
        $this->assertDatabaseHas((new Loan())->getTable(), [
            'name' => 'test',
            'amount' => 10000,
            'type_id' => Loan::TYPE_ID_CREDIT,
            'deadline_on' => $deadLineOn->toDateString(),
        ]);

        $response->assertCreated();
        $response->assertExactJson([
            'message' => 'Долг создан',
        ]);
    }

    #[DataProvider('invalidLoanDataProvider')]
    public function testLoanCanNotBeStoredWithInvalidData(array $request, array $errors): void
    {
        $response = $this->postJson(route('api.v1.loans.store'), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public function testLoanUpdate(): void
    {
        /** @var Loan $loan */
        $loan = Loan::factory()
            ->has(Transaction::factory()->count(10))
            ->create(['name' => 'test load name'])
        ;

        $dataToUpdate = [
            'name' => 'new loan name',
            'type_id' => Loan::TYPE_ID_CREDIT,
            'amount' => 100,
            'deadline_on' => '2025-01-01',
        ];

        $this->assertCount(1, Loan::all());
        $this->assertDatabaseMissing((new Loan())->getTable(), [
            'name' => $dataToUpdate['name'],
            'type_id' => $dataToUpdate['type_id'],
            'amount' => $dataToUpdate['amount'] * 100,
            'deadline_on' => $dataToUpdate['deadline_on'],
        ]);

        $response = $this->putJson(route('api.v1.loans.update', $loan), $dataToUpdate);

        $this->assertCount(1, Loan::all());
        $this->assertDatabaseHas((new Loan())->getTable(), [
            'name' => $dataToUpdate['name'],
            'type_id' => $dataToUpdate['type_id'],
            'amount' => $dataToUpdate['amount'] * 100,
            'deadline_on' => $dataToUpdate['deadline_on'],
        ]);

        $response->assertOk();

        $freshLoan = $loan->fresh();
        $response->assertExactJson([
            'message' => 'Долг обновлен',
            'data' => [
                'id' => $freshLoan->getKey(),
                'name' => $freshLoan->name,
                'type' => [
                    'id' => $freshLoan->type_id,
                    'name' => Loan::TYPES[$freshLoan->type_id],
                ],
                'amount' => $freshLoan->amount / 100,
                'amount_left' => LoanService::create()->getAmountLeft($freshLoan),
                'deadline_on' => '2025-01-01T00:00:00.000000Z',
                'created_at' => $freshLoan->created_at,
                'updated_at' => $freshLoan->updated_at,
            ],
        ]);
    }

    #[DataProvider('invalidLoanDataProvider')]
    public function testLoanCanNotBeUpdatedWithInvalidData(array $request, array $errors): void
    {
        $loanForUpdate = Loan::factory()->create();

        $response = $this->putJson(route('api.v1.loans.update', $loanForUpdate), $request);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'Заполните форму правильно',
            'errors' => $errors,
        ]);
    }

    public static function invalidLoanDataProvider(): array
    {
        return [
            'wrong_data_1' => [
                'request' => [],
                'errors' => [
                    'name' => ['Поле name обязательно.'],
                    'type_id' => ['Поле type id обязательно.'],
                    'amount' => ['Поле amount обязательно.'],
                    'deadline_on' => ['Поле deadline on обязательно.'],
                ],
            ],
            'wrong_data_2' => [
                'request' => [
                    'name' => 'loan name',
                    'type_id' => 3,
                    'amount' => 100,
                    'deadline_on' => '2025-01-01',
                ],
                'errors' => [
                    'type_id' => ['Значение поля type id отсутствует в списке разрешённых.'],
                ],
            ],
        ];
    }
}
