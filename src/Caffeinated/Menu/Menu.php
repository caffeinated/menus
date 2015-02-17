<?php
namespace Caffeinated\Menu;

use Collective\Html\HtmlBuilder;
use Illuminate\Config\Repository;
use Illuminate\View\Factory;

class Menu
{
	/**
	 *
	 */
	protected $collection;

	/**
	 *
	 */
	protected $config;

	/**
	 *
	 */
	protected $html;

	/**
	 *
	 */
	protected $view;

	/**
	 * Constructor.
	 */
	public function __construct(Repository $config, Factory $view, HtmlBuilder $html)
	{
		$this->config     = $config;
		$this->view       = $view;
		$this->html       = $html;
		$this->collection = new Collection;
	}

	/**
	 *
	 */
	public function make($name, $callback)
	{
		if (is_callable($callback)) {
			$menu = new Builder($name, $this->loadConfig($name), $this->html);

			call_user_func($callback, $menu);

			$this->collection->put($name, $menu);

			$this->view->share('menu_'.$name, $menu);

			return $menu;
		}
	}

	/**
	 *
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
	 * 
	 */
	public function get($key)
	{
		return $this->collection->get($key);
	}

	/**
	 *
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 *
	 */
	public function all()
	{
		dd($this->collection);

		return $this->collection;
	}
}