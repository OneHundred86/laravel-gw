<?php

namespace Oh86\GW\ProxyRequest\Impls;

use Oh86\GW\ProxyRequest\ProxyRequestInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Http\UploadedFile;

/**
 * multipart/form-data 表单代理请求
 */
class MultipartProxyRequest implements ProxyRequestInterface
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $datas, array $options = []): ResponseInterface
    {
        $body = [];
        foreach ($datas as $key => $val) {
            $data = [];
            if ($val instanceof UploadedFile) {
                $data["name"] = $key;
                $data["contents"] = fopen($val->path(), "r");
                $data["filename"] = $val->getClientOriginalName();
                $body[] = $data;
            } elseif (is_array($val)) {
                foreach ($val as $k => $v) {
                    $data["name"] = sprintf("%s[%s]", $key, $k);
                    $data["contents"] = $v;
                    $body[] = $data;
                }
            } else {
                $data["name"] = $key;
                $data["contents"] = $val;
                $body[] = $data;
            }
        }

        $options["multipart"] = $body;
        return $this->client->request($method, $url, $options);
    }
}
