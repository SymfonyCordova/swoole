<?php


namespace Test\Elegant\Udp;

use Swoole\Server;

class ElegantUdpServer
{
    private $server;
    private $port;
    private $daemon = false;
    private $workerNum = 8;
    private $ManagerPid;
    private $MasterPid;
    private $connections;

    public function __construct($port)
    {
        $this->port = $port ? $port : 9502;
        /**
         * SWOOLE_PROCESS 进程模式
         * SWOOLE_SOCK_UDP udp
         */
        $this->server = new Server("0.0.0.0", $this->port, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);

        $this->server->set(array(
            'worker_num' => $this->workerNum,
            'daemonize' => $this->daemon,
        ));

        $this->MasterPid = $this->server->master_pid;
        $this->ManagerPid = $this->server->manager_pid;

        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Connect', array($this, 'onConnect'));
        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->on('Packet', array($this, 'onPacket'));
        $this->server->on('Close', array($this, 'onClose'));
    }

    public function onStart(Server $server)
    {
        echo "Udp Server Start\n";
    }

    /**
     * @param $server 服务器信息
     * @param $fd 客户端信息
     * @param $from_id
     */
    public function onConnect(Server $server, $fd, $from_id)
    {
        $server->send( $fd, "Hello {$fd}!" );
    }

    /**
     * @param swoole_server $server 服务器信息
     * @param $fd 客户端信息
     * @param $from_id 客户端id
     * @param $data 传来的数据
     */
    public function onReceive(Server $server, $fd, $from_id, $data )
    {
        echo "Get Message From Client {$fd}:{$data}\n";
        var_dump($data);
        $server->send($fd, $data);
    }

    /**
     * @param \swoole_server $server 服务器信息
     * @param $data 接收到的数据
     * @param $fd 客户端信息
     */
    public function onPacket(Server $server, $data, $fd)
    {
        $server->sendto($fd['address'], $fd['port'], "Server: {$data}");
        var_dump($fd);
    }

    /**
     * @param $server 服务器信息
     * @param $fd 客户端信息
     * @param $from_id 传来的数据
     */
    public function onClose(Server $server, $fd, $from_id )
    {
        echo "Client {$fd} close connection\n";
    }

    public function getConnections()
    {
        $this->connections = $this->server->connections;

        return $this->connections;
    }

    public function start()
    {
        $this->server->start();
    }

}