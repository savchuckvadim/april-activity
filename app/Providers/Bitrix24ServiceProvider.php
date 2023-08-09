<?php
namespace Antsupovsa\Bitrix24;

use Illuminate\Support\ServiceProvider;

class Bitrix24ServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBitrix24Service();

        if ($this->app->runningInConsole()) {
            $this->registerResources();
        }
    }

    /**
     * Register currency provider.
     *
     * @return void
     */
    public function registerBitrix24Service()
    {
        $this->app->singleton('bitrix24', function ($app) {
            return new Bitrix24($app->config->get('bitrix24', []));
        });
    }

    /**
     * Register currency resources.
     *
     * @return void
     */
    public function registerResources()
    {
        if ($this->isLumen() === false) {
            $this->publishes([
                __DIR__ . '/../config/bitrix24.php' => config_path('bitrix24.php'),
            ], 'config');
        }
    }


    /**
     * Check if package is running under Lumen app
     *
     * @return bool
     */
    protected function isLumen()
    {
        return str_contains($this->app->version(), 'Lumen') === true;
    }
}