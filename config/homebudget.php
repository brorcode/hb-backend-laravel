<?php

return [
    'chunk' => 1000,
    'super_user_email' => env('SUPER_USER_EMAIL', ''),
    'demo_user_email' => env('DEMO_USER_EMAIL', ''),
    'queue_long_running_timeout' => env('QUEUE_LONG_RUNNING_TIMEOUT', 600),
];
