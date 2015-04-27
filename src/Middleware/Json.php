<?php

namespace Nogo\Api\Middleware;

use Slim\Middleware;

/**
 * Json middleware set the correct headers.
 *
 * @author Danilo Kuehn <dk@nogo-software.de>
 */
class Json extends Middleware
{
    /**
     * @var array
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function call()
    {
        if ($this->app->request()->getMediaType() === 'application/json') {
            $env = $this->app->environment();
            $env['slim.input_original'] = $env['slim.input'];
            $result = json_decode($env['slim.input'], true);
            if(json_last_error() === JSON_ERROR_NONE) {
                $env['slim.input'] = $result;
            }
        }

        $this->next->call();

        if (isset($this->config['headers'])) {
            $header = $this->config['headers'];
            foreach ($header as $key => $value) {
                $this->app->response()->headers()->set($key, $value);
            }
        } else {
            $this->app->contentType('application/json');
        }
    }
}
