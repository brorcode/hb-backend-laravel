<?php

namespace App\Models;

use App\Exceptions\SystemException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int id
 * @property string name
 * @property int code_id
 *
 * @property-read Collection users
 */
class Integration extends Model
{
    use HasFactory;

    const CODE_ID_TINKOFF_BANK = 1;
    const CODE_ID_SBERBANK = 2;
    const CODE_ID_TOCHKA_BANK = 3;
    const CODE_ID_YANDEX_MONEY = 4;
    const CODE_ID_TINKOFF_BANK_BUSINESS = 5;

    const CODES = [
        self::CODE_ID_TINKOFF_BANK,
        self::CODE_ID_SBERBANK,
        self::CODE_ID_TOCHKA_BANK,
        self::CODE_ID_YANDEX_MONEY,
        self::CODE_ID_TINKOFF_BANK_BUSINESS,
    ];

    private static function findOrFailByCodeId(int $codeId): self
    {
        /** @var self $model */
        if (!$model = self::query()->where('code_id', $codeId)->first()) {
            throw new SystemException("Missed Integration with code id: {$codeId}.");
        }

        return $model;
    }

    public static function findTinkoffBank(): self
    {
        return self::findOrFailByCodeId(self::CODE_ID_TINKOFF_BANK);
    }

    public static function findSberBank(): self
    {
        return self::findOrFailByCodeId(self::CODE_ID_SBERBANK);
    }

    public static function findTochkaBank(): self
    {
        return self::findOrFailByCodeId(self::CODE_ID_TOCHKA_BANK);
    }

    public static function findYandexMoney(): self
    {
        return self::findOrFailByCodeId(self::CODE_ID_YANDEX_MONEY);
    }

    public static function findTinkoffBankBusiness(): self
    {
        return self::findOrFailByCodeId(self::CODE_ID_TINKOFF_BANK_BUSINESS);
    }
}
