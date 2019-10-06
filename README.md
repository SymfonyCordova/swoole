# sfswoole

## 运行容器
    下拉项目
        git clone https://github.com/SymfonyCordova/swoole.git
    下拉镜像
        sudo docker pull registry.cn-hangzhou.aliyuncs.com/symfonycordova/sfswoole:v1
    进入项目目录
        cd swoole
    运行镜像
        sudo docker run --name swoole -p 80:80 -p 443:443 -p 3306:3306 -p 6379:6379 \
        -v $(pwd)/docker-swoole/mysql.conf.d:/etc/mysql/custom.conf.d \
        -v $(pwd)/docker-swoole/mysql.data:/var/lib/mysql \
        -v $(pwd)/docker-swoole/mysql.log:/var/log/mysql \
        -v $(pwd)/docker-swoole/nginx.conf.d:/etc/nginx/conf.d \
        -v $(pwd)/docker-swoole/nginx.log:/var/log/nginx \
        -v $(pwd)/docker-swoole/html:/var/www/html \
        -v $(pwd)/docker-swoole/redis:/etc/redis -d registry.cn-hangzhou.aliyuncs.com/symfonycordova/sfswoole:v1

## swoole基础
   父进程创建子进程时
        子进程会复制父进程的内存空间和上下文环境
        子进程和父进程的内存空间是独立的相互不影响的
        修改某个子进程的内存空间,不会修改父进程或其他子进程中的内存空间
   
   进程间通讯有很多种比如共享内存
        共享内存不属于任何一个进程
        在共享内存中分配的内存空间可以被任何进程访问
        即使进程关闭,共享内存仍然可以继续保留
        ipcs -m 可以查看共享内存
   
   swoole结构
        Master
            Main Reactor
                Reactor[相当于nginx]
                Reactor[读写事件的监听]
                Reactor
                Reactor
                ......
        Manager [管理worker,保证worker进程是固定的]
            Worker Worker Worker Worker ...... [写业务逻辑]
            Task   Task   Task   Task ...... [写业务异步耗时任务的逻辑]
        Master->Worker使用管道通信
        Worker->Task 使用管道或者消息队列通信
   
   Worker进程和Task进程
       Worker进程
            task() 
            onFinish()
       Task进程
            onTask()
            finish()
       Worker进程使用task向Task进程发送处理任务
       Task进程使用onTask获取Worker进程发送过来的任务并处理
       Task进程使用finish通知Worker的onFinish来接收已经完成的任务
       
       Task进程和Worker进程通过UnixSock管道通信(也可以配置消息队列)
       Task传递数据大小
            数据大小8K： 直接通过管道传递;数据大于8K;写入临时文件传递
       如果传递的数据是对像
            可以通过序列化传递一个对象的拷贝来传递对象
            因为Worker进程和Task进程是两个进程传递对象的话,Task中对象的改变不会反映到Worker进程中
            同样数据库连接网络连接对象不可传递 比如通过swoole server写一个mysql连接
   
   Timer
        取代cron比cron更加精度高的毫秒的定时任务
        基于Reactor线程(在Task Worker中使用系统定时器)
        基于epoll的timeout机制实现
        使用堆存放timer,提高检索效率
        Timer传递参数
            可以通过tick方法的第三个参数传递,也可以使用use闭包
        Timer传递对象
            onTimer是在调用tick方法的进程中回调,因此可以直接使用在Worker进程中声明的对象(局部变量无法访问到)
        Timer的清除
            Tick方法返回timer_id,可以使用swoole_time_clear清除指定的定时器

```php
        //每隔一段时间执行一次
        Timer::tick(2000, function ($timerId){
            echo "执行 {$timerId} \n";
        });

        //3000之后执行
        Timer::after(3000, function (){
            echo "3000 后执行\n";
        });
```

    Timer-示例
        Swoole Crontab
        原理:使用tick方法,每1s检查一次crontab任务表,如果发现有需要执行的任务,就通知Worker进程处理任务
        步骤:
            解析crontab配置文件,并存入DB(参考:https://github.com/osgochina/swoole-
            crontab/blob/master/src/include/ParseCrontab.class.php)
            在tick的回调中,检查所有crontab任务,找到满足当前时序的任务,并执行

## Process和Event
    子进程会复制父进程的IO句柄(fd描述符)
    进程间通信方式-管道
        管道是一组(2个)特殊的描述符
        管道需要在fork函数调用前创建
        如果某一端主动关闭管道,另一端的读取操作会直接返回0
    进程间通信方式-消息队列
        通过指定key创建一个消息队列
        在消息队列中传递的数据有大小限制
        消息队列会一直保留直到主动关闭
    IO多路服用
        epoll函数会监听注册在自己名下的所有的socket描述符(不仅仅是socket,只要属于IO都可以)
        当有socket感兴趣的事件发生时,epoll函数才会响应,并返回有事件发生的socket集合
        epoll的本质时阻塞IO,它的优点在于能同时处理大量的socket连接
    Event Loop
        swoole提供了epoll上层的封装
        在发起循环监听时,swoole会在底层创建一个Reactor线程,在这个线程中会运行一个epoll的实例
        我们需要注册描述符到这个epoll中,并且设置read和write的处理函数
        Event Loop不可用于FPM环境中
        
        比如 stream_socket_client 获取socket句柄 使用swoole_event_add加入到epoll中
        然后将 stdin也加入到swoole_event_add 
        epoll的好处是,读写都不会阻塞 在网络中单线程单进程也能实现高并发服务器客户端
        调用swoole_event_exit函数即可关闭事件循环 (注意,swoole_server程序中此函数无效)
        主要运用在非阻塞异步的客户端
    Process
        创建一个Poccess默认包含了管道,内存,IO句柄
            这个管道是可以父子进程通信的
        基于c语言封装的进程管理模块,方便PHP的多进程管理
        内置管道,消息队列接口,可方便实现进程通信
        提供自定义信号的管理

## HttpServer
    HttpServer本质是swoole_server,其协议解析部分固定使用http协议解析
    完整的Http协议请求会被解析并封装在swoole_http_request对象内
    所有的Http响应都通过swoole_http_respose对象进行封装和发送
    
    回调函数onRequest
        swoole_http_server不接收onReceive回调,但是可以使用TaskWorker和定时器
        swoole_http_server可以使用诸如onStart,onWorkerStart等回调
        $response/$request对象传递给其他函数时不要加&引用符号
    swoole_http_request
        $header Http请求的头部信息.类型为数组,所有key均为小写
        $server Http请求相关信息
        $get Http请求的GET参数,相当于PHP中的$_GET
        $post Http请求的POST参数,相当于PHP中的$_POST,Content-type限定为application/x-www-form-urlencoded
        $cookie Http请求的COOKIE参数,相当于PHP的$_COOKIE
        $files Http上传文件的文件信息,相当于PHP的$_FILES
        rawContent() 原始的Http Post内容,用于非application/x-www-form-urlencoded格式的请求比如json,xml
        
        注意:
            除了header和server外,其他四个变量可能没有赋值,因此使用前时使用isset判定
    swoole_http_response
        swoole_http_response::header($key, $value);
            设置Http响应头信息
        swoole_http_response::cookie($key, $value='',$path='/',$domain='',$secure=false,$httponly=false);
            设置Http响应的COOKIE信息,等效于PHP的setcookie函数
        swoole_http_response::status($code);
            设置Http响应的状态码,如200,404,503等
        swoole_http_response::gzip($level=1);
            开启GZIP压缩
        swoole_http_response::write($data);
            启用Http Chunk分段向浏览器发送相关内容
        swoole_http_response::sendfile($filename);
            发送文件到浏览器
        swoole_http_response::end($key, $value);
            发送Http响应

## WebSocket
    WebSocketServer是在swoole_http_server基础上增加协议解析
    完整的websocket协议请求会被解析并封装在frame对象内
    新增push方法用于发送websocket数据
    
    在websocketserver中也可以使用onRequest这样很好的解决了http发送请求进行群发什么的
    但是是要区分connect是http连接还是websocket连接
    
## 多协议多端口
    EOF协议
        data EOF data EOF data EOF
        用一组固定的,不会在正常数据内出现的字符串作为分割协议的标记,称之为EOF协议

```php
    class Server 
    {
        private $server;
        public function __construct() {
            $this->server = new swoole_server("0.0.0.0", 9501);
            $this->server->set(array(
                'worker_num' => 1,
                'daemonize' => false,
                'max_request' => 10000,
                'dispatch_mode' => 2,
                'package_max_length' => 8192, //一个完整包的大小 每个连接来的时候的缓冲区
                //'open_eof_check' => true, //开启eof检测
                'open_eof_split' => true, //开启eof检测
                'package_eof' => "\r\n", //用来标记什么来结尾
            ));
            $this->server->on('Start', array($this, 'onStrat'));
            $this->server->on('Connect', array($this, 'onConnect'));
            $this->server->on('Receive', array($this, 'onReceive'));
            $this->server->on('Close', array($this, 'cc'));
        }    
        
        public function onStrat($server){
            echo "Start \n";
        }

        public function onConnect($server, $fd, $fromId){
            echo "Client {$fd} connect\n";
        }

        public function onReceive(swoole_server $server, $fd, $fromId, $data){
            //这里接收的是swoole帮我拆分好的包
            var_dump($data);
        }
        
        public function onClose(){}
    }

```
    固定包头协议
        header length header data
        比如固定头是20个字节
        在数据首部加上一组固定格式的数据作为协议头,称之为固定头协议
        协议头的格式必须固定,并且其中需要标明后续数据的长度
        长度字段格式只支持"S,L,N,V"和"s,l,n,v" 
```php
    class Server 
    {
        private $server;
        public function __construct() {
            $this->server = new swoole_server("0.0.0.0", 9501);
            $this->server->set(array(
                'worker_num' => 1,
                'daemonize' => false,
                'max_request' => 10000,
                'dispatch_mode' => 2,
                'package_max_length' => 8192, //一个完整包的大小 每个连接来的时候的缓冲区
                'open_length_check' => true, //开启固定包头检测
                'package_length_offset' => 0, // 从头开始的偏移量
                'package_body_offset' => 4,
                'package_length_type' => N, //从头开始的偏移量读多的长度
            ));
            $this->server->on('Start', array($this, 'onStrat'));
            $this->server->on('Connect', array($this, 'onConnect'));
            $this->server->on('Receive', array($this, 'onReceive'));
            $this->server->on('Close', array($this, 'cc'));
        }    
        
        public function onStrat($server){
            echo "Start \n";
        }

        public function onConnect($server, $fd, $fromId){
            echo "Client {$fd} connect\n";
        }

        public function onReceive(swoole_server $server, $fd, $fromId, $data){
            //拆包
            $length = unpack("N", $data)[1];
            echo "Length = {$length}\n";
            $msg = substr($data, -$length);
            echo "Get Message From Client {$fd}:{$msg}";
        }
        
        public function onClose(){
            echo "Client {$fd} close connection\n";
        }
    }

    class Client {
        private $client;
        public function __construct() {
            $this->client = new swoole_client(SWOOLE_SOCK_TCP);
            $this->connect();
        }
        public function connect() {
            if(!$this->client->connect('127.0.0.1', 9501, 1)){
                echo "Error: {$this->client->errMsg} {$this->client->errCode} \n";
            }
            $msg_normal = "This is a Msg";
            $msg_length = pack("N", strlen($msg_normal)).$msg_normal;
            $i = 0;
            while($i < 100) {
                var_dump("test");
                $this->client->send($msg_length);
                $i++;
            }
        }

    }
```

    多端口
        swoole_server::listen
        函数功能: 创建一个额外的监听端口
        函数原型:
            swoole_server_port swoole_server->listen(string $host, int $port, int $type);
        注意事项
            Listen方法返回一个swoole_sverer_port对象,可以调用set, on方法
            新创建的端口需要设置协议参数,否则将会服用swoole_server的协议解析方法
            创建的端口无法使用onRequest和OnMessage回调
        这个用来做RPC通信,也可以用来通知内网的服务器,也可以用来和嵌入式通讯
        这样的好处是可以和任意的通讯协议通讯
        
        Hprose-swoole
            快速构建跨语言RPC的框架
            项目内容
                一个Http端口提供