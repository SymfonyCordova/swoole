<?php


namespace Elegant\Lock;


use Swoole\Lock;

class ElegantLock
{
    protected $lock;

    /**
     * ElegantLock constructor.
     */
    public function __construct()
    {
        $this->lock = new Lock(SWOOLE_MUTEX);//互斥锁
        echo "创建互斥锁\n";
        $this->lock->lock(); //开始锁定
        if ( pcntl_fork() > 0) {
            sleep(1);
            $this->lock->unlock();//解锁
        } else {
            echo "子进程 等待锁\n";
            $this->lock->lock();
            echo "子进程 获取锁\n";
            $this->lock->unlock();
            exit("子进程退出 \n");
        }
        echo "主进程释放锁 \n";
        unset($this->lock);
        sleep(1);
        echo "主进程退出 \n";
    }
}