<?php


namespace Framework;


use Symfony\Component\Routing\RouteCollection;

class UrlMatcher extends \Symfony\Component\Routing\Matcher\UrlMatcher
{
    /**
     * UrlMatcher constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param RouteCollection $routes
     */
    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

}