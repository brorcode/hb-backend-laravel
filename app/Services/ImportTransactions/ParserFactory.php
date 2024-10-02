<?php

namespace App\Services\ImportTransactions;

use App\Exceptions\LogicException;
use App\Exceptions\SystemException;
use App\Models\Account;
use App\Models\Integration;

class ParserFactory
{
    /**
     * @throws SystemException
     */
    public function make(Account $account): ParserContract
    {
        switch ($account->integration_id) {
            case Integration::findTinkoffBank()->getKey():
                return ParseTinkoff::create();
            case Integration::findTochkaBank()->getKey():
                return ParseTochka::create();
            case Integration::findTinkoffBankBusiness()->getKey():
                return ParseTinkoffBusiness::create();
            case Integration::findSberBank()->getKey():
                throw new SystemException('Автоимпорт для Сбербанка отключен, внесите транзакции вручную.');
            case Integration::findYandexMoney()->getKey():
                throw new SystemException('Автоимпорт для Яндекса отключен, внесите транзакции вручную.');
            default:
                throw new SystemException('Undefined integration');
        }
    }
}
