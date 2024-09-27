<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateDemoDataJob;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Models\Loan;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UpdateDemoDataJobTest extends TestCase
{
    use DatabaseMigrations;

    private function dispatch(): void
    {
        (new UpdateDemoDataJob())->handle();
    }

    private function getDemoUser(): User
    {
        $email = config('homebudget.demo_user_email');

        $user = User::query()->where('email', $email)->first();
        if (!$user) {
            $user = User::factory()->create(['email' => $email]);
        }

        return $user;
    }

    private function assertNoDbRecordsForLoggedInUser(): void
    {
        $this->assertCount(0, Transaction::all());
        $this->assertCount(0, Category::all());
        $this->assertCount(0, Loan::all());
        $this->assertCount(0, Tag::all());
        $this->assertCount(0, CategoryPointerTag::all());
        $this->assertCount(0, CategoryPointer::all());
        $this->assertCount(0, Account::all());
    }

    private function assertDbCountRecordsForDemoUser(): void
    {
        $this->assertCount(48, Transaction::all());
        $this->assertCount(21, Category::all());
        $this->assertCount(2, Loan::all());
        $this->assertCount(1, Tag::all());
        $this->assertCount(9, CategoryPointerTag::all());
        $this->assertCount(3, CategoryPointer::all());
        $this->assertCount(2, Account::all());
    }

    public function testJobAddsDemoDataForDemoUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertNoDbRecordsForLoggedInUser();
        Auth::logout();

        /**
         * UpdateDemoDataJob uses OwnerService to set up demo user
         */
        $this->dispatch();

        $this->actingAs($user);
        $this->assertNoDbRecordsForLoggedInUser();
        Auth::logout();

        $this->actingAs($this->getDemoUser());
        $this->assertDbCountRecordsForDemoUser();
    }

    public function testJobUpdatesOnlyDemoDataForDemoUser(): void
    {
        /**
         * UpdateDemoDataJob uses OwnerService to set up demo user
         */
        $this->dispatch();

        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertNoDbRecordsForLoggedInUser();

        $account = Account::factory()->create();
        $childCategory = Category::factory()
            ->withParentCategory()
            ->create()
        ;
        Transaction::factory()
            ->withAccount($account)
            ->withCategory($childCategory)
            ->create()
        ;
        Loan::factory()->create();
        Tag::factory()->create();
        $categoryPointer = CategoryPointer::factory()->create();
        CategoryPointerTag::factory()
            ->withCategoryPointer($categoryPointer)
            ->create()
        ;

        Auth::logout();

        $this->actingAs($this->getDemoUser());
        $this->assertDbCountRecordsForDemoUser();
        $this->dispatch();
        $this->assertDbCountRecordsForDemoUser();

        Auth::logout();

        $this->actingAs($user);
        $this->assertCount(1, Transaction::all());
        $this->assertCount(2, Category::all());
        $this->assertCount(1, Loan::all());
        $this->assertCount(1, Tag::all());
        $this->assertCount(1, CategoryPointerTag::all());
        $this->assertCount(1, CategoryPointer::all());
        $this->assertCount(1, Account::all());
    }
}
