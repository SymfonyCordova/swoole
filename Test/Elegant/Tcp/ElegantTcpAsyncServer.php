<?php

namespace Test\Elegant\Tcp;

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
        echo "Get Message From Client ${fd}:{$data}";
        $data = array(
            'task' => 'task_1',
            'params' => $data,
            'fd' => $fd
        );
        $taskId = $server->task(json_encode($data)); //获取异步ID
    }

    /**
     * 2. task接收到worker的数据进行处理
     * @param Server $server
     * @param $taskId 任务的Id
     * @param $fromId 从那个来的WorkerId
     * @param $data 从那个来的WorkerId的数据
     */
    public function onTask(Server $server, $taskId, $fromId, $data)
    {
        echo "This Task {$taskId} from Worker {$fromId} \n";
        echo "Data: {$data} \n";

        $data = json_decode($data,true);

        echo "Receive Task: {$data['task']} \n";
        var_dump($data['params']);

        $server->send($data['fd'], "Hello Task"); //发送给客户端内容 也可以while 广播给所有的客户端
        $server->finish("Finish"); // 3.task处理完成后通知给worker
    }

    public function onFinish(Server $server, $taskId, $data)
    {
        echo "Task {$taskId} finish \n";
        echo "Result: {$data}\n";
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