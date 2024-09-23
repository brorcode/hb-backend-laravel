<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionFactory extends Factory
{
    /** @var string */
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(Permission::NAMES),
            'guard_name' => config('auth.defaults.guard'),
        ];
    }

    public function withName(string $name): Factory
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
