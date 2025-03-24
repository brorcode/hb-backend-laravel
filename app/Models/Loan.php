<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\LoanObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property string $name
 * @property int $type_id
 * @property int $amount
 * @property Carbon $deadline_on
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection $users
 * @property-read Collection|null $transactions
 *
 * @see LoanObserver
 */
class Loan extends Model
{
    use HasFactory;

    const TYPE_ID_CREDIT = 1;
    const TYPE_ID_DEBIT = 2;

    const TYPES = [
        self::TYPE_ID_CREDIT => 'Нам должны',
        self::TYPE_ID_DEBIT => 'Мы должны',
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
