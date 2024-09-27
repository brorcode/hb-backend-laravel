<?php

namespace App\Jobs;

use App\Services\DemoDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;

class UpdateDemoDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function tags(): array
    {
        return [(new ReflectionClass(static::class))->getShortName()];
    }

    public function handle(): void
    {
        $service = DemoDataService::create();
        $service->update();
    }
}
