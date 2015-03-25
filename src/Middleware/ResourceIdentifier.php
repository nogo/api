<?php
namespace Nogo\Api\Middleware;

use Slim\Middleware;

class ResourceIdentifier extends Middleware
{
    protected $route;
    protected $allowed = [];

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
        $this->app->log->debug('Call middleware [ResourceIdentifier]');
        $route = $this->app->router()->getCurrentRoute();
        $name = $route->getParam('resource');
        $tables = $this->app->schemas->fetchTableList();
        if (!in_array($name, $tables)) {
            $this->app->halt(404, 'Resource [' . $name . '] not found.');
        }
    }

}
