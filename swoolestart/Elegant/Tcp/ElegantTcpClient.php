<?php

namespace Elegant\Tcp;

use Swoole\Client;

class ElegantTcpClient
{
    private $client;
    private $host;
    private $post;
    private $timeout;

    public function __construct($host = "127.0.0.1", $post = 9501, $timeout = 1) {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->host = $host;
        $this->post = $post ;
        $this->timeout = $timeout;
    }

    public function connect() {
        if(!$this->client->connect($this->host, $this->post, $this->timeout) ) {
            exit("connect failed. Error: {$this->client->errCode}\n");
        }
    }

    public function send($data)
    {
        $this->client->send($data) or die("发送失败");
    }

    public function recv()
    {
        return $this->client->recv();
    }

    public function close(){
        if (!$this->client){
            $this->client->close();
        }
    }
}