<?php

namespace Tests\Feature\Dictionary;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTypesDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testLoanTypesDictionaryList(): void
    {
        $response = $this->postJson(route('api.v1.dictionary.loans.types'));

        $response->assertOk();
        $response->assertExactJson(Loan::TYPES);
    }
}
