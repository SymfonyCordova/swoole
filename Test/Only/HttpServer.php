<?php


namespace Test\Only;


use Pimple\Container;
use Framework\Request;
use Framework\Response;
use Swoole\Http\Server;
use Symfony\Component\Yaml\Yaml;


class HttpServer
{
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
        var_dump($this->getRootDir());
        //Yaml::parseFile($this->getRootDir().'/app/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function onRequest($request, $response)
    {
        $wrapperRequest = new Request($request);
        $wrapperResponse = new Response($response);
        go(function () use ($wrapperRequest, $wrapperResponse) {
            $wrapperResponse->end("hello");
        });
    }

    public function onWorkerStop(Server $server, int $workId) {
        unset($this->container);
        echo "workerId:{$workId} stop";
    }
}