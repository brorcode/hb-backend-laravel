<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\AccountObserver;
use Carbon\Carbon;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int id
 * @property string name
 * @property int|null integration_id
 * @property boolean is_archived
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection transactions
 * @property-read Integration|null integration
 * @property-read Collection users
 *
 * @method static self findOrFail(int $id)
 * @method static AccountFactory factory($count = null, $state = [])
 *
 * @see AccountObserver
 */
class Account extends Model
{
    use HasFactory;

    protected $casts = [
        'is_archived' => 'bool',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function integration(): ?BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
