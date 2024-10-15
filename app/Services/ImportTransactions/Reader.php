<?php

namespace App\Services\ImportTransactions;

use App\Models\Account;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use SplFileObject;

class Reader
{
    private ParserContract $parser;
    private Account $account;

    public function __construct(ParserContract $parser, Account $account)
    {
        $this->parser = $parser;
        $this->account = $account;
    }

    public function parse(string $filePath): Collection
    {
        $reader = new SplFileObject(Storage::path($filePath));
        $reader->setFlags(SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        return $this->readTransactions($reader);
    }

    private function readTransactions(SplFileObject $reader): Collection
    {
        $transactions = new Collection();

        $latestImportedDate = $this->parser->getLatestImportedDate($this->account);
        foreach ($reader as $line) {
            $row = str_getcsv($line, DelimiterDetector::make()->getDelimiter($line));
            if ($transaction = $this->parser->parse($row, $this->account, $latestImportedDate)) {
                $transactions->push($transaction);
            }
        }

        return $transactions;
    }
}
