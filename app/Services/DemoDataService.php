<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Models\Integration;
use App\Models\Loan;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoDataService
{
    use ServiceInstance;

    private function removeOldDemoDataFor(User $user): void
    {
        $user->transactions()->delete();
        $user->categories()->delete();
        $user->loans()->delete();
        $user->tags()->delete();
        $user->categoryPointerTags()->forceDelete();
        $user->categoryPointers()->forceDelete();
        $user->accounts()->delete();
    }

    public function update(): void
    {
        $email = config('homebudget.demo_user_email');
        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            $user = $this->createUser($email);
        }
        OwnerService::make()->setUser($user);
        $this->removeOldDemoDataFor($user);

        $this->checkIntegrations();
        $account = $this->createAccount('Тинькофф карта');
        $this->createAccount('Сбербанк карта');
        $parentCategory = $this->createCategory('Доходы');
        $this->createCategory('Источник 1', $parentCategory);
        $this->createCategory('Источник 2', $parentCategory);
        $this->createCategory('Источник 3', $parentCategory);
        $this->createDebitTransactions($account, $parentCategory);

        $parentCategory = $this->createCategory('Супермаркеты');
        $this->createCategory('Магазин продуктов 1', $parentCategory);
        $this->createCategory('Магазин продуктов 2', $parentCategory);
        $this->createCategory('Магазин продуктов 3', $parentCategory);
        $this->createCreditTransaction($account, $parentCategory);
        $tag = $this->createTag('Название тега');
        $tag->transactions()->syncWithoutDetaching($parentCategory->subTransactions()->get());

        $parentCategory = $this->createCategory('Спорт');
        $this->createCategory('Фитнес', $parentCategory);
        $this->createCategory('Бассейн', $parentCategory);
        $this->createCategory('Спортивный магазин', $parentCategory);
        $this->createCreditTransaction($account, $parentCategory);

        $parentCategory = $this->createCategory('Одежда');
        $this->createCategory('Магазин одежды 1', $parentCategory);
        $this->createCategory('Магазин одежды 2', $parentCategory);
        $this->createCategory('Магазин одежды 3', $parentCategory);
        $this->createCreditTransaction($account, $parentCategory);
        $this->createLoans($account);
        $this->createCategoryPointers();
    }

    private function createTag(string $name): Tag
    {
        $tag = new Tag();
        $tag->name = $name;
        $tag->save();

        return $tag;
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->name = 'Demo User';
        $user->email = $email;
        $user->email_verified_at = Carbon::now();
        $user->password = Hash::make('password');
        $user->save();

        $role = $this->createRole(Role::NAME_DEMO_USER);
        $user->assignRole($role);

        return $user;
    }

    private function createRole(string $name): Role
    {
        $role = new Role();
        $role->name = $name;
        $role->save();

        return $role;
    }

    private function checkIntegrations(): void
    {
        $tb = Integration::query()->where('code_id', Integration::CODE_ID_TINKOFF_BANK)->first();
        if (!$tb) {
            $this->createIntegration(Integration::CODE_ID_TINKOFF_BANK, 'Тинькофф Банк');
        }
        $sber = Integration::query()->where('code_id', Integration::CODE_ID_SBERBANK)->first();
        if (!$sber) {
            $this->createIntegration(Integration::CODE_ID_SBERBANK, 'Сбербанк');
        }
        $tochka = Integration::query()->where('code_id', Integration::CODE_ID_TOCHKA_BANK)->first();
        if (!$tochka) {
            $this->createIntegration(Integration::CODE_ID_TOCHKA_BANK, 'Точка Банк');
        }
        $yandexMoney = Integration::query()->where('code_id', Integration::CODE_ID_YANDEX_MONEY)->first();
        if (!$yandexMoney) {
            $this->createIntegration(Integration::CODE_ID_YANDEX_MONEY, 'Яндекс Деньги');
        }
    }

    private function createIntegration(int $codeId, string $name): void
    {
        $integration = new Integration();
        $integration->code_id = $codeId;
        $integration->name = $name;
        $integration->save();
    }

    private function createCategory(string $name, Category $parentCategory = null): Category
    {
        $category = new Category();
        $category->name = $name;

        if ($parentCategory) {
            $category->parent_id = $parentCategory->getKey();
        }
        $category->save();

        return $category;
    }

    private function createAccount(string $name): Account
    {
        $account = new Account();
        $account->integration_id = Integration::findTinkoffBank()->getKey();
        $account->name = $name;
        $account->save();

        return $account;
    }

    private function createDebitTransactions(Account $account, Category $parentCategory): void
    {
        $parentCategory->subCategories()->get()->each(function (Category $childCategory) use ($account) {
            $amount = mt_rand(7000, 10000);
            $createdAt = now()->subMinute();
            $this->createTransaction($childCategory, $amount, $account, false, true, $createdAt);

            $amount = mt_rand(3000, 7000);
            $createdAt = now()->subMonths(1)->subMinute();
            $this->createTransaction($childCategory, $amount, $account, false, true, $createdAt);

            $amount = mt_rand(1000, 3000);
            $createdAt = now()->subMonths(2)->subMinute();
            $this->createTransaction($childCategory, $amount, $account, false, true, $createdAt);
        });
    }

    private function createCreditTransaction(Account $account, Category $parentCategory): void
    {
        $parentCategory->subCategories()->get()->each(function (Category $childCategory) use ($account) {
            $amount = mt_rand(100, 1000);

            $createdAt = now();
            $this->createTransaction($childCategory, $amount, $account, false, false, $createdAt);

            $createdAt = now()->subMonths(1);
            $this->createTransaction($childCategory, $amount, $account, false, false, $createdAt);

            $createdAt = now()->subMonths(2);
            $this->createTransaction($childCategory, $amount, $account, false, false, $createdAt);
        });
    }

    private function createTransaction(
        Category $category,
        int $amount,
        Account $account,
        bool $isTransfer,
        bool $isDebit,
        Carbon $createdAt,
        ?Loan $loan = null
    ): void
    {
        $transaction = new Transaction();
        $transaction->category_id = $category->getKey();
        $transaction->amount = $amount;
        $transaction->account_id = $account->getKey();
        $transaction->loan_id = $loan?->getKey();
        $transaction->is_transfer = $isTransfer;
        $transaction->is_debit = $isDebit;
        $transaction->is_auto_import = rand(0,1) === 1;
        $transaction->created_at = $createdAt;
        $transaction->save();
    }

    private function createLoans(Account $account): void
    {
        $parentCategory = $this->createCategory('Долги');
        $childCreditCategory = $this->createCategory('Мы дали в долг', $parentCategory);
        $childDebitCategory = $this->createCategory('Нам вернули долг', $parentCategory);
        $this->createDebitLoan($account, $childCreditCategory, $childDebitCategory);

        $childDebitCategory = $this->createCategory('Нам дали в долг / кредит', $parentCategory);
        $childCreditCategory = $this->createCategory('Мы вернули долг / кредит', $parentCategory);
        $this->createCreditLoan($account, $childCreditCategory, $childDebitCategory);
    }

    private function createDebitLoan(Account $account, Category $childCreditCategory, Category $childDebitCategory): void
    {
        $loan = new Loan();
        $loan->name = 'Должник 1';
        $loan->amount = 1500;
        $loan->type_id = Loan::TYPE_ID_CREDIT;
        $loan->deadline_on = now()->addMonth();
        $loan->save();

        $this->createTransaction($childCreditCategory, -1500, $account, true, false, now(), $loan);
        for ($i = 0; $i < 5; $i++) {
            $this->createTransaction($childDebitCategory, 100, $account, true, true, now(), $loan);
        }
    }

    private function createCreditLoan(Account $account, Category $childCreditCategory, Category $childDebitCategory): void
    {
        $loan = new Loan();
        $loan->name = 'Кредит 1';
        $loan->amount = 1000;
        $loan->type_id = Loan::TYPE_ID_DEBIT;
        $loan->deadline_on = now()->addMonth();
        $loan->save();

        $this->createTransaction($childDebitCategory, 1000, $account, true, true, now(), $loan);
        for ($i = 0; $i < 5; $i++) {
            $this->createTransaction($childCreditCategory, -100, $account, false, false, now(), $loan);
        }
    }

    private function createCategoryPointers(): void
    {
        $categoryPointer1 = $this->createCategoryPointer('Одежда одежда', true);
        $this->createCategoryPointerTag('Одежда 1', $categoryPointer1);
        $this->createCategoryPointerTag('Одежда 2', $categoryPointer1);
        $this->createCategoryPointerTag('Магазин одежды 3', $categoryPointer1);
        $this->createCategoryPointerTag('Магазин одежды 4', $categoryPointer1);

        $categoryPointer2 = $this->createCategoryPointer('Спорт', true);
        $this->createCategoryPointerTag('Спорт 1', $categoryPointer2);
        $this->createCategoryPointerTag('Спорт 2', $categoryPointer2);
        $this->createCategoryPointerTag('Спорт 3', $categoryPointer2);
        $this->createCategoryPointerTag('Спорт 4', $categoryPointer2);

        $categoryPointer = $this->createCategoryPointer('Магазин одежды 1', false);
        $this->createCategoryPointerTag('Одежда магазин', $categoryPointer);
    }

    private function createCategoryPointer(string $name, bool $isParent): CategoryPointer
    {
        $categoryPointer = new CategoryPointer();
        $categoryPointer->name = $name;
        $categoryPointer->is_parent = $isParent;
        $categoryPointer->save();

        return $categoryPointer;
    }

    private function createCategoryPointerTag(string $name, CategoryPointer $categoryPointer): void
    {
        $categoryPointerTag = new CategoryPointerTag();
        $categoryPointerTag->name = $name;
        $categoryPointerTag->category_pointer_id = $categoryPointer->getKey();
        $categoryPointerTag->save();
    }
}
