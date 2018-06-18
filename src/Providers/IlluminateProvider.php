<?php namespace Tekton\Messages\Providers;

use Illuminate\Support\ServiceProvider;
use Tekton\Messages\MessageManager;
use Tekton\Messages\Drivers\IlluminateDriver;

class IlluminateProvider extends ServiceProvider
{
    function provides()
    {
        return ['messages'];
    }

    function register()
    {
        $this->app->singleton('messages', function($app) {
            $driver = new IlluminateDriver($app->make('session'));
            return new MessageManager($driver);
        });
    }
}
