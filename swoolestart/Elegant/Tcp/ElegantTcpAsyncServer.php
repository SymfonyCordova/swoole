<?php

namespace Elegant\Tcp;

use Swoole\Server;

class ElegantTcpAsyncServer
{
    private $server;
    private $daemon = false;
    private $workerNum = 8;
    private $ManagerPid;
    private $MasterPid;
    private $connections;
    private $port;
    private $taskWorkerNum; //需要设置该参数

    public function __construct($port = 9501)
    {
        $this->port = $port;
        $this->server = new Server("0.0.0.0", $this->port);

        $this->server->set(array(
            'worker_num' => $this->workerNum,
            'daemonize' => $this->daemon,
            "task_worker_num" => $this->taskWorkerNum ? $this->taskWorkerNum : 4,
        ));

        $this->MasterPid = $this->server->master_pid;
        $this->ManagerPid = $this->server->manager_pid;

        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Connect', array($this, 'onConnect'));

        $this->server->on('Receive', array($this, 'onReceive'));
        $this->server->on('task', array($this, 'onTask'));
        $this->server->on('finish', array($this, 'onFinish'));

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
        //$server->send( $fd, "Hello {$fd}!" );
    }

    /**
     * 1.worker投递异步任务到task
     * @param swoole_server $server 服务器信息
     * @param $fd 客户端信息
     * @param $from_id 客户端id
     * @param $data 传来的数据
     */
    public function onReceive(Server $server, $fd, $from_id, $data)
    {
        $taskId = $server->task($data); //获取异步ID
        echo "异步ID: {$taskId}\n";
        $server->send($fd, $data);
    }

    /**
     * 2. task接收到worker的数据进行处理
     * @param Server $server
     * @param $taskId
     * @param $fromId
     * @param $data
     */
    public function onTask(Server $server, $taskId, $fromId, $data)
    {
        echo "执行异步ID： {$taskId} \n";
        $server->finish("{$data} -> OK \n"); // 3.task处理完成后通知给worker
    }

    public function onFinish(Server $server, $taskId, $data)
    {
        echo "执行完成 \n";
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