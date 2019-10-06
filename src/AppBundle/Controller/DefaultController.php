<?php


namespace AppBundle\Controller;

use Framework\Request;

class DefaultController
{

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function Index(Request $request)
    {
        $arr = array(
            "swoole" => 'good',
            "symfony" => "aa",
        );
        return json_encode($arr);
    }


}