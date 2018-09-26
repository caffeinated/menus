<?php
namespace Caffeinated\Menus;

use Illuminate\Config\Repository;

class Menu
{
	/**
	 * @var \Caffeinated\Menus\Collection
	 */
	protected $collection;

	/**
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param  \Illuminate\Config\Repository     $config
	 */
	public function __construct(Repository $config)
	{
		$this->config     = $config;
		$this->collection = new Collection;
	}

	/**
	 * Create a new menu instance.
	 *
	 * @param  string    $name
	 * @param  callable  $callback
	 * @return \Caffeinated\Menus\Builder
	 */
	public function make($name, $callback)
	{
		if (is_callable($callback)) {
			$menu = new Builder($name, $this->loadConfig($name));

			call_user_func($callback, $menu);

			$this->collection->put($name, $menu);

			view()->share('menu_'.$name, $menu);

			return $menu;
		}
	}

	/**
	 * Loads and merges configuration data.
	 *
	 * @param  string  $name
	 * @return array
	 */
	public function loadConfig($name)
	{
		$options = $this->config->get('menu.settings');
		$name    = strtolower($name);

		if (isset($options[$name]) and is_array($options[$name])) {
			return array_merge($options['default'], $options[$name]);
		}

		return $options['default'];
	}

	/**
	 * Find and return the given menu collection.
	 *
	 * @param  string  $key
	 * @return \Caffeinated\Menus\Collection
	 */
	public function get($key)
	{
		return $this->collection->get($key);
	}

	/**
	 * Returns all menu instances.
	 *
	 * @return \Caffeinated\Menus\Collection
	 */
	public function all()
	{
		return $this->collection;
	}
}
