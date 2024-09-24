<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\CategoryPointerObserver;
use Database\Factories\CategoryPointerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int id
 * @property string name
 * @property bool is_parent
 *
 * @property-read Collection categoryPointerTags
 * @property-read Collection users
 *
 * @method static CategoryPointerFactory factory($count = null, $state = [])
 *
 * @see CategoryPointerObserver
 */
class CategoryPointer extends Model
{
    use HasFactory;

    protected $casts = [
        'is_parent' => 'bool',
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

    public function categoryPointerTags(): HasMany
    {
        return $this->hasMany(CategoryPointerTag::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
