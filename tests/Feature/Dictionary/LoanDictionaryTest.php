<?php

namespace Tests\Feature\Dictionary;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testLoanDictionaryList(): void
    {
        $loans = Loan::factory(11)->create();
        $response = $this->postJson(route('api.v1.dictionary.loans'));

        $data = $loans->take(10)->map(function (Loan $loan) {
            return [
                'id' => $loan->getKey(),
                'name' => $loan->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }

    public function testLoanDictionaryListWithSearch(): void
    {
        $loans = Loan::factory(2)
            ->sequence(
                ['name' => 'Name 1'],
                ['name' => 'Name 2'],
            )
            ->create()
        ;
        $response = $this->postJson(route('api.v1.dictionary.loans'), [
            'q' => 'Name 1',
        ]);

        $data = $loans->filter(function (Loan $loan) {
            return $loan->name === 'Name 1';
        })->map(function (Loan $loan) {
            return [
                'id' => $loan->getKey(),
                'name' => $loan->name,
            ];
        });

        $response->assertOk();
        $response->assertExactJson($data->toArray());
    }
}
