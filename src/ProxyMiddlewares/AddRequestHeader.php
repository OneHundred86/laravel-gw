<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class AddRequestHeader extends AbstractMiddleware
{
    public function __invoke(...$args)
    {
        $key = $args[0];
        $val = $args[1];
        // var_dump(__METHOD__, $key, $val);

        return Middleware::mapRequest(function (RequestInterface $request) use ($key, $val) {
            return $request->withHeader($key, $val);
        });
    }
}
