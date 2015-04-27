<?php

namespace Nogo\Api\Middleware;

use Slim\Middleware;

class ResourceIdentifier extends Middleware
{

    protected $config;
    protected $route = '/api';

    public function __construct($config)
    {
        $this->config = $config;
        if (isset($this->config['prefix'])) {
            $this->route = $this->config['prefix'];
        }
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
        if (!array_key_exists($name, $this->config['resources'])) {
            $this->app->halt(404, 'Resource [' . $name . '] not found.');
        }
    }

}
