<?php

namespace Guihigashi\SimpleVite;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'simple-vite');
        $this->app->singleton(SimpleVite::class, function () {
            return new SimpleVite();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => App::configPath('simple-vite.php'),
            ], 'simple-vite');
        }

        Blade::directive('simple_vite', function () {
            return "<?php echo SimpleVite::config(); ?>";
        });

    }
}
