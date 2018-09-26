<?php
namespace Caffeinated\Menus;

use Illuminate\Support\ServiceProvider;

class MenusServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->publishConfig();
    }
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerServices();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['menu'];
	}

	/**
	 * Register the package services.
	 *
	 * @return void
	 */
	protected function registerServices()
	{
		// Bind our Menu class to the IoC container
		$this->app->singleton('menu', function($app) {
			return new Menu($app['config'], $app['view'], $app['url']);
		});
	}

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/menu.php' => config_path('menu.php'),
        ], 'caffeinated-menu');
    }
}
