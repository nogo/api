<?php
namespace Nogo\Api\Middleware;

use Slim\Middleware;

class ResourceIdentifierMiddleware extends Middleware
{
    protected $route;

    public function __construct($route)
    {
        $this->route = $route;
    }

    public function call()
    {
        if (strpos($this->app->request()->getPathInfo(), $this->route) !== false) {
            $this->app->hook('slim.before.dispatch', array($this, 'onBeforeDispatch'));
        }
        $this->next->call();
    }

    public function onBeforeDispatch()
    {
        $app = $this->app;
        
        // Identify resource 
        $route = $app->router()->getCurrentRoute();
        $name = $route->getParam('resource');
//
//        $tables = $app->schemas->fetchTableList();
//        if (!in_array($name, $tables)) {
//            $app->halt(404, 'Resource [' . $name . '] not found.');
//        }
    }

}
