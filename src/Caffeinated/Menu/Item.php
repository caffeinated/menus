<?php
namespace Caffeinated\Menu;

class Item
{
	/**
	 *
	 */
	protected $builder;

	public $id;

	public $title;

	public $slug;

	public $divider;

	public $parent;

	protected $data = array();

	public $attributes = array();

	/**
	 *
	 */
	public function __construct($builder, $id, $title, $options)
	{
		$this->builder    = $builder;
		$this->id         = $id;
		$this->title      = $title;
		$this->slug       = camel_case($title);
		$this->attributes = $this->builder->extractAttributes($options);
		$this->parent     = (is_array($options) and isset($options['parent'])) ? $options['parent'] : null;

		$this->configureLink($options);
	}

	/**
	 *
	 */
	public function configureLink($options)
	{
		if (! is_array($options)) {
			$path = ['url' => $options];
		} elseif (isset($options['raw']) and $options['raw'] == true) {
			$path = null;
		} else {
			$path = array_only($options, ['url', 'route', 'action', 'secure']);
		}

		if (! is_null($path)) {
			$path['prefix'] = $this->builder->getLastGroupPrefix();
		}

		$this->link = isset($path) ? new Link($path) : null;

		if ($this->builder->config('auto_active') === true) {
			$this->checkActiveStatus();
		}
	}

	/**
	 *
	 */
	public function add($title, $options = '')
	{
		if (! is_array($options)) {
			$url            = $options;
			$options        = array();
			$options['url'] = $url;
		}

		$options['parent'] = $this->id;

		return $this->builder->add($title, $options);
	}

	/**
	 *
	 */
	public function attributes()
	{
		$args = func_get_args();

		if (isset($args[0]) and is_array($args[0])) {
			$this->attributes = array_merge($this->attributes, $args[0]);

			return $this;
		} elseif (isset($args[0]) and isset($args[1])) {
			$this->attributes[$args[0]] = $args[1];

			return $this;
		} elseif (isset($args[0])) {
			return isset($this->attributes[$args[0]]) ? $this->attributes[$args[0]] : null;
		}

		return $this->attributes;
	}

	/**
	 *
	 */
	public function url()
	{
		if (! is_null($this->link)) {
			if ($this->link->href) {
				return $this->link->href;
			}

			return $this->builder->dispatch($this->link->path);
		}
	}

	/**
	 *
	 */
	public function hasChildren()
	{
		return count($this->builder->whereParent($this->id)) or false;
	}

	public function __get($property)
	{
		if (property_exists($property)) {
			return $this->$property;
		}

		return $this->data($property);
	}
}