<?php

namespace Oh86\GW\ProxyRequest\Impls;

use Oh86\GW\ProxyRequest\ProxyRequestInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * application/x-www-form-urlencoded代理请求
 */
class FormParamsProxyRequest implements ProxyRequestInterface
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $datas, array $options = []): ResponseInterface
    {
        $options["form_params"] = $datas;
        return $this->client->request($method, $url, $options);
    }
}
