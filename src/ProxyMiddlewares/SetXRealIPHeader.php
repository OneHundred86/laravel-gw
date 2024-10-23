<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * 设置X-Real-IP请求头
 */
class SetXRealIPHeader extends BaseMiddleware
{
    public function __invoke()
    {
        return Middleware::mapRequest(function (RequestInterface $request) {
            return $request->withHeader('X-Real-IP', $this->request->getClientIp());
        });
    }
}
