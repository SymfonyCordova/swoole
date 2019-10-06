<?php


namespace Framework;

use Swoole\Http\Response;

abstract class WrapperResponse extends Response
{
    /**
     * @var Response
     */
    public $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(){
        return $this->response;
    }
}