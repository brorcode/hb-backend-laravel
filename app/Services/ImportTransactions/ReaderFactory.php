<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;

class ReaderFactory
{
    private ParserFactory $parserFactory;

    public function __construct(ParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
    }

    public function make(Account $account): Reader
    {
        return new Reader($this->parserFactory->make($account), $account);
    }
}
