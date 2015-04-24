<?php
namespace Nogo\Api\Middleware;

use Nogo\Framework\Database\Connector;
use Slim\Middleware;

class Initialize extends Middleware
{
    
    public function call() {
        $app = $this->app;
        $app->log->debug('Call middleware [Initialize]');

        // Database
        $db = new Connector(
            $app->config('database.adapter'),
            $app->config('database.dsn'),
            $app->config('database.username'),
            $app->config('database.password')
        );

        $app->container->singleton('connection', function() use ($db) {
            return $db->connect();
        });

        $app->container->singleton('schemas', function() use ($app, $db) {
            return $db->getSchema($app->connection->getPdo());
        });

        $app->container->singleton('queries', function() use ($db) {
            return $db->getQueryFactory();
        });

        $this->next->call();
    }
}
