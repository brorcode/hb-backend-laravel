<?php

namespace Tests\Unit\ImportTransactions;

use App\Models\Account;
use App\Models\Category;
use App\Models\Integration;
use App\Models\Transaction;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->userLogin();
    }

    public function testItCorrectlyReassignParentCategory(): void
    {
        /** @var Category $childCategory */
        $childCategory = Category::factory()->withParentCategory()->create(['name' => 'Перевод']);

        Integration::factory()->create(['code_id' => Integration::CODE_ID_TINKOFF_BANK]);

        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->getFileContent()
        );

        $this->assertNull(Category::findByName('Переводы между счетами'));

        $service = ImportService::create();
        $service->handle($file, $account);

        $freshChildCategory = $childCategory->fresh();
        $newCreatedParentCategory = Category::findByName('Переводы между счетами');
        $this->assertNotNull($newCreatedParentCategory);
        $this->assertEquals($freshChildCategory->parent_id, $newCreatedParentCategory->getKey());
    }

    public function testItDoesNotCreateDebitTransferTransactionWhenNoCashAccount(): void
    {
        Integration::factory()->create(['code_id' => Integration::CODE_ID_TINKOFF_BANK]);

        $account = Account::factory([
            'integration_id' => Integration::findTinkoffBank()->getKey(),
        ])->create();
        $file = UploadedFile::fake()->createWithContent(
            'transactions.csv',
            $this->getFileContent()
        );

        $this->assertCount(0, Transaction::all());
        $service = ImportService::create();
        $service->handle($file, $account);
        $this->assertCount(6, Transaction::all());
    }

    private function getFileContent(): string
    {
        $rows = [
            'Дата операции;1;2;Статус;4;5;Сумма платежа;7;8;Категория;10;Описание;12',
            '01.01.2024 00:00:01;1;2;OK;4;5;100,00;6;8;Переводы между счетами;10;Перевод;12',
            '01.01.2024 00:00:02;1;2;OK;4;5;-100,00;6;8;Переводы между счетами;10;Перевод;12',
            '01.01.2024 00:00:03;1;2;OK;4;5;100,00;6;8;Доходы;10;Доход;12',
            '01.01.2024 00:00:04;1;2;OK;4;5;-100,00;6;8;Расходы;10;Расход;12',
            '01.01.2024 00:00:05;1;2;OK;4;5;-100,00;6;8;Наличные;10;Банкомат 1;12',
            '01.01.2024 00:00:06;1;2;OK;4;5;-100,00;6;8;Наличные;10;Банкомат 2;12',
        ];

        return implode("\n", $rows);
    }
}
