<?php namespace Tekton\Messages\Providers;

use Illuminate\Support\ServiceProvider;
use Tekton\Messages\MessageManager;
use Tekton\Messages\Drivers\TektonDriver;

class TektonProvider extends ServiceProvider
{
    function provides()
    {
        return ['messages'];
    }

    function register()
    {
        $this->app->singleton('messages', function($app) {
            $driver = new TektonDriver($app->make('session'));
            return new MessageManager($driver);
        });
    }
}
