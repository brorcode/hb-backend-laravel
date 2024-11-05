<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use App\Observers\CategoryPointerTagObserver;
use Database\Factories\CategoryPointerTagFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 * @property string $name
 * @property integer $category_pointer_id
 *
 * @property-read CategoryPointer $categoryPointer
 * @property-read Collection $users
 *
 * @method static CategoryPointerTagFactory factory($count = null, $state = [])
 *
 * @see CategoryPointerTagObserver
 */
class CategoryPointerTag extends Model
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

    public static function findByName(string $name, bool $isParent): ?self
    {
        return self::query()
            ->where('name', $name)
            ->whereHas('categoryPointer', function (Builder $query) use ($isParent) {
                $query->where('is_parent', $isParent);
            })
            ->first()
        ;
    }

    public function categoryPointer(): BelongsTo
    {
        return $this->belongsTo(CategoryPointer::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
