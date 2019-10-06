<?php


namespace Test\Only;


use Swoole\Http\Server;

class UploadFile
{

    protected $server;
    /**
     * UploadFile constructor.
     */
    public function __construct()
    {
        $this->server = new Server("0.0.0.0", 9501);
        $this->server->on('Request', array($this, 'onRequest'));
        $this->server->set(array(
            'max_package_length' => 200000000
        ));
        $this->server->start();
    }

    public function onRequest($request, $response)
    {
        if($request->server['request_method'] == 'GET')
        {
            return ;
        }
        var_dump($request->files);

        $file = $request->files['file'];

        $fileName = $file['name'];
        $fileTempPath = $file['tmp_name'];
        $uploadPath = __DIR__.'/uploader/';
        if(!file_exists($uploadPath)){
            mkdir($uploadPath);
        }
        move_uploaded_file($fileTempPath, $uploadPath, $fileName);
        $response->end("<h1>Upload Success</h1>");

    }
}