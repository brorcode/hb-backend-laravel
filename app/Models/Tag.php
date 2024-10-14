<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\TagObserver;
use Carbon\Carbon;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int id
 * @property string name
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Collection|null transactions
 * @property-read Collection users
 *
 * @method static self findOrFail(int $id)
 * @method static TagFactory factory($count = null, $state = [])
 *
 * @see TagObserver
 */
class Tag extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OwnerScope);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
