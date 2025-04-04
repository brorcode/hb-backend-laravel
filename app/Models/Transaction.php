<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\TransactionObserver;
use Carbon\Carbon;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 * @property int $category_id
 * @property int $amount
 * @property int $account_id
 * @property int|null $loan_id
 * @property bool $is_debit
 * @property bool $is_transfer
 * @property bool $is_auto_import
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Category $category
 * @property-read Account $account
 * @property-read Loan|null $loan
 * @property-read Collection|null $tags
 * @property-read Collection $users
 *
 * @method static TransactionFactory factory($count = null, $state = [])
 *
 * @see TransactionObserver
 */
class Transaction extends Model
{
    use HasFactory;

    const TYPE_ID_DEBIT = 1;
    const TYPE_ID_CREDIT = 2;
    const TYPE_ID_TRANSFER = 3;

    const TYPES = [
        self::TYPE_ID_DEBIT => 'Доход',
        self::TYPE_ID_CREDIT => 'Расход',
        self::TYPE_ID_TRANSFER => 'Перевод',
    ];

    protected $casts = [
        'is_debit' => 'bool',
        'is_transfer' => 'bool',
        'is_auto_import' => 'bool',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
