<?php

namespace Oh86\GW\ProxyMiddlewares;

use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

class AllowRequestHeaders extends AbstractMiddleware
{
    /**
     * @param array $headers
     */
    public function __invoke(...$headers)
    {
        return Middleware::mapRequest(function (RequestInterface $request) use ($headers) {
            /** @var string $header */
            foreach ($headers as $header) {
                if (($val = $this->request->header($header))) {
                    $request = $request->withHeader($header, $val);
                }
            }
            return $request;
        });
    }
}
