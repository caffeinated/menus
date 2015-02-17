<?php
namespace Caffeinated\Menu;

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
	 * Returns all items with no parents.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function roots()
	{
		return $this->whereParent();
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
		return "<ul{$this->attributes($attributes)}>{$this->render('ul')}</ul>";
	}

	/**
	 *
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
	 *
	 */
	public function __call($method, $args)
	{
		preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);

		if ($matches) {
			$attribute = strtolower($matches[1]);
		} else {
			return false;
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
	 *
	 */
	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return $this->whereSlug($property)->first();
	}
}