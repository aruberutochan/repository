<?php
namespace Aruberuto\Repository\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package Aruberuto\Repository\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/config/repository.php' => config_path('repository.php')
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../resources/config/repository.php', 'repository');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'repository');
    }


    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->app->register('Aruberuto\Repository\Providers\EventServiceProvider');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
