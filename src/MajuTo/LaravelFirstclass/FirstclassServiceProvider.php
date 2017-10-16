<?php
namespace MajuTo\LaravelFirstclass;

use Illuminate\Support\ServiceProvider;

class FirstclassServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('firstclass.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('firstclass', function ($app) {
            return new Firstclass();
        });
    }
}
