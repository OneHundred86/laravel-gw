<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class AddRequestHeader extends BaseMiddleware
{
    public function __invoke(string $key, string $val)
    {
        // var_dump(__METHOD__, $key, $val);

        return Middleware::mapRequest(function (RequestInterface $request) use ($key, $val) {
            return $request->withHeader($key, $val);
        });
    }
}
