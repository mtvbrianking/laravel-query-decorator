<?php

namespace Bmatovu\QueryDecorator\Providers;

use Illuminate\Support\ServiceProvider;

class QueryDecoratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/json-api-paginate.php' => base_path('config/json-api-paginate.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/json-api-paginate.php', 'json-api-paginate');
        // $this->app->bind('hello-world', function () {
        //     return new HelloWorld();
        // });
    }
}
