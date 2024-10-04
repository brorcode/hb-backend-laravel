<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReleaseCommand extends Command
{
    protected $signature = "db-update:release";
    protected $description = "Run all commands need for release";

    private array $commands = [
        'app:create-permissions-and-roles',
        'app:update-transactions',

        // Examples:
        // ['send-in-blue:set-email-template', [
        //     'code' => SendInBlueEmailTemplate::CODE_P2P_TRANSFER_PROCESSED_TO_RECEIVER,
        //     'id' => 1804,
        // ]],
        // ['permissions:create', [
        //     'name' => 'Delete Countries and States',
        //     'slug' => Permission::SLUG_COUNTRIES_DELETE,
        //     'description' => 'Permission to Delete Countries and States',
        //     '--attach-to-superuser' => 0,
        // ]],
    ];

    public function handle(): void
    {
        $startAt = now();
        $this->comment('db-update:release ['.$startAt->format('Y-m-d H:i:s').'] Started.');
        $this->line('');

        foreach ($this->commands as $index => $command) {
            if (is_array($command)) {
                [$command, $params] = $command;
            } else {
                $params = [];
            }
            $i = $index+1;
            $cmdStartAt = now();
            $this->line("[$i] db-update:release [".now()->format('Y-m-d H:i:s')."] running $command ".implode(' ', $params));
            $this->call($command, $params);
            $cmdEndDiff = now()->diffForHumans($cmdStartAt);
            $this->line("[$i] db-update:release [".now()->format('Y-m-d H:i:s')."] ended $command $cmdEndDiff");
            $this->line('');
        }

        $endDiff = now()->diffForHumans($startAt);
        $this->comment('db-update:release ['.now()->format('Y-m-d H:i:s')."] Ended {$endDiff}.");
    }
}
