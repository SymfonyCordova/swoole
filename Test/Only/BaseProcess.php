<?php


namespace Test\Only;


use Swoole\Event;
use Swoole\Process;
use Swoole\Timer;

class BaseProcess
{
    private $process;

    public function __construct()
    {
        $this->process = new Process(array($this, 'write'), false, true);
        $this->process->start();
        Event::add($this->process->pipe, array($this, 'read'));
        //Process::daemon(true, true);
        Process::signal(SIGCHLD, array($this, 'handleSingle'));
    }

    public function write(Process $process)
    {
        Timer::tick(1000, array($this, 'doThing'));
    }

    public function doThing($timeId)
    {
        static $index = 0;
        $index = $index + 1;
        $this->process->write("Hello");
        var_dump($index);
        if ( $index == 10 ) {
            Timer::clear($timeId);
        }
    }

    public function read($pipe)
    {
        $data = $this->process->read();
        echo "RECV: ". $data.PHP_EOL;
    }

    public function handleSingle ($single)
    {
        while ($ret = Process::wait(false)) {
            echo "PID={$ret['pid']}\n";
        }
    }
}