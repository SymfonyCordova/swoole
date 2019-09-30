<?php

namespace Elegant\Timer;

use Swoole\Timer;

class ElegantTimer
{
    public function __construct()
    {
        //每隔一段时间执行一次
        Timer::tick(2000, function ($timerId){
            echo "执行 {$timerId} \n";
        });

        //3000之后执行
        Timer::after(3000, function (){
            echo "3000 后执行\n";
        });
    }
}