<?php


namespace Framework;

use Swoole\Http\Response as SwooleHttpResponse;

class Response extends WrapperResponse
{

    /**
     * Response constructor.
     */
    public function __construct(SwooleHttpResponse $response)
    {
        parent::__construct($response);
    }
}