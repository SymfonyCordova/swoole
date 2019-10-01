<?php

use Elegant\Tcp\ElegantTcpAsyncServer;
use Elegant\Tcp\ElegantTcpClient;

require __DIR__ . '/vendor/autoload.php';

if(count($argv) !== 2){
    die("参数错误 \n");
}

if ($argv[1] === 'client') {

} elseif ($argv[1] === "process") {
    new \Test\Only\BaseProcess();

} elseif ($argv[1] === "signal"){


} elseif ($argv[1] === "lock"){

} elseif ($argv[1] === "dns") {

} elseif ($argv[1] === "async_file"){


} elseif ($argv[1] === "async_event"){

} elseif ($argv[1] === "async_mysql"){

} else {

}

