<?php
namespace Caffeinated\Menu;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Boot the service provider.
	 *
	 * @return null
	 */
	public function boot()
	{
		// $this->publishes([
		// 	__DIR__.'/../../config/menu.php' => config_path('menu.php')
		// ]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// $this->mergeConfigFrom(
		//     __DIR__.'/../../config/menu.php', 'menu'
		// );

		$this->registerServices();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return string[]
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
		$this->app->bindShared('menu', function($app) {
			return new Menu($app['html'], $app['url'], $app['view']);
		});
	}
}
