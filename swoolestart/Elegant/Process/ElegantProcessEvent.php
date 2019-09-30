<?php


namespace Elegant\Process;


use Swoole\Event;
use Swoole\Process;

class ElegantProcessEvent
{
    protected $workers = array(); //进程池
    protected $workNum = 3; //创建进程的数量
    protected $callable;

    public function __construct()
    {
        $this->callable = function (Process $process){
            //子进程写入信息到管道pipe
            //在子进程内调用write，父进程可以调用read接收此数据
            $process->write("PID: {$process->pid} ");

            echo "写入信息: $process->pid $process->callback";
        };
        for ($i = 0; $i < $this->workNum; ++$i) {
            $process = new Process($this->callable);
            $pid = $process->start();
            $this->workers[$pid] = $process;
        }
        //添加进程事件 向每个一个子进程添加需要执行的动作
        //使用Event可以读取任意的文件描述符,管道,socket或者文件流等等,可以实现进程间的通信
        foreach ($this->workers as $process) {
            Event::add($process->pipe, function($pipe) use($process) {
                $data = $process->read();//能否读取数据
                echo "接收到: $data \n";
            });
        }
    }
}