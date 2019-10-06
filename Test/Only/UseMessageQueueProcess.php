<?php


namespace Test\Only;


use Swoole\Process;
use Swoole\Timer;

class UseMessageQueueProcess
{

    private $process;

    public function __construct()
    {
        $this->process = new Process(array($this, 'write'), false, true);
        if (!$this->process->useQueue(123)) {
            var_dump(swoole_strerror(swoole_errno()));
            exit;
        }
        $this->process->start();
        Process::signal(SIGCHLD, array($this, 'handleSingle'));
        while (true) {
            $data = $this->process->pop();
            echo "RECV: ".$data.PHP_EOL;
        }

    }

    public function write(Process $process)
    {
        Timer::tick(1000, array($this, 'doThing'));
    }

    public function doThing($timeId)
    {
        static $index = 0;
        $index++;
        $this->process->push("Hello");
        var_dump($index);
        if ($index === 10) {
            Timer::clear($timeId);
        }
    }

    public function handleSingle ($single)
    {
        while ($ret = Process::wait(false)) {
            echo "PID={$ret['pid']}\n";
        }
    }
}