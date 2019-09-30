<?php


namespace Elegant\Tcp;

use Swoole\Client;

class ElegantTcpAsyncClient
{
    private $client;
    private $host;
    private $post;
    private $timeout;

    public function __construct($host = "127.0.0.1", $post = 9501, $timeout = 1) {
        $this->client = new Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);
        $this->host = $host;
        $this->post = $post ;
        $this->timeout = $timeout;

        $this->client->on('connect', array($this,'onConnect'));
        $this->client->on('receive', array($this,'onReceive'));
        $this->client->on('error', array($this,'onError'));
        $this->client->on('close', array($this,'onClose'));
    }

    /**
     * @param $server 服务端信息
     */
    public function onConnect($server) {
        //$server->send("hello \n");
    }

    /**
     * @param $server 服务端信息
     * @param $data 服务器返回的数据
     */
    public function onReceive($server, $data)
    {
        echo "data:{$data} \n";
    }

    /**
     * @param $server 服务端信息
     */
    public function onError($server)
    {
        echo "失败\n";
    }

    public function onClose($server)
    {
        echo '关闭\n';
    }

    public function connect()
    {
        $this->client->connect($this->host, $this->post, $this->timeout);
    }
}