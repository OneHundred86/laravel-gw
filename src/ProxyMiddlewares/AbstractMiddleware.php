<?php

namespace Oh86\GW\ProxyMiddlewares;

use Illuminate\Http\Request;

abstract class AbstractMiddleware
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param mixed $args
     * @return callable
     */
    abstract public function __invoke(...$args);
}
