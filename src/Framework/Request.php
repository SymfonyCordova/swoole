<?php


namespace Framework;

use Swoole\Http\Request as SwooleHttpRequest;

class Request extends WrapperRequest
{

    public function __construct(SwooleHttpRequest $request)
    {
        parent::__construct($request);
    }
}