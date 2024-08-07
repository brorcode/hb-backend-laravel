<?php

namespace App\Services;

trait ServiceInstance
{
    /**
     * @return static
     */
    public static function create(): static
    {
        return resolve(self::class);
    }
}
