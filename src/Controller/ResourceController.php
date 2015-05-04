<?php

namespace Nogo\Api\Controller;

use Nogo\Api\Middleware\Database;
use Nogo\Api\Middleware\ApiMiddleware;
use Nogo\Api\Resource\Factory;
use Nogo\Api\View\Json as JsonView;
use Nogo\Framework\Controller\SlimController;
use Slim\Slim;

class ResourceController implements SlimController
{

    /**
     * @var Slim
     */
    protected $app;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Factory;
     */
    protected $factory;

    public function enable(Slim $app)
    {
        $this->app = $app;
        $this->config = $this->app->config('api');
        $this->factory = new Factory($this->config['resources']);

        // Middleware
        $this->app->add(new Database());
        $this->app->add(new ApiMiddleware($this->config));

        // Routes
        $this->app->get($this->config['prefix'] . '/:resource/:id', [$this, 'getAction'])
                ->conditions(['id' => '\d+'])
                ->name('resource_get');

        $this->app->get($this->config['prefix'] . '/:resource', [$this, 'listAction'])
                ->name('resource_get_list');
        
        $this->app->put($this->config['prefix'] . '/:resource/:id', [$this, 'putAction'])
                ->conditions(['id' => '\d+'])
                ->name('resource_put');
        
        $this->app->post($this->config['prefix'] . '/:resource', [$this, 'postAction'])
                ->name('resource_post');

        $this->app->delete($this->config['prefix'] . '/:resource/:id', [$this, 'deleteAction'])
                ->conditions(['id' => '\d+'])
                ->name('resource_delete');
    }

    /**
     * [GET] /$resource/
     * @param string $resource
     */
    public function listAction($resource)
    {
        $modelClass = $this->factory->with($resource);
        $this->render($modelClass->get()->toArray());
    }

    /**
     * [GET] /$resource/$id
     * @param string $resource
     * @param int $id
     */
    public function getAction($resource, $id)
    {
        $modelClass = $this->factory->with($resource);
        $model = $modelClass->find($id);
        if (!is_null($model)) {
            $this->render($model->toArray());
        } else {
            $this->render(['error' => 'Resource could not be found.'], 404);
        }
    }

    /**
     * [POST] /$resource/
     * @param type $resource
     * @return type
     */
    public function postAction($resource)
    {
        $env = $this->app->environment();
        if (empty($env['slim.input'])) {
            $this->render(['error' => 'Data not valid'], 400);
        } else {
            $model = $this->factory->create($resource, $env['slim.input']);
            $model->save();
            $this->render($model->toArray());
        }
    }

    /**
     * [PUT] /$resource/$id
     * @param string $resource
     * @param int $id
     * @return type
     */
    public function putAction($resource, $id)
    {
        $modelClass = $this->factory->with($resource);
        $model = $modelClass->find($id);
        if (is_null($model)) {
            $this->render(['error' => 'Not found'], 404);
        } else {
            $env = $this->app->environment();
            if (empty($env['slim.input'])) {
                $this->render(['error' => 'Data not valid'], 400);
            } else {
                $model->fill($env['slim.input']);
                $model->save();
                $this->render($model->toArray());
            }
        }
    }

    public function deleteAction($resource, $id)
    {
        $modelClass = $this->factory->getClass($resource);
        $model = $modelClass::find($id);
        if (is_null($model)) {
            $this->render(['error' => 'Not found'], 404);
        } else {
            $model->delete();
            $this->render($model->toArray());
        }
    }

    /**
     * Render content and status
     *
     * @param mixed $data
     * @param int $status
     */
    protected function render(array $data, $status = 200)
    {
        $this->app->view(new JsonView());

        $this->app->response()->setStatus($status);

        $this->app->render('', $data);
    }

}
