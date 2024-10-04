<?php

namespace Tests\Feature\Dictionary;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTypesDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testTransactionTypesDictionaryList(): void
    {
        $response = $this->postJson(route('api.v1.dictionary.transactions.types'));

        $response->assertOk();
        $response->assertExactJson(Transaction::TYPES);
    }
}
