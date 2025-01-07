<?php

use Illuminate\Support\Facades\Route;
use Oh86\GW\Controllers\ServiceDiscoveryController;

Route::get('gw/service/config', [ServiceDiscoveryController::class, 'getServiceConfig']);