<?php

namespace Tests\Unit\Models;

use App\Exceptions\SystemException;
use App\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testIntegrationModelHasTinkoffBank(): void
    {
        $codeId = Integration::CODE_ID_TINKOFF_BANK;
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("Missed Integration with code id: {$codeId}.");
        Integration::findTinkoffBank();
    }

    public function testIntegrationModelHasSberBank(): void
    {
        $codeId = Integration::CODE_ID_SBERBANK;
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("Missed Integration with code id: {$codeId}.");
        Integration::findSberBank();
    }

    public function testIntegrationModelHasTochkaBank(): void
    {
        $codeId = Integration::CODE_ID_TOCHKA_BANK;
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("Missed Integration with code id: {$codeId}.");
        Integration::findTochkaBank();
    }

    public function testIntegrationModelHasYandexMoney(): void
    {
        $codeId = Integration::CODE_ID_YANDEX_MONEY;
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("Missed Integration with code id: {$codeId}.");
        Integration::findYandexMoney();
    }

    public function testIntegrationModelHasTinkoffBankBusiness(): void
    {
        $codeId = Integration::CODE_ID_TINKOFF_BANK_BUSINESS;
        $this->expectException(SystemException::class);
        $this->expectExceptionMessage("Missed Integration with code id: {$codeId}.");
        Integration::findTinkoffBankBusiness();
    }
}
