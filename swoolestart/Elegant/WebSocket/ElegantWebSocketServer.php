<?php


namespace Elegant\WebSocket;

use Swoole\WebSocket\Server;

class ElegantWebSocketServer
{
    private $server;
    private $port;

    public function __construct($port)
    {
        $this->port = $port ? $port : 9504;
        $this->server = new Server("0.0.0.0", $this->port);

        //$this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Open', array($this, 'onOpen')); //建立连接
        $this->server->on('Message', array($this, 'onMessage')); //接收信息
        $this->server->on('Close', array($this, 'onClose')); //关闭连接
    }

    /**
     * @param $ws 服务器
     * @param $request 客户端信息
     */
    public function onOpen(Server $ws, $request)
    {
        var_dump($request);
        $ws->push($request->fd, "welcome \n");
    }

    /**
     * @param $ws 服务器
     * @param $request 客户端信息
     */
    public function onMessage(Server $ws, $request)
    {
        echo "Message: {$request->data}";
        $ws->push($request->fd, "get it message");
    }

    /**
     * @param $ws 服务器
     * @param $request 客户端信息
     */
    public function onClose(Server $ws, $request)
    {
        echo "close \n";
    }

    public function start()
    {
        $this->server->start();
    }
}