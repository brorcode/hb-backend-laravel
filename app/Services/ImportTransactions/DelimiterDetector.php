<?php

namespace App\Services\ImportTransactions;

use App\Services\ServiceSingleton;

class DelimiterDetector
{
    use ServiceSingleton;

    public function getDelimiter(string $line): string
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($line, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }
}
