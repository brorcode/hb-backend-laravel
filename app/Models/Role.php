<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as BaseRole;

/**
 * @property string $name
 * @property string $guard_name
 *
 * @method static RoleFactory factory($count = null, $state = [])
 */
class Role extends BaseRole
{
    use HasFactory;

    const NAME_SUPER_USER = 'Super User';
    const NAME_USER = 'User';
    const NAME_DEMO_USER = 'Demo User';

    const NAMES = [
        self::NAME_SUPER_USER,
        self::NAME_USER,
        self::NAME_DEMO_USER,
    ];
}
