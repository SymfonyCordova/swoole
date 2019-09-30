<?php


namespace Elegant\Process;

use Swoole\Process;

/**
 * 进程信号触发器
 * Class ProcessSignalTrigger
 * @package Elegant\Process
 */
class ProcessSignalTrigger
{

    /**
     * ProcessSignalTrigger constructor.
     */
    public function __construct()
    {
        //触发函数 异步执行
        Process::signal(SIGALRM, function () {
            echo "1\n";
        });
        //定时信号
        Process::alarm(100 * 1000);

        while (true) {}
    }
}