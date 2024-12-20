<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class PrivateRequest extends BaseMiddleware
{
    public function __invoke(string $app, string $ticket)
    {
        return Middleware::mapRequest(function (RequestInterface $request) use ($app, $ticket) {
            $time = time();
            $token = sm3($app . $time . $ticket);
            return $request->withHeader("Private-App", $app)
                ->withHeader("Private-Time", $time)
                ->withHeader("Private-Token", $token);
        });
    }
}
