<?php

namespace Tests\Feature\Loan;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanDestroyTest extends TestCase
{
    use RefreshDatabase;

    public function testLoanDestroy(): void
    {
        $this->userLogin();

        $loans = Loan::factory(2)->create();
        $loanToBeDeleted = $loans->last();

        $this->assertCount(2, Loan::all());
        $this->assertDatabaseHas((new Loan)->getTable(), [
            'name' => $loanToBeDeleted->name,
        ]);
        $response = $this->deleteJson(route('api.v1.loans.destroy', $loanToBeDeleted));

        $this->assertCount(1, Loan::all());
        $this->assertDatabaseMissing((new Loan)->getTable(), [
            'name' => $loanToBeDeleted->name,
        ]);

        $response->assertOk();
        $response->assertExactJson([
            'message' => 'Долг удален',
        ]);
    }
}
