<?php

use Pimple\Container;
use Framework\Request;
use Swoole\Http\Server;
use Symfony\Component\Yaml\Yaml;


class AppKernelBak
{
    /**
     * @var Server
     */
    private $httpServer;

    /**
     * @var Container
     */
    protected $container;
    protected $boot = false;

    public function __construct()
    {
        $this->httpServer = new Server("0.0.0.0", 9501);
        $this->httpServer->set(array(
            'worker_num' => 1
        ));
        $this->httpServer->on('Start', array($this, 'onStart'));
        $this->httpServer->on('ManagerStart', array($this, 'onManagerStart'));
        $this->httpServer->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->httpServer->on('WorkerStop', array($this, 'onWorkerStop'));
        $this->httpServer->on('Request', array($this, 'onRequest'));
        $this->httpServer->start();
    }

    public function onStart()
    {
        swoole_set_process_name('simple_route_master');
    }

    public function onManagerStart()
    {
        swoole_set_process_name('simple_route_manager');
    }

    public function onWorkerStart(Server $server, $workerId)
    {
        swoole_set_process_name('simple_route_worker');
        if (!$this->boot) {
            $this->container = new Container();
            $this->container['container'] = function ($class) {
                return $this->container;
            };
            $this->registerContainerConfiguration($this->container);
            //$this->initializeFile();
            //$this->initializeContainer();
            $this->boot = true;
        }
    }

    protected function initializeFile()
    {

    }

    protected function initializeContainer()
    {

    }

    public function registerContainerConfiguration(Container $container)
    {
        try{
            $routes = Yaml::parseFile($this->getRootDir().'/config/route.yml');
            $container['routes'] = $routes;
        }catch (Exception $e){
            // todo 通知整个系统主进程程序结束
        }
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function onRequest($request, $response)
    {
        $wrapperRequest = new Request($request);
        $wrapperResponse = new \Framework\Response($response);

        $class = $action = null;
        foreach ($this->container['routes'] as $route) {
            if (in_array($wrapperRequest->getRequestMethod(),$route['methods']) && $wrapperRequest->getPathInfo() == $route['path']){
                $arr = explode(":",$route['defaults']['_controller']);
                $class = sprintf("\\%s\\Controller\\%sController", $arr[0], $arr[1]);
                $action = $arr[2];
            }
        }

        go(function () use ($wrapperRequest, $wrapperResponse, $class, $action) {
            if ($wrapperRequest->getPathInfo() === '/favicon.ico') {
                $wrapperResponse->getResponse()->end('');
                return ;
            }

            if ($class !== null && $action !== null){
                $object = new $class();
                if(method_exists($object, $action)){
                    $result = $object->$action($wrapperRequest);
                    $wrapperResponse->getResponse()->end($result);
                }else{
                    $wrapperResponse->getResponse()->end("not found resource");
                }
            }else{
                $wrapperResponse->getResponse()->end(sprintf("%s %s not found request.", $wrapperRequest->getRequestMethod(), $wrapperRequest->getPathInfo()));
            }
        });

    }

    public function onWorkerStop(Server $server, $workId) {
        unset($this->container);
        echo "workerId:{$workId} stop";
    }
}