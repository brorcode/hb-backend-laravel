<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateTransactionCategoriesJob;
use App\Models\Category;
use App\Models\CategoryPointer;
use App\Models\CategoryPointerTag;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ImportTransactions\ImportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UpdateTransactionCategoriesJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->userLogin();
    }

    private function dispatch(): void
    {
        (new UpdateTransactionCategoriesJob($this->user))->handle();
    }

    public function testThisDoesNotRemoveManuallyCreatedEmptyChildCategories(): void
    {
        $childCategory = Category::factory()
            ->isManualCreated(true)
            ->withParentCategory()
            ->create()
        ;

        $this->assertSame(0, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        $this->assertSame(2, Category::query()->count());
        $this->dispatch();
        $this->assertSame(2, Category::query()->count());
    }

    public function testThisRemoveAutomaticCreatedEmptyChildCategories(): void
    {
        $this->actingAs($this->user);

        $childCategory = Category::factory()
            ->isManualCreated(false)
            ->withParentCategory()
            ->create()
        ;

        $this->assertSame(0, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        $this->assertSame(2, Category::query()->count());
        $this->dispatch();
        $this->assertSame(1, Category::query()->count());
    }

    public function testThisDoesNotRemoveAutomaticCreatedNotEmptyChildCategories(): void
    {
        $childCategory = Category::factory()
            ->isManualCreated(false)
            ->withParentCategory()
            ->has(Transaction::factory())
            ->create()
        ;
        $this->assertSame(1, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        $this->assertSame(2, Category::query()->count());
        $this->dispatch();
        $this->assertSame(2, Category::query()->count());
    }

    public function testThisUpdatesCategoriesBasedOnPointers(): void
    {
        /** @var Category $childCategory */
        $childCategory = Category::factory()
            ->isManualCreated(false)
            ->withParentCategory()
            ->has(Transaction::factory())
            ->create()
        ;

        /** @var CategoryPointer $pointer */
        $pointer = CategoryPointer::factory()
            ->withName('new test category name')
            ->isParent(true)
            ->has(CategoryPointerTag::factory()->withName($childCategory->parentCategory->name))
            ->create()
        ;

        /** @var CategoryPointer $childPointer */
        $childPointer = CategoryPointer::factory()
            ->withName('new test child category name')
            ->isParent(false)
            ->has(CategoryPointerTag::factory()->withName($childCategory->name))
            ->create()
        ;

        CategoryPointerTag::factory()
            ->withName($childCategory->name)
            ->withCategoryPointer($childPointer)
            ->create()
        ;

        $this->assertSame(1, Transaction::query()->count());
        $this->assertSame(1, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        $this->assertSame(2, Category::query()->count());
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => $pointer->name,
        ]);
        $this->assertDatabaseMissing((new Category())->getTable(), [
            'name' => $childPointer->name,
        ]);

        $this->dispatch();

        $this->assertSame(1, Transaction::query()->count());
        $this->assertSame(0, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        $this->assertSame(1, Transaction::query()
            ->whereHas('category', function (Builder $query) use ($childPointer) {
                $query->where('name', $childPointer->name);
            })
            ->whereHas('category.parentCategory', function (Builder $query) use ($pointer) {
                $query->where('name', $pointer->name);
            })
            ->count()
        );

        $this->assertSame(3, Category::query()->count());
    }

    public function testJobRemovesAutomaticCreatedEmptyChildCategoriesForUserWhoRunJob(): void
    {
        $childCategory = Category::factory()
            ->isManualCreated(false)
            ->withParentCategory()
            ->create()
        ;

        $this->assertSame(0, Transaction::query()
            ->where('category_id', $childCategory->getKey())
            ->count()
        );
        // logged in user has own 2 categories
        $this->assertSame(2, Category::query()->count());
        // all users have 2 categories
        $this->assertSame(2, Category::query()->withoutGlobalScope(OwnerScope::class)->count());
        Auth::logout();

        $anotherUser = User::factory()->create();
        $this->actingAs($anotherUser);
        Category::factory()
            ->isManualCreated(false)
            ->withParentCategory()
            ->create()
        ;
        // another logged in user has own 2 categories
        $this->assertSame(2, Category::query()->count());
        // all users have 4 categories
        $this->assertSame(4, Category::query()->withoutGlobalScope(OwnerScope::class)->count());
        Auth::logout();

        $this->actingAs($this->user);
        $this->dispatch();

        // logged in user has own 1 category
        $this->assertSame(1, Category::query()->count());
        Auth::logout();

        $this->actingAs($anotherUser);
        // another logged in user still has own 2 categories
        $this->assertSame(2, Category::query()->count());
        // all users have 3 categories
        $this->assertSame(3, Category::query()->withoutGlobalScope(OwnerScope::class)->count());
    }

    public function testJobChecksAndUpdatesTransferTransactions(): void
    {
        $this->actingAs($this->user);

        $parentCategories = Category::factory()
            ->count(3)
            ->sequence(
                ['name' => ImportService::BETWEEN_ACCOUNTS],
                ['name' => ImportService::CASH],
                ['name' => ImportService::CORRECTING],
            )
            ->create()
        ;

        $parentCategories->each(function (Category $parentCategory) {
            Category::factory()
                ->has(Transaction::factory()->notTransfer())
                ->create(['parent_id' => $parentCategory->getKey()])
            ;
        });

        $this->assertCount(3, Transaction::all());
        $this->assertSame(3, Transaction::query()
            ->where('is_transfer', false)
            ->count()
        );

        $this->dispatch();

        $this->assertCount(3, Transaction::all());
        $this->assertSame(3, Transaction::query()
            ->where('is_transfer', true)
            ->count()
        );
    }

    public function testUpdateTransactionCategoriesJobReturnsCorrectTagNames(): void
    {
        $tagNames = (new UpdateTransactionCategoriesJob($this->user))->tags();
        $this->assertEquals(['UpdateTransactionCategoriesJob'], $tagNames);
    }
}
