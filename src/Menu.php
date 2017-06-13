<?php
namespace Caffeinated\Menus;

use Collective\Html\HtmlBuilder;
use Illuminate\Config\Repository;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Factory;

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
	 * @var \Collective\Html\HtmlBuilder
	 */
	protected $html;

	/**
	 * @var \Illuminate\Routing\UrlGenerator
	 */
	protected $url;

	/**
	 * @var \Illuminate\View\Factory
	 */
	protected $view;

	/**
	 * Constructor.
	 *
	 * @param  \Illuminate\Config\Repository     $config
	 * @param  \Illuminate\View\Factory          $view
	 * @param  \Collective\Html\HtmlBuilder      $html
	 * @param  \Illuminate\Routing\UrlGenerator  $url
	 */
	public function __construct(Repository $config, Factory $view, HtmlBuilder $html, UrlGenerator $url)
	{
		$this->config     = $config;
		$this->view       = $view;
		$this->html       = $html;
		$this->url        = $url;
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
			$menu = new Builder($name, $this->loadConfig($name), $this->html, $this->url);

			call_user_func($callback, $menu);

			$this->collection->put($name, $menu);

			$this->view->share(config('menu.prepend','menu_').$name, $menu);

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
