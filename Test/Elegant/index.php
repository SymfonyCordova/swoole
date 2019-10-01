<?php

use Elegant\Tcp\ElegantTcpAsyncServer;
use Elegant\Tcp\ElegantTcpClient;

require __DIR__ . '/vendor/autoload.php';

if(count($argv) !== 2){
    die("参数错误 \n");
}

if ($argv[1] === 'client') {
//    $client = new ElegantTcpClient();
//    $client->connect();
//    fwrite(STDOUT, "请输入消息 Please input msg：");
//    $msg = trim(fgets(STDIN));
//    $client->send( $msg );
//    $message = $client->recv(); //接收数据
//    echo "Get Message From Server:{$message}\n";

    $client = new \Elegant\Tcp\ElegantTcpAsyncClient();
    $client->connect();
} elseif ($argv[1] === "process") {
//    new \Elegant\Process\ElegantProcess();
//    new \Elegant\Process\ElegantProcessEvent();
//    new \Elegant\Process\ElegantProcessQueueCommunication();
//    new \Elegant\Process\ProcessSignalTrigger();

} elseif ($argv[1] === "signal"){
    swoole_process::signal(SIGALRM, function () {
        static $i = 0;
        echo "$i \n";
        $i++;
        if ($i > 10) {
            swoole_process::alarm(-1);
        }
    });
    swoole_process::alarm(100 * 1000);

} elseif ($argv[1] === "lock"){
    new Elegant\Lock\ElegantLock();
} elseif ($argv[1] === "dns") {
    //new \Elegant\DNS\ElegantDnsQuery();
} elseif ($argv[1] === "async_file"){
    swoole_async_readfile(__DIR__ . "/index.html", function ($fileName, $content){
        echo "$fileName \n $content \n";
    });

} elseif ($argv[1] === "async_event"){
    $fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
    fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    swoole_event_add($fp, function ($fp){
        $response = fread($fp, 8192);
        var_dump($response);
        swoole_event_del($fp);
        fclose($fp);
    });
    echo "先执行完成\n";
} elseif ($argv[1] === "async_mysql"){
    $db = new swoole_mysql();
    $config = array(
        'host'      => '172.17.0.2',
        'user'      => 'root',
        'password'  => 'root',
        'database'  => 'mysql',
        'charset'   => 'utf8',
    );
    $db->connect($config, function (\swoole_mysql $db, $res){

        if ($res === false) {
            var_dump($db->connect_errno, $db->connect_error);
            die("连接失败\n");
        }

        $sql = ' show tables;';
        $db->query($sql, function(\swoole_mysql $db, $res){
            if ($res === false) {
                var_dump($db->error);
                die("操作失败\n");
            } elseif($res === true){
                var_dump($db->affected_rows, $db->insert_id);
            }
            var_dump($res);
            $db->close();
        });

    });
    echo "先执行\n";
} else {
    $server = new ElegantTcpAsyncServer();
    $server->start();
}

