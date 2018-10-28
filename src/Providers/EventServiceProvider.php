<?php
namespace Aruberuto\Repository\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Aruberuto\Repository\Events\RepositoryEntityCreated' => [
            'Aruberuto\Repository\Listeners\CleanCacheRepository'
        ],
        'Aruberuto\Repository\Events\RepositoryEntityUpdated' => [
            'Aruberuto\Repository\Listeners\CleanCacheRepository'
        ],
        'Aruberuto\Repository\Events\RepositoryEntityDeleted' => [
            'Aruberuto\Repository\Listeners\CleanCacheRepository'
        ]
    ];

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        $events = app('events');

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}
