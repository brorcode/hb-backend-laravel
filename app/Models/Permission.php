<?php

namespace App\Models;

use Database\Factories\PermissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as BasePermission;

/**
 * @property string name
 * @property string guard_name
 *
 * @method static PermissionFactory factory($count = null, $state = [])
 */
class Permission extends BasePermission
{
    use HasFactory;

    const NAME_HORIZON_VIEW = 'horizon view';
    const NAME_USERS_VIEW = 'users view';
    const NAME_USERS_EDIT = 'users edit';
    const NAME_PROFILE_VIEW = 'profile view';
    const NAME_PROFILE_EDIT = 'profile edit';
    const NAME_TRANSACTIONS_VIEW = 'transactions view';
    const NAME_TRANSACTIONS_EDIT = 'transactions edit';
    const NAME_CATEGORIES_VIEW = 'categories view';
    const NAME_CATEGORIES_EDIT = 'categories edit';
    const NAME_ACCOUNTS_VIEW = 'accounts view';
    const NAME_ACCOUNTS_EDIT = 'accounts edit';
    const NAME_TAGS_VIEW = 'tags view';
    const NAME_TAGS_EDIT = 'tags edit';
    const NAME_CATEGORY_POINTERS_VIEW = 'category pointers view';
    const NAME_CATEGORY_POINTERS_EDIT = 'category pointers edit';

    const NAMES = [
        self::NAME_HORIZON_VIEW,
        self::NAME_USERS_VIEW,
        self::NAME_USERS_EDIT,
        self::NAME_TRANSACTIONS_VIEW,
        self::NAME_TRANSACTIONS_EDIT,
        self::NAME_CATEGORIES_VIEW,
        self::NAME_CATEGORIES_EDIT,
        self::NAME_ACCOUNTS_VIEW,
        self::NAME_ACCOUNTS_EDIT,
        self::NAME_TAGS_VIEW,
        self::NAME_TAGS_EDIT,
        self::NAME_CATEGORY_POINTERS_VIEW,
        self::NAME_CATEGORY_POINTERS_EDIT,
        self::NAME_PROFILE_VIEW,
        self::NAME_PROFILE_EDIT,
    ];
}
