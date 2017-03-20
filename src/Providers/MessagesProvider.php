<?php namespace Tekton\Messages\Providers;

use Tekton\Support\ServiceProvider;
use Tekton\Messages\MessageManager;

class MessagesProvider extends ServiceProvider {

    function register() {
        $this->app->register(\Tekton\Session\Providers\SessionProvider::class);

        $this->app->singleton('messages', function($app) {
            return new MessageManager($app->make('session'));
        });
    }
}
