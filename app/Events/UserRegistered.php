<?php

namespace App\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use SerializesModels;

    public Authenticatable $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }
}
