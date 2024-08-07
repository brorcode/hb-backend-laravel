<?php

namespace App\Services;

trait ServiceSingleton
{
    /**
     * @return static
     */
    public static function make(): static
    {
        return resolve(self::class);
    }
}
