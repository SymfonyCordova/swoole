<?php


namespace Elegant\DNS;


class ElegantDnsQuery
{

    /**
     * DNS查询
     * ElegantDnsQuery constructor.
     */
    public function __construct()
    {
        swoole_async_dns_lookup("www.baidu.com", function ($host, $ip){
            echo "$host $ip\n";
        });
    }
}