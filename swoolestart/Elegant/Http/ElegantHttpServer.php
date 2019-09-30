<?php


namespace Elegant\Http;


use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

class ElegantHttpServer
{
    private $server;

    private $port;

    public function __construct($port)
    {
        $this->port = $port ? $port : 9503;
        $this->server = new Server("0.0.0.0", $this->port);
        $this->server->on("request", array($this, "onRequest"));
    }

    /**
     * @param $request 请求信息
     * @param $response 返回信息
     */
    public function onRequest(Request $request, Response $response){
        var_dump($request);
        $response->header("Content-Type", "text/html; charset=utf-8");
        $response->end("hello world ".rand(100, 999));
    }

    public function start(){
        $this->server->start();
    }

}