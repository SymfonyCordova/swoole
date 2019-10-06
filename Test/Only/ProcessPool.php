<?php


namespace Test\Only;


use Swoole\Event;
use Swoole\Process;
use Swoole\Timer;

class ProcessPool
{
    /**
     * @var Process
     */
    private $process;
    private $processList = array();
    private $processUse = array();
    private $minWorkerNum = 3;
    private $maxWorkerNum = 6;
    private $currentWorkerNum;

    public function __construct()
    {
        new Process(array($this, 'run'), false, 2);
    }

    public function run()
    {
        $this->currentWorkerNum = $this->minWorkerNum;
        for ($i = 0; $i < $this->currentWorkerNum; ++$i) {
            $process = new Process(array($this, 'taskRun'), false, 2);
            $pid = $process->start();
            $this->processList[$pid] = $process;
            $this->processUse[$pid] = 0;
        }

        foreach ($this->processList as $process) {
            Event::add($process->pipe, function ($pipe) use($process) {
                $data = $process->read();
                var_dump($data);
                $this->processUse[$data] = 0; //我已经写下来了,没有事情了
            });
        }

        Timer::tick(1000, function ($timerId) {
            static $index = 0;
            $index++;
            $flag = true;
            foreach ($this->processUse as $pid => $used) {
                if($used == 0) { //看那些进程没有在工作,就选其中一个工作
                    $flag = false;
                    $this->processUse[$pid] = 1;
                    $this->processList[$pid]->write($index, 'Hello');
                    break;
                }
            }
            //如果当前的工作进程已经没有一个空闲的,并且当前的工作数量是小于最大工作进程的,那么创建进程进行工作
            if ( $flag && $this->currentWorkerNum < $this->maxWorkerNum ) {
                $process = new Process(array($this, 'taskRun'), false, 2);
                $pid = $process->start();
                $this->processList[$pid] = $process;
                $this->processUse[$pid] = 1;
                $this->processList[$pid]->write($index, "Hello");
                $this->currentWorkerNum++;
            }
            var_dump($index);
            if ($index == 10) {
                foreach ($this->processList as $process) {
                    $process->write("exit");
                }
                Timer::clear($timerId);
                $this->process->exit();
            }
        });
    }

    public function taskRun(Process $process)
    {
        Event::add($process->pipe, function ($pipe)use($process){
            $data = $process->read();
            var_dump($process->pid.":".$data);
            if ($data == 'exit') {
                $process->exit();
                exit();
            }
            sleep(5);
            $process->write("".$process->pid);
        });
    }
}