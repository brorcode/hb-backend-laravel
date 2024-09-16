<?php

namespace Database\Seeders;

use App\Services\DemoDataService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $service = DemoDataService::create();
        $service->update();
    }
}
