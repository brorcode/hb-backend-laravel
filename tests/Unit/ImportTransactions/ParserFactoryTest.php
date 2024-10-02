<?php

namespace Tests\Unit\ImportTransactions;

use App\Exceptions\SystemException;
use App\Models\Account;
use App\Models\Integration;
use App\Services\ImportTransactions\ParserFactory;
use App\Services\ImportTransactions\ParseTinkoff;
use App\Services\ImportTransactions\ParseTinkoffBusiness;
use App\Services\ImportTransactions\ParseTochka;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParserFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();

        Integration::factory()
            ->count(5)
            ->sequence(
                ['code_id' => Integration::CODE_ID_TINKOFF_BANK],
                ['code_id' => Integration::CODE_ID_SBERBANK],
                ['code_id' => Integration::CODE_ID_TOCHKA_BANK],
                ['code_id' => Integration::CODE_ID_YANDEX_MONEY],
                ['code_id' => Integration::CODE_ID_TINKOFF_BANK_BUSINESS],
            )
            ->create()
        ;
    }

    public function testItReturnsCorrectParserForTinkoffBankIntegration(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();

        $parser = (new ParserFactory())->make($account);
        $this->assertInstanceOf(ParseTinkoff::class, $parser);
    }

    public function testItReturnsCorrectParserForTochkaBankIntegration(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTochkaBank()->getKey(),
        ])->create();

        $parser = (new ParserFactory())->make($account);
        $this->assertInstanceOf(ParseTochka::class, $parser);
    }

    public function testItReturnsCorrectParserForTinkoffBankBusinessIntegration(): void
    {
        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBankBusiness()->getKey(),
        ])->create();

        $parser = (new ParserFactory())->make($account);
        $this->assertInstanceOf(ParseTinkoffBusiness::class, $parser);
    }

    public function testItThrowsExceptionForSberBankIntegration(): void
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Автоимпорт для Сбербанка отключен, внесите транзакции вручную.');
        $account = Account::factory([
            'integration_id' => Integration::findSberBank()->getKey(),
        ])->create();

        (new ParserFactory())->make($account);
    }

    public function testItThrowsExceptionForYandexMoneyIntegration(): void
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Автоимпорт для Яндекса отключен, внесите транзакции вручную.');
        $account = Account::factory([
            'integration_id' => Integration::findYandexMoney()->getKey(),
        ])->create();

        (new ParserFactory())->make($account);
    }

    public function testItThrowsExceptionForUnknownIntegration(): void
    {
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage('Undefined integration');
        $account = Account::factory()->create(['integration_id' => null]);

        (new ParserFactory())->make($account);
    }
}
