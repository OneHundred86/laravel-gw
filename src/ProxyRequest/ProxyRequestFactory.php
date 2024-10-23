<?php

namespace Oh86\GW\ProxyRequest;

use GuzzleHttp\Client;
use Oh86\GW\ProxyRequest\Impls\JsonProxyRequest;
use Oh86\GW\ProxyRequest\Impls\MultipartProxyRequest;
use Oh86\GW\ProxyRequest\Impls\FormParamsProxyRequest;
use Oh86\GW\ProxyRequest\Impls\QueryProxyRequest;

class ProxyRequestFactory
{
    public static function create(?string $contentType, Client $client): ProxyRequestInterface
    {
        $contentType = strtolower($contentType);

        if (self::isJson($contentType)) {
            return new JsonProxyRequest($client);
        } elseif (self::isMultipart($contentType)) {
            return new MultipartProxyRequest($client);
        } elseif (self::isFormParams($contentType)) {
            return new FormParamsProxyRequest($client);
        } else {
            return new QueryProxyRequest($client);
        }
    }

    protected static function isJson(string $contentType): bool
    {
        return strpos($contentType, "application/json") !== false;
    }

    protected static function isFormParams(string $contentType): bool
    {
        return strpos($contentType, "application/x-www-form-urlencoded") !== false;
    }

    protected static function isMultipart(string $contentType): bool
    {
        return strpos($contentType, "multipart/form-data") !== false;
    }
}
