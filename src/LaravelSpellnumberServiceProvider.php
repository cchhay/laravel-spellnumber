<?php

namespace Cchhay\LaravelSpellnumber;

use Illuminate\Support\ServiceProvider;

class LaravelSpellnumberServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(): void
    {
        // $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
    }

    public function register(): void
    {
        $this->registerPublishables();
        $this->registerBindings();
    }

    /**
     * Public configuration file into laravel configuration path.
     */
    private function registerPublishables()
    {
        $arrayPublishable = [
            'config' => [
                __DIR__ . '/config/spellnumber.php' => config_path('spellnumber.php'),
            ],
        ];
        foreach ($arrayPublishable as $publish => $paths) {
            $this->publishes($paths, $publish);
        }
    }

    /**
     * Register spellnumber Number Class into service provider with it configuration setting.
     */
    protected function registerBindings()
    {
        $this->app->singleton('Spellnumber', function () {
            return new Spellnumber(config('spellnumber'));
        });
    }
}
