<?php

namespace Oh86\GW\Config;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Config\Repository;
use Generator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class GatewayConfig
{
    /** @var Repository */
    private static $config;

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @return bool
     */
    public static function loadConfig(): bool
    {
        $cacheConfigFile = self::getCacheConfigFilePath();
        if (file_exists($cacheConfigFile)) {
            self::$config = new Repository(include($cacheConfigFile));
        } else {
            try {
                self::$config = new Repository(self::parseConfig());
            } catch (FileNotFoundException $e) {
                return false;
            }
        }

        return true;
    }

    public static function genCacheConfig()
    {
        $cacheConfigFile = self::getCacheConfigFilePath();
        $cacheConfig = self::parseConfig();

        file_put_contents($cacheConfigFile, "<?php return " . var_export($cacheConfig, true) . ';');
    }

    public static function clearCacheConfig()
    {
        @unlink(self::getCacheConfigFilePath());
    }

    private static function getCacheConfigFilePath(): string
    {
        return base_path("bootstrap/cache/gw.php");
    }

    /**
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws FileNotFoundException
     * @return array
     */
    private static function parseConfig()
    {
        if (!file_exists(base_path('gw.yaml'))) {
            throw new FileNotFoundException(base_path('gw.yaml') . ' does not exists');
        }
        return Yaml::parseFile(base_path('gw.yaml'));
    }


    public static function get(string $key, $default = null)
    {
        return self::$config->get($key, $default);
    }

    public static function getLogChannel(): ?string
    {
        return self::$config->get('log_channel');
    }

    /**
     * @return Generator<RouteConfig>
     */
    public static function getRoutes(): Generator
    {
        foreach (self::$config->get('routes', []) as $appTag => $config) {
            yield new RouteConfig($appTag, $config);
        }
    }

    public static function getRouteConfig(string $appTag): RouteConfig
    {
        $config = self::$config->get('routes.' . $appTag);
        return new RouteConfig($appTag, $config);
    }
}
