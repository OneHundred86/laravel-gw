### 基于laravel实现的网关服务

#### 1.安装
```shell
composer install oh86/laravel-gw
php artisan vendor:publish --provider="Oh86\GW\GatewayServiceProvider"
```

#### 2.配置 gw.yaml
```yaml
log_channel: daily  # 使用laravel的日志配置
routes:
  app1:
    name: 应用1
    access_log_channel:   # 缺省使用log_channel配置
    error_log_channel:    # 缺省使用log_channel配置
    route: /app1/api/{path}     # 必须，{path}为固定变量
    proxy_pass: http://localhost:8000/{path} # 必须，{path}为route配置的变量
    middlewares:  # laravel的中间件
      - Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull
    proxy_middelewares: # 代理中间件
      - Oh86\GW\ProxyMiddlewares\AllowRequestHeaders:cookie,accept
      - Oh86\GW\ProxyMiddlewares\AddRequestHeader:h1,v1
      - Oh86\GW\ProxyMiddlewares\SetXRealIPHeader
      - Oh86\GW\ProxyMiddlewares\SetXForwardedForHeader
      - Oh86\GW\ProxyMiddlewares\PrivateRequest:app1,ticket1
    proxy_timeout: 10
    circuit_breaker:  # {error_period}时间内错误超过{error_threshold}次，触发熔断，熔断时长为{break_period}
      error_period: 60
      error_threshold: 5
      break_period: 300
```