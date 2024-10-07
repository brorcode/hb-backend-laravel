<?php

namespace Tests\Unit\Services;

use App\Exceptions\SystemException;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Loan\LoanService;
use App\Services\OwnerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testCanGetDebitLoanAmountLeft(): void
    {
        $loan = Loan::factory()
            ->state([
                'type_id' => Loan::TYPE_ID_DEBIT,
                'amount' => 1000,
            ])
            ->has(
                Transaction::factory()->count(2)->sequence(
                    ['is_debit' => true, 'amount' => 1000, 'is_transfer' => false],
                    ['is_debit' => false, 'amount' => 700, 'is_transfer' => false],
                )
            )
            ->create()
        ;

        $amountLeft = LoanService::create()->getAmountLeft($loan);
        $this->assertEquals(300, $amountLeft);
    }

    public function testCanGetCreditLoanAmountLeft(): void
    {
        $loan = Loan::factory()
            ->state([
                'type_id' => Loan::TYPE_ID_CREDIT,
                'amount' => 1000,
            ])
            ->has(
                Transaction::factory()->count(2)->sequence(
                    ['is_debit' => false, 'amount' => 1000, 'is_transfer' => false],
                    ['is_debit' => true, 'amount' => 800, 'is_transfer' => false],
                )
            )
            ->create()
        ;

        $amountLeft = LoanService::create()->getAmountLeft($loan);
        $this->assertEquals(200, $amountLeft);
    }

    public function testCanGetLoanAmountLeftWithWrongTypeId(): void
    {
        $loan = Loan::factory()
            ->state([
                'type_id' => 3, // not exist
                'amount' => 1000,
            ])->create()
        ;

        $amountLeft = LoanService::create()->getAmountLeft($loan);
        $this->assertEquals(1000, $amountLeft);
    }
}
