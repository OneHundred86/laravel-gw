<?php

return [
    'config_file' => env('GW_CONFIG_FILE') ? base_path(env('GW_CONFIG_FILE', 'gw.php')) : null,
];