<?php

namespace App\Models;

use App\Models\Scopes\OwnerScope;
use Carbon\Carbon;
use Database\Factories\BudgetTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $user_id
 * @property int $category_id
 * @property int $amount
 * @property Carbon $period_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Category $category
 *
 * @method static self findOrFail(int $id)
 * @method static BudgetTemplateFactory factory($count = null, $state = [])
 *
 * @see BudgetObserver
 */
class Budget extends Model
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
