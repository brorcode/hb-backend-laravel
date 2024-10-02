<?php

namespace App\Services\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;

class ReaderFactory
{
    private ParserFactory $parserFactory;

    public function __construct(ParserFactory $parserFactory)
    {
        $this->parserFactory = $parserFactory;
    }

    /**
     * @throws SystemException
     */
    public function make(Account $account): Reader
    {
        return new Reader($this->parserFactory->make($account), $account);
    }
}
