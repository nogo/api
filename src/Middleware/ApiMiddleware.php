<?php

namespace Nogo\Api\Middleware;

use Slim\Middleware;

/**
 * ApiMiddleware
 *
 * @author Danilo Kuehn <dk@nogo-software.de>
 */
class ApiMiddleware extends Middleware
{

    protected $config;
    protected $route = '/';
    protected $mediaType;

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
            $this->app->hook('slim.after.router', array($this, 'onAfterRouter'));
        }
        
        $this->next->call();
    }

    /**
     * Check if resource is allowed and decode data
     */
    public function onBeforeDispatch()
    {
        $route = $this->app->router()->getCurrentRoute();
        $name = $route->getParam('resource');
        if (!array_key_exists($name, $this->config['resources'])) {
            $this->app->halt(404, 'Resource [' . $name . '] not found.');
        } else {
            $env = $this->app->environment();
            $env['slim.input_original'] = $env['slim.input'];
            $env['slim.input'] = $this->decode($this->app->request()->getMediaType(), $env['slim.input']);
        }
    }

    /**
     * Add api header to response
     */
    public function onAfterRouter()
    {
        if (isset($this->config['headers'])) {
            $header = $this->config['headers'];
            foreach ($header as $key => $value) {
                $this->app->response()->headers()->set($key, $value);
            }
        } else {
            $this->app->contentType('application/json');
        }
    }

    /**
     * Decode data by given media type
     * 
     * @param type $mediaType
     * @param type $data
     * @return type
     */
    public function decode($mediaType, $data)
    {
        $result = $data;
        switch ($mediaType) {
            case 'application/json':
                $decoded = json_decode($data, true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    $result = $decoded;
                }
                break;
        }

        return $result;
    }

}
