<?php

use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AppKernel
{

    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
        swoole_set_process_name("symfony_swoole_server");
        //Swoole\Process::daemon(false,false);
        Swoole\Coroutine::set([
            'max_coroutine' =>  300000,
        ]);
    }

    public function run()
    {
        $ret = go(function (){
            try{
                $container = new ContainerBuilder();
                $container->getParameterBag()->add(array(
                    'kernel.root_dir' => __DIR__,
                    'kernel.project_dir' => '',
                    'kernel.environment' => '',
                    'kernel.debug' => '',
                    'kernel.name' => '',
                    'kernel.cache_dir' => '',
                    'kernel.logs_dir' => '',
                    'kernel.bundles' => '',
                    'kernel.bundles_metadata' => '',
                    'kernel.charset' => 'UTF-8',
                    'kernel.container_class' => '',
                ));
                $load = new YamlFileLoader($container, new FileLocator(__DIR__.'/config'));
                $load->load('service.yml');

                //$container->register('file.locator', FileLocator::class)->addArgument('%@config_directory%');
                //$container->register('file.loader.yaml', YamlFileLoader::class)->addArgument('%file.locator%');
                //$collection = $container->get('file.loader.yaml');
                //$collection = $collection->load('service.yml');


                // todo 注册路由 Yaml
                $routes = new RouteCollection();
                $routes->add('hello', new Route('/hello/{name}', array('name' => 'World')));
                $routes->add('bye', new Route('/bye'));
                $container->register('routes', $routes);
                //$container->register('request.content', new RequestContext())->setShared(false);

                // urlMatcher
                $urlMatcher = new \Framework\UrlMatcher();
                $container->register('url.matcher', $urlMatcher);
                $urlMatcher->setRoutes($container->get('routes'));


                var_dump($container->getParameter('debug'));
                echo "-------";

                unset($routes);
                unset($urlMatcher);

                $httpServer = new HttpServer("0.0.0.0", 9501, false);
                $httpServer->handle('/', function (SwooleRequest $request, SwooleResponse $response) use ($container){
                    //Swoole\Http\Response 转化为 Symfony\Component\HttpFoundation\Request
                    $query = $request->get ? $request->get : array();
                    $tempRequest = $request->post ? $request->post : array();
                    $cookie = $request->cookie ? $request->cookie: array();
                    $files = $request->files ? $request->files : array();
                    $server = array_change_key_case($request->server, CASE_UPPER);
                    foreach ($request->header as $key => $val) {
                        $server[sprintf('HTTP_%s', strtoupper(str_replace('-', '_', $key)))] = $val;
                    }
                    $content = $request->rawContent();
                    // todo POST 需要验证
                    $wrapperRequest = new Request($query, $tempRequest, array(), $cookie, $files, $server, $content);

//                    $path = $wrapperRequest->getPathInfo();
//                    if (isset($map[$path])) {
//                        require $map[$path];
//                    } else {
//                        $response->setStatusCode(404);
//                        $response->setContent('Not Found');
//                    }

                    $urlMatcher = $container->get('file.locator');
                    //var_dump($urlMatcher->match($wrapperRequest->getPathInfo()));
                    var_dump($container->get('file.locator'));
                    echo "-------";

                    $wrapperResponse =new Response("<h1>Welcome to symfony swoole</h1>");
                    //将Symfony\Component\HttpFoundation\Response转化为Swoole\Http\Response
                    $response->status($wrapperResponse->getStatusCode());
                    foreach ($wrapperResponse->headers->allPreserveCase() as $key => $values) {
                        foreach ($values as $val) {
                            $response->header($key, $val);
                        }
                    }
                    $response->end($wrapperResponse->getContent());
                });
                $httpServer->start();
            }catch (\Swoole\Exception $exception){
                echo sprintf("Error: %s",$exception->getMessage());
            }catch (Exception $exception) {
                echo "{$exception->getMessage()}\n";
            }
        });
    }


    public function clearTempResource()
    {

    }
}