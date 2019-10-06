<?php


namespace Framework;


use Swoole\Http\Request;

abstract class WrapperRequest extends Request
{
    public $request;

    public function __construct(Request $request)
    {
        /**
         * $header Http请求的头部信息.类型为数组,所有key均为小写
        $server Http请求相关信息
        $get Http请求的GET参数,相当于PHP中的$_GET
        $post Http请求的POST参数,相当于PHP中的$_POST,Content-type限定为application/x-www-form-urlencoded
        $cookie Http请求的COOKIE参数,相当于PHP的$_COOKIE
        $files Http上传文件的文件信息,相当于PHP的$_FILES
        rawContent() 原始的Http Post内容,用于非application/x-www-form-urlencoded格式的请求比如json,xml
         *
         * 除了header和server外,其他四个变量可能没有赋值,因此使用前时使用isset判定
         */
        $this->request = $request;
        $this->request->get = isset($request->get) ? $request->get : array();
        if(isset($request->post)) {
            $this->request->post = $request->post;
        } else {
            $this->request->post = $request->rawContent();
        }
        $this->request->cookie = isset($request->cookie) ? $request->cookie : array();
        $this->request->files = isset($request->files) ? $request->files : array();
    }

    public function getRequest(){
        return $this->request;
    }

    public function getRequestMethod()
    {
        return $this->request->server['request_method'];
    }

    public function getPathInfo()
    {
        return $this->request->server['path_info'];
    }

    public function getClientIp()
    {
        return $this->request->server['remote_addr'];
    }
}