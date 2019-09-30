<?php

namespace Elegant\Tcp;

use Swoole\Server;

class ElegantTcpServer
{
    private $server;
    private $daemon = false;
    private $workerNum = 8;
    private $ManagerPid;
    private $MasterPid;
    private $connections;
    private $port;

    public function __construct($port)
    {
        $this->port = $port ? $port : 9501;
        $this->server = new Server("0.0.0.0", $this->port);

        $this->server->set(array(
            'worker_num' => $this->workerNum,
            'daemonize' => $this->daemon,
        ));

        $this->MasterPid = $this->server->master_pid;  //主进程的PID，通过向主进程发送SIGTERM信号可安全关闭服务器
        $this->ManagerPid = $this->server->manager_pid;  //管理进程的PID，通过向管理进程发送SIGUSR1信号可实现柔性重启

        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Connect', array($this, 'onConnect'));
        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->on('Close', array($this, 'onClose'));
    }

    public function onStart(Server $server)
    {
        echo "Tcp Server Start\n";
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