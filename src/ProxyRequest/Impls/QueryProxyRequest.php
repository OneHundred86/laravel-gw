<?php

namespace Oh86\GW\ProxyRequest\Impls;

use Oh86\GW\ProxyRequest\ProxyRequestInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * json代理请求
 */
class QueryProxyRequest implements ProxyRequestInterface
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $datas, array $options = []): ResponseInterface
    {
        if ($datas) {
            $options["query"] = $datas;
        }

        return $this->client->request($method, $url, $options);
    }
}
