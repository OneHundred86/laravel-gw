<?php

namespace Oh86\GW\ProxyRequest;

use Psr\Http\Message\ResponseInterface;

interface ProxyRequestInterface
{
    public function request(string $method, string $url, array $datas, array $options = []): ResponseInterface;
}
