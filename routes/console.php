<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:clean --disable-notifications')->daily()->at('01:00');
Schedule::command('backup:run --only-db --disable-notifications')->daily()->at('01:30');
