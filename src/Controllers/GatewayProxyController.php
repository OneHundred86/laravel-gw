<?php

namespace Oh86\GW\Controllers;

use Oh86\GW\Config\GatewayConfig;
use Oh86\GW\ProxyRequest\ProxyRequestFactory;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GatewayProxyController
{
    public function proxy(Request $request, RateLimiter $rateLimiter, ?string $path = null)
    {
        $beginTime = microtime(true);
        $proxyResponse = null;
        $responseStatusCode = null;

        try {
            // 配置
            $appTag = $request->route()->getName();
            $routeConfig = GatewayConfig::getRouteConfig($appTag);
            $accessLogChannel = $routeConfig->getAccessLogChannel();
            $errorLogChannel = $routeConfig->getErrorLogChannel();
            $proxyPass = str_replace("{path}", $path, $routeConfig->getProxyPass());
            $circuitBreaker = $routeConfig->getCircuitBreaker();

            if ($request->getQueryString()) {
                $proxyPassUrl = $proxyPass . "?" . $request->getQueryString();
            } else {
                $proxyPassUrl = $proxyPass;
            }
            
            // 熔断处理
            if ($circuitBreaker) {
                if (Cache::has("gw_break:$appTag")) {
                    $responseStatusCode = 503;
                    return new Response("gw break", $responseStatusCode);
                }
            }

            // 代理请求
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            foreach ($routeConfig->getProxyMiddlewares() as [$middlewareClass, $middlewareArgs]) {
                $stack->push((new $middlewareClass($request))(...$middlewareArgs));
            }
            $client = new Client([
                'handler' => $stack,
            ]);

            $proxyRequest = ProxyRequestFactory::create($request->headers->get("Content-Type"), $client);

            // dd($name, $request->all());
            $options = [
                // "debug" => true,
            ];
            if ($routeConfig->getProxyTimeout()) {
                $options["timeout"] = $routeConfig->getProxyTimeout();
            }

            $proxyResponse = $proxyRequest->request($request->method(), $proxyPassUrl, $request->all(), $options);
            $responseStatusCode = $proxyResponse->getStatusCode();
            return new Response($proxyResponse->getBody()->getContents(), $proxyResponse->getStatusCode(), $proxyResponse->getHeaders());
        } catch (RequestException $e) {
            $proxyResponse = $e->getResponse();
            if ($proxyResponse) {
                $responseStatusCode = $proxyResponse->getStatusCode();
                return new Response($proxyResponse->getBody()->getContents(), $proxyResponse->getStatusCode(), $proxyResponse->getHeaders());
            } else {
                Log::channel($errorLogChannel)->error(__METHOD__, [
                    "url" => $proxyPassUrl,
                    "error" => $e->getMessage(),
                ]);

                // 熔断记录
                if ($circuitBreaker) {
                    $rateLimiter->hit($appTag, $circuitBreaker['error_period']);
                    if ($rateLimiter->tooManyAttempts($appTag, $circuitBreaker['error_threshold'])) {
                        Cache::put("gw_break:$appTag", 1, $circuitBreaker['break_period']);
                    }
                }

                $responseStatusCode = 502;
                return new Response("gw error", $responseStatusCode);
            }
        } finally {
            Log::channel($accessLogChannel)->info(__METHOD__, [
                "costTime" => sprintf("%.3fs", microtime(true) - $beginTime),
                "appTag" => $appTag,
                "method" => $request->method(),
                "url" => $request->fullUrl(),
                "proxyPassUrl" => $proxyPassUrl,
                "datas" => $request->all(),
                "proxyStatusCode" => $proxyResponse ? $proxyResponse->getStatusCode() : null,
                "statusCode" => $responseStatusCode,
            ]);
        }
    }
}
