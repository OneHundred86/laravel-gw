<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * 设置X-Forwarded-For请求头
 */
class SetXForwardedForHeader extends AbstractMiddleware
{
    public function __invoke(...$args)
    {
        return Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('X-Forwarded-For', $this->request->getClientIps());
        });
    }
}
