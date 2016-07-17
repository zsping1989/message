<?php

namespace Message\Providers;

use Illuminate\Support\ServiceProvider;
use Message\MessageManager;

class MessageServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //注册
        $this->app->singleton('message', function($app)
        {
            return new MessageManager();
        });
    }
    public function provides()
    {
        return ['message'];
    }
}
