<?php
namespace Caffeinated\Menus;

use BadMethodCallException;
use Collective\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;

class Builder
{
	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var \Collective\Html\HtmlBuilder
	 */
	protected $html;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $groupStack = array();

	/**
	 * @var array
	 */
	protected $reserved = ['route', 'action', 'url', 'prefix', 'parent', 'secure', 'raw'];

	/**
	 * @var int
	 */
	protected $lastId;

	/**
	 * @var \Illuminate\Routing\UrlGenerator
	 */
	protected $url;

	/**
	 * Constructor.
	 *
	 * @param string                            $name
	 * @param array                             $config
	 * @param \Collective\Html\HtmlBuilder      $html
	 * @param \Illuminate\Routing\UrlGenerator  $url
	 */
	public function __construct($name, $config, HtmlBuilder $html, UrlGenerator $url)
	{
		$this->name   = $name;
		$this->config = $config;
		$this->html   = $html;
		$this->url    = $url;
		$this->items  = new Collection;
	}

	/**
	 * Add an item to the defined menu.
	 *
	 * @param  string  $title
	 * @param  array   $options
	 * @return \Caffeinated\Menu\Item
	 */
	public function add($title, $options = '')
	{
		$item = new Item($this, $this->id(), $title, $options);

		$this->items->push($item);

		$this->lastId = $item->id;

		return $item;
	}

	/**
	 * Generate a unique ID for every item added to the menu.
	 *
	 * @return int
	 */
	protected function id()
	{
		return $this->lastId + 1;
	}

	/**
	 * Extract the valid attributes from the passed options.
	 *
	 * @param  array  $options
	 * @return array
	 */
	public function extractAttributes($options = array())
	{
		if (is_array($options)) {
			if (count($this->groupStack) > 0) {
				$options = $this->mergeWithLastGroup($options);
			}

			return array_except($options, $this->reserved);
		}

		return array();
	}

	/**
	 * Converts the defined attributes into HTML.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public function attributes($attributes = array())
	{
		return $this->html->attributes($attributes);
	}

	/**
	 * Return the configuration value by key.
	 *
	 * @param  string  $key
	 * @return string
	 */
	public function config($key)
	{
		return $this->config[$key];
	}

	/**
	 * Returns all items with no parents.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function roots()
	{
		return $this->whereParent();
	}

	/**
	 * Get the prefix from the last group of the stack.
	 *
	 * @return string
	 */
	public function getLastGroupPrefix()
	{
		if (count($this->groupStack) > 0) {
			return array_get(last($this->groupStack), 'prefix', '');
		}

		return null;
	}

	/**
	 * Sorts the menu based on user's callable.
	 *
	 * @param  string|callable  $sortBy
	 * @param  string           $sortType
	 * @return Caffeinated\Menus\Builder
	 */
	public function sortBy($sortBy, $sortType = 'asc')
	{
		if (is_callable($sortBy)) {
			$result = call_user_func($sortBy, $this->items->toArray());

			if (! is_array($result)) {
				$result = array($result);
			}

			$this->items = new Collection($result);
		}

		$this->items->sort(function ($itemA, $itemB) use ($sortBy, $sortType) {
			$itemA = $itemA->$sortBy;
			$itemB = $itemB->$sortBy;

			if ($itemA == $itemB) {
				return 0;
			}

			if ($sortType == 'asc') {
				return $itemA > $itemB ? 1 : -1;
			}

			return $itemA < $itemB ? 1 : -1;
		});

		return $this;
	}

	/*
	|--------------------------------------------------------------------------
	| Dispatch Methods
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Get the action type from the options.
	 *
	 * @param  array  $options
	 * @return string
	 */
	public function dispatch($options)
	{
		if (isset($options['url'])) {
			return $this->getUrl($options);
		} elseif (isset($options['route'])) {
			return $this->getRoute($options['route']);
		} elseif (isset($options['action'])) {
			return $this->getcontrollerAction($options['action']);
		}

		return null;
	}

	/**
	 * Get the action for a "url" option.
	 *
	 * @param  array|string  $options
	 * @return string
	 */
	protected function getUrl($options)
	{
		foreach ($options as $key => $value) {
			$$key = $value;
		}

		$secure = (isset($options['secure']) and $options['secure'] === true) ? true : false;

		if (is_array($url)) {
			if (self::isAbs($url[0])) {
				return $url[0];
			}

			return $this->url->to($prefix.'/'.$url[0], array_slice($url, 1), $secure);
		}

		if (self::isAbs($url)) {
			return $url;
		}

		return $this->url->to($prefix.'/'.$url, array(), $secure);
	}

	/**
	 * Determines if the given URL is absolute.
	 *
	 * @param  string  $url
	 * @return bool
	 */
	public static function isAbs($url)
	{
		return parse_url($url, PHP_URL_SCHEME) or false;
	}

	/*
	|--------------------------------------------------------------------------
	| Rendering Methods
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Renders the menu as an unordered list.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public function asUl($attributes = array())
	{
		return "<ul{$this->attributes($attributes)}>{$this->render('ul')}</ul>";
	}

	/**
	 * Generate the menu items as list items, recursively.
	 *
	 * @param  string  $type
	 * @param  int     $parent
	 * @return string
	 */
	protected function render($type = 'ul', $parent = null)
	{
		$items   = '';
		$itemTag = in_array($type, ['ul', 'ol']) ? 'li' : $type;

		foreach ($this->whereParent($parent) as $item) {
			$items .= "<{$itemTag}{$this->attributes($item->attributes())}>";

			if ($item->link) {
				$items .= "<a{$this->attributes($item->link->attr())} href=\"{$item->url()}\">{$item->title}</a>";
			} else {
				$items .= $item->title;
			}

			if ($item->hasChildren()) {
				$items .= "<{$type}>";
				$items .= $this->render($type, $item->id);
				$items .= "</{$type}>";
			}

			$items .= "</{$itemTag}>";

			if ($item->divider) {
				$items .= "<{$item_tag}{$this->attributes($item->divider)}></{$item_tag}>";
			}
		}

		return $items;
	}

	/**
	 * Dynamic search method against a menu attribute.
	 *
	 * @param  string  $method
	 * @param  array   $args
	 * @return \Caffeinated\Menu\Item|bool
	 */
	public function __call($method, $args)
	{
		preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);

		if ($matches) {
			$attribute = strtolower($matches[1]);
		} else {
			throw new BadMethodCallException('Call to undefined method '.$method);
		}

		$value     = $args ? $args[0] : null;
		$recursive = isset($args[1]) ? $args[1] : false;

		if ($recursive) {
			return $this->filterRecursive($attribute, $value);
		}

		return $this->items->filter(function($item) use ($attribute, $value) {
			if (! property_exists($item, $attribute)) {
				return false;
			}

			if ($item->$attribute == $value) {
				return true;
			}

			return false;
		})->values();
	}

	/**
	 * Returns menu item by name.
	 *
	 * @param  string  $property
	 * @return \Caffeinated\Menu\Item
	 */
	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return $this->whereSlug($property)->first();
	}
}
