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
     */
    public static function loadConfig(string $configFilePath)
    {
        $cacheConfigFile = self::getCacheConfigFilePath();
        if (file_exists($cacheConfigFile)) {
            self::$config = new Repository(include($cacheConfigFile));
        } else {
            self::$config = new Repository(self::parseConfig($configFilePath));
        }
    }

    public static function genCacheConfig(string $configFilePath)
    {
        $cacheConfigFile = self::getCacheConfigFilePath();
        $cacheConfig = self::parseConfig($configFilePath);

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
    private static function parseConfig($configFilePath)
    {
        if (!file_exists($configFilePath)) {
            throw new FileNotFoundException($configFilePath . ' does not exists');
        }
        return Yaml::parseFile($configFilePath);
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
        foreach (self::$config->get('routes') ?? [] as $appTag => $config) {
            yield new RouteConfig($appTag, $config);
        }
    }

    public static function getRouteConfig(string $appTag): RouteConfig
    {
        $config = self::$config->get('routes.' . $appTag);
        return new RouteConfig($appTag, $config);
    }
}
