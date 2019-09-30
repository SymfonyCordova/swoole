<?php


namespace Elegant\Process;


use Swoole\Event;
use Swoole\Process;

class ElegantProcess
{
    protected $callable;
    public function __construct()
    {
        /**
         * $callable 子进程创建成功后执行的函数
         * $redirectStdinAndStdout 重定向子进程的标准输入输出。
         *      启动此选项后,在进程内echo将不是打印屏幕,而是写入管道
         *      读取键盘输入将变成从管道中读取。默认为阻塞读取
         * $pipeType 是否创建管道。启用$redirectStdinAndStdout后,此选项将忽略用户参数,强制为true
         *      如果子进程内没有进程间通讯,可以设置为false
         */

        $this->callable = function (Process $worker) {
            echo "PID".$worker->pid."\n";
            sleep(10);
        };

        $process = new Process($this->callable);
        $pid = $process->start();

        $process = new Process($this->callable);
        $pid = $process->start();

        $process = new Process($this->callable);
        $pid = $process->start();

        //等待结束
        Process::wait();
    }
}