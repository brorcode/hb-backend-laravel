<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read int id
 * @property string name
 * @property string email
 * @property string password
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @property-read Collection|null accounts
 * @property-read Collection|null categories
 * @property-read Collection|null categoryPointers
 * @property-read Collection|null categoryPointerTags
 * @property-read Collection|null tags
 * @property-read Collection|null transactions
 * @property-read Collection|null loans
 *
 * @method static UserFactory factory($count = null, $state = [])
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function categoryPointers(): BelongsToMany
    {
        return $this->belongsToMany(CategoryPointer::class);
    }

    public function categoryPointerTags(): BelongsToMany
    {
        return $this->belongsToMany(CategoryPointerTag::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class);
    }

    public function loans(): BelongsToMany
    {
        return $this->belongsToMany(Loan::class);
    }

    // @todo check this
    // public static function findSuperUsers(): Collection
    // {
    //     return self::query()->whereHas('roles', function (Builder $query) {
    //         $query->where('name', Role::NAME_SUPER_USER);
    //     })->get();
    // }
    //
    // public static function findDemoUsers(): Collection
    // {
    //     return self::query()->whereHas('roles', function (Builder $query) {
    //         $query->where('name', Role::NAME_DEMO_USER);
    //     })->get();
    // }
}
