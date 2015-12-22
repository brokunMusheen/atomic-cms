<?php namespace BrokunMusheen\Atomic;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AtomicServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/atomic.php', 'atomic'
        );
    }

    /**
     * Publish the plugin configuration.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/atomic.php' => config_path('atomic.php'),
        ]);
    }
}
