<?php

namespace Oh86\GW\Config;

use Oh86\GW\Exceptions\GatewayConfigException;

class RouteConfig
{
    private string $appTag;
    private array $config;

    public function __construct(string $appTag, array $config)
    {
        $this->appTag = $appTag;
        $this->config = $config;
    }

    public function getAppTag(): string
    {
        return $this->appTag;
    }

    public function getName(): ?string
    {
        return $this->config['name'] ?? null;
    }

    public function getAccessLogChannel(): ?string
    {
        return $this->config['access_log_channel'] ?? GatewayConfig::getLogChannel();
    }

    public function getErrorLogChannel(): ?string
    {
        return $this->config['error_log_channel'] ?? GatewayConfig::getLogChannel();
    }

    public function getRoute(): string
    {
        return $this->config['route'];
    }

    /**
     * @return array<string>
     */
    public function getMiddlewares(): array
    {
        return $this->config['middlewares'] ?? [];
    }

    public function getProxyPass(): string
    {
        return $this->config['proxy_pass'];
    }

    /**
     * @return array{string :: 类名, array :: 参数数组}
     * @throws GatewayConfigException
     */
    public function getProxyMiddlewares(): array
    {
        $middlewares = [];
        foreach ($this->config['proxy_middelewares'] ?? [] as $middleware) {
            $arr = explode(':', $middleware);
            $len = count($arr);
            if ($len == 1) {
                $middlewareClass = $arr[0];
                $middlewareArgs = [];
            } elseif ($len == 2) {
                $middlewareClass = $arr[0];
                $middlewareArgs = explode(',', $arr[1]);
            } else {
                throw new GatewayConfigException('proxy_middlewares config error');
            }

            $middlewares[] = [$middlewareClass, $middlewareArgs];
        }

        return $middlewares;
    }

    public function getProxyTimeout(): ?int
    {
        return $this->config['proxy_timeout'] ?? null;
    }

    /**
     * @return array{error_period: int, error_threshold: int, break_period: int}
     */
    public function getCircuitBreaker(): ?array
    {
        return $this->config['circuit_breaker'] ?? null;
    }
}
