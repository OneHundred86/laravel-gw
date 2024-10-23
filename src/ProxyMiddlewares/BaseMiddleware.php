<?php

namespace Oh86\GW\ProxyMiddlewares;

use Illuminate\Http\Request;

class BaseMiddleware
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
