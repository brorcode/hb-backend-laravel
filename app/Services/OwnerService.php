<?php

namespace App\Services;

use App\Exceptions\SystemException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OwnerService
{
    use ServiceSingleton;

    private User $user;

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @throws SystemException
     */
    public function getUser(): User
    {
        if (!$user = Auth::user()) {
            $user = $this->user;
        }

        if (!$user) {
            throw new SystemException('User doesn\'t set.');
        }

        return $user;
    }
}
