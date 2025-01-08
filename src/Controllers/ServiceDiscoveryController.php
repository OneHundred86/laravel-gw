<?php

namespace Oh86\GW\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Oh86\GW\Config\GatewayConfig;
use Oh86\GW\ProxyMiddlewares\PrivateRequest;
use Oh86\Http\Exceptions\ErrorCodeException;
use Oh86\Http\Response\OkResponse;

class ServiceDiscoveryController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            /**
             * @var array{app: string, ticket: string}
             */
            $config = config("gw.private_request");

            if (!$config || $config['app'] != $request->header('GW-Private-App')) {
                throw new ErrorCodeException(403, "app error", null, 403);
            }

            $time = $request->header('GW-Private-Time');
            if (abs(time() - $time) > 300) {
                throw new ErrorCodeException(403, "time error", null, 403);
            }

            $expectedSignature = sm3(sprintf(
                "%s%s%s",
                $config['app'],
                $time,
                $config['ticket'],
            ));

            if ($expectedSignature != $request->header('GW-Private-Sign')) {
                throw new ErrorCodeException(403, "signature error", null, 403);
            }

            return $next($request);
        });
    }

    public function getServiceConfig(Request $request)
    {
        $request->validate([
            'appTag' => 'required|string',
        ]);

        $appTag = $request->get('appTag');
        $config = GatewayConfig::getRouteConfig($appTag);

        if (!$config) {
            return new OkResponse(null);
        }

        $proxyPass = $config->getProxyPass();
        // 没有配置{path}变量，`baseUrl` 取 `proxy_pass`
        if (!Str::contains($proxyPass, '{path}')) {
            $baseUrl = $proxyPass;
        } else {
            /**  @var array{scheme:string, host:string, port:?int, path:string} */
            $urlArr = parse_url($proxyPass);

            if ($urlArr['port'] ?? false) {
                $baseUrl = $urlArr['scheme'] . '://' . $urlArr['host'] . ':' . $urlArr['port'];
            } else {
                $baseUrl = $urlArr['scheme'] . '://' . $urlArr['host'];
            }
        }


        $app = $ticket = null;
        foreach ($config->getProxyMiddlewares() as [$middlewareClass, $middlewareArgs]) {
            if (trim($middlewareClass, '\\') == PrivateRequest::class) {
                $app = $middlewareArgs[0] ?? '';
                $ticket = $middlewareArgs[1] ?? '';
            }
        }

        return new OkResponse([
            'baseUrl' => $baseUrl,
            'app' => $app,
            'ticket' => $ticket,
        ]);
    }
}