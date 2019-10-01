<?php


namespace Test\Elegant\Process;


use Swoole\Process;

/**
 * 进程间队列通讯 swoole process默认是pipe管道通讯
 * Class ElegantProcessQueueCommunication
 * @package Elegant\Process
 */
class ElegantProcessQueueCommunication
{
    protected $callable;
    protected $workers = array();
    protected $workerNum = 2;

    public function __construct()
    {
        //进程执行函数
        $this->callable = function (Process $process) {
            $receive = $process->pop(); //8192
            echo "从主进程获取到的数据: $receive \n";
            sleep(5);
            $process->exit(0); //当前进程退出
        };
        for ($i = 0; $i < $this->workerNum; ++$i) {
            $process = new Process($this->callable, false,false);
            $process->useQueue(); //开启队列,类似于全局函数
            $pid = $process->start();
            $this->workers[$pid] = $process;
        }

        // 主进程 向子进程添加数据
        foreach ($this->workers as $pid => $process) {
            $process->push("Hello 子进程 $pid \n");
        }

        //等待 子进程结束 回收资源
        //Process::wait(); 这个是比较暴力的回收子进程
        for ($i = 0; $i < $this->workerNum; $i++) {
            $ret = Process::wait(); //等待执行完成
            $pid = $ret['pid'];
            unset($this->workerNum[$pid]);
            echo "子进程退出 $pid \n";
        }
    }
}