<?php
namespace Caffeinated\Menu;

use Collective\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Factory;
use Illuminate\Config\Repository;

class Menu
{
	/**
	 * @var array
	 */
	protected $groupStack = array();

	/**
	 * @var \Collective\Html\HtmlBuilder
	 */
	public $html;

	/**
	 * @var array
	 */
	protected $htmlLists = ['ul' => 'li', 'ol' => 'li'];

	/**
	 * @var array
	 */
	public $menu = array();

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $reserved = ['route', 'action', 'url', 'prefix', 'pid'];

	/**
	 * @var \Illuminate\Routing\UrlGenerator
	 */
	protected $url;

	/**
	 * @var \Illuminate\View\Factory
	 */
	protected $view;

	public function __construct(HtmlBuilder $html, UrlGenerator $url, Factory $view, Repository $config)
	{
		$this->html   = $html;
		$this->url    = $url;
		$this->view   = $view;
		$this->config = $config;
	}

	/**
	 * Create a new menu instance.
	 *
	 * @param  string    $name
	 * @param  callable  $callback
	 * @return \Caffeinated\Menu\Menu
	 */
	public function make($name, $callback)
	{
		if (is_callable($callback)) {
			call_user_func($callback, $this);

			$this->name = $name;

			$this->view->composer('*', function($view) {
				$view->with('menu_'.$this->name, $this);
			});

			return $this;
		}
	}

	/**
	 * Adds an item to the menu.
	 *
	 * @param  string                 $title
	 * @param  string|array           $action
	 * @return Caffeinated\Menu\Item  $item
	 */
	public function add($title, $action)
	{
		$url = $this->dispatch($action);

		if (is_array($action)) {
			$attributes = $this->getAttributes($action);
		} else {
			$attributes = $this->getAttributes();
		}

		$pid  = (isset($action['pid'])) ? $action['pid'] : null;
		$item = new Item($this, $title, $url, $attributes, $pid);

		array_push($this->menu, $item);

		return $item;
	}

	/**
	 * Return the valid attributes from the given options.
	 *
	 * @param  array  $options
	 * @return array
	 */
	protected function getAttributes($options = array())
	{
		if (count($this->groupStack) > 0) {
			$options = $this->mergeWithLastGroup($options);
		}

		$attributes = array_except($options, $this->reserved);

		return $attributes;
	}

	/**
	 *
	 */
	protected function mergeGroup($new, $old)
	{
		$new['prefix'] = $this->formatGroupPrefix($new, $old);

		return array_merge_recursive(array_except($old, array('prefix')), $new);
	}

	/**
	 * 
	 */
	protected function mergeWithLastGroup($new)
	{
		return $this->mergeGroup($new, last($this->groupStack));
	}

	/**
	 * 
	 */
	protected function formatGroupPrefix($new, $old)
	{
		if (isset($new['prefix'])) {
			return trim(array_get($old, 'prefix'), '/').'/'.trim($new['prefix'], '/');
		}

		return array_get($old, 'prefix');
	}

	/**
	 *
	 */
	protected function getLastGroupPrefix()
	{
		if (count($this->groupStack) > 0) {
			return array_get(last($this->groupStack), 'prefix', '');
		}

		return '';
	}

	/**
	 * 
	 */
	protected function dispatch($options)
	{
		if (! is_array($options)) {
			return $this->getUrl($options);
		}

		if (isset($options['url'])) {
			return $this->getUrl($options['url']);
		}

		if (isset($options['route'])) {
			return $this->getRoute($options['route']);
		} elseif (isset($options['action'])) {
			return $this->getControllerAction($options['action']);
		}

		return null;
	}

	/**
	 * 
	 */
	protected function getUrl($options)
	{
		if (is_array($options)) {
			return $this->url->to($this->getLastGroupPrefix().'/'.$options[0], array_slice($options, 1));
		}

		return $this->url->to($this->getLastGroupPrefix().'/'.$options);
	}

	/**
	 *
	 */
	public function roots()
	{
		return $this->whereParent();
	}

	/**
	 *
	 */
	public function whereParent($parent = 0)
	{
		return array_filter($this->menu, function($item) use ($parent) {
			if ($item->getPid() == $parent) {
				return true;
			}

			return false;
		});
	}

	/*
	|--------------------------------------------------------------------------
	| Rendering Methods
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 *
	 */
	public function asUl($attributes = array())
	{
		return "<ul{$this->html->attributes($attributes)}>{$this->render('ul')}</ul>";
	}

	/**
	 * 
	 */
	public function asOl($attributes = array())
	{
		return "<ol{$this->html->attributes($attributes)}>{$this->render('ol')}</ol>";
	}

	/**
	 *
	 */
	public function asDiv($attributes = array())
	{
		return "<div{$this->html->attributes($attributes)}>{$this->render('div')}</div>";
	}

	/**
	 *
	 */
	public function asView($view, $name = 'menu')
	{
		return $this->view->make($view, [$name => $this]);
	}

	/**
	 *
	 */
	protected function render($type = 'ul', $pid = null)
	{
		$items   = '';
		$itemTag = (isset($this->htmlLists[$type])) ? $this->htmlLists[$type] : $type;

		foreach ($this->whereParent($pid) as $item) {
			$items .= "<{$itemTag}{$this->html->attributes($item->attributes)}>".$item->link();

			if ($item->hasChildren()) {
				$items .= "<{$type}>";
				$items .= $this->render($type, $item->getId());
				$items .= "</{$type}>";
			}

			$items .= "</{$itemTag}>";
		}

		return $items;
	}
}