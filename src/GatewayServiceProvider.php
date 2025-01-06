<?php

namespace Oh86\GW;

use Oh86\GW\Config\GatewayConfig;
use Oh86\GW\Config\RouteConfig;
use Oh86\GW\Controllers\GatewayProxyController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Oh86\GW\Commands\GatewayCacheConfig;
use Oh86\GW\Commands\GatewayClearCacheConfig;

class GatewayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gw.php', 'gw');

        $this->parseConfigAndRegisterRoutes();
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GatewayCacheConfig::class,
                GatewayClearCacheConfig::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/gw.php' => config_path('gw.php'),
                __DIR__ . '/../config/gw.yaml' => base_path('gw.yaml'),
            ]);
        }
    }

    public function parseConfigAndRegisterRoutes()
    {
        $config = config('gw');
        $configFilePath = $config['config_file'];

        // 没有配置网关配置文件，则认为不使用网关功能
        if ($configFilePath && GatewayConfig::loadConfig($configFilePath)) {
            /** @var RouteConfig $routeConfig */
            foreach (GatewayConfig::getRoutes() as $routeConfig) {
                $r = Route::any($routeConfig->getRoute(), [GatewayProxyController::class, 'proxy'])->where('path', '.*')->name($routeConfig->getAppTag());
                $r->middleware($routeConfig->getMiddlewares());
            }
        }
    }
}
