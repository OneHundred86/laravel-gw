<?php

return [
    'config_file' => env('GW_CONFIG_FILE') ? base_path(env('GW_CONFIG_FILE', 'gw.php')) : null,

    'private_request' => [
        'app' => env('GW_PRIVATE_APP'),
        'ticket' => env('GW_PRIVATE_TICKET'),
    ],
];