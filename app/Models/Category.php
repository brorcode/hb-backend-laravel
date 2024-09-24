<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\CategoryObserver;
use Carbon\Carbon;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property-read int id
 * @property integer|null parent_id
 * @property string name
 * @property bool is_manual_created
 * @property bool check_return
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection transactions
 * @property-read Collection transactionsDebit
 * @property-read Collection transactionsCredit
 * @property-read Category|null parentCategory
 * @property-read Collection users
 *
 * @method static CategoryFactory factory($count = null, $state = [])
 *
 * @see CategoryObserver
 */
class Category extends Model
{
    use HasFactory;

    protected $casts = [
        'is_manual_created' => 'bool',
        'check_return' => 'bool',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OwnerScope);
    }

    public static function findByName(string $name): ?self
    {
        return self::query()->where('name', $name)->first();
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function parentCategory(): ?BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function subTransactions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Transaction::class,
            self::class,
            'parent_id',
            'category_id',
            'id')
        ;
    }

    public function transactionsDebit(): HasMany
    {
        return $this->transactions()
            ->where('is_debit', 1)
            ->where('is_transfer', 0)
        ;
    }

    public function transactionsCredit(): HasMany
    {
        return $this->transactions()
            ->where('is_debit', 0)
            ->where('is_transfer', 0)
        ;
    }

    public function transactionsTransfer(): HasMany
    {
        return $this->transactions()
            ->where('is_transfer', 1)
            // ->where('amount', '>', 0)
        ;
    }

    public function subTransactionsDebit(): HasManyThrough
    {
        return $this->subTransactions()
            ->where('is_debit', 1)
            ->where('is_transfer', 0)
        ;
    }

    public function subTransactionsCredit(): HasManyThrough
    {
        return $this->subTransactions()
            ->where('is_debit', 0)
            ->where('is_transfer', 0)
        ;
    }

    public function subTransactionsTransfer(): HasManyThrough
    {
        return $this->subTransactions()
            ->where('is_transfer', 1)
            // ->where('amount', '>', 0)
        ;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function isParent(): bool
    {
        if($this->parent_id) {
            return false;
        }

        return true;
    }
}
