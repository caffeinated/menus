<?php
namespace Caffeinated\Menus;

use Illuminate\Support\Facades\Request;

class Item
{
	/**
	 * @var \Caffeinated\Menus\Builder
	 */
	protected $builder;

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var array
	 */
	public $divider = array();

	/**
	 * @var int
	 */
	public $parent;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var array
	 */
	public $attributes = array();

	/**
	 * Constructor.
	 *
	 * @param  \Caffeinated\Menus\Builder  $builder
	 * @param  int                        $id
	 * @param  string                     $title
	 * @param  array|string               $options
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

	public function builder()
	{
		return $this->builder;
	}

	/**
	 * Configures the link for the menu item.
	 *
	 * @param  array|string  $options
	 * @return null
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

		$this->checkActiveStatus();
	}

	/**
	 * Adds a sub item to the menu.
	 *
	 * @param  string        $title
	 * @param  array|string  $options
	 * @return \Caffeinated\Menus\Item
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
	 * Fetch the formatted attributes for the item in HTML.
	 *
	 * @return string
	 */
	public function attributes()
	{
		return $this->builder->attributes($this->attributes);
	}

	/**
	 * Get all attributes.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Assign or fetch the desired attribute.
	 *
	 * @param  array|string  $attribute
	 * @param  string        $value
	 * @return mixed
	 */
	public function attribute($attribute, $value = null)
	{
		if (isset($attribute) and is_array($attribute)) {
			if (array_key_exists('class', $attribute)) {
				$this->attributes['class'] = $this->builder->formatGroupClass(['class' => $attribute['class']], $this->attributes);
				unset ($attribute['class']);
			}

			$this->attributes = array_merge($this->attributes, $attribute);

			return $this;
		} elseif (isset($attribute) and isset($value)) {
			if ($attribute == 'class') {
				$this->attributes['class'] = $this->builder->formatGroupClass(['class' => $value], $this->attributes);
			} else {
				$this->attributes[$attribute] = $value;
			}

			return $this;
		}

		return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : null;
	}

	/**
	 * Generates a valid URL for the menu item.
	 *
	 * @return string
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
	 * Prepends HTML to the item.
	 *
	 * @param  string $html
	 * @return \Caffeinated\Menus\Item
	 */
	public function prepend($html)
	{
		$this->title = $html.' '.$this->title;

		return $this;
	}

	/**
	 * Appends HTML to the item.
	 *
	 * @param  string $html
	 * @return \Caffeinated\Menus\Item
	 */
	public function append($html)
	{
		$this->title = $this->title.' '.$html;

		return $this;
	}

	/**
	 * Appends the specified icon to the item.
	 *
	 * @param  string  $icon
	 * @param  string  $type  Can be either "fontawesome" or "glyphicon"
	 * @return \Caffeinated\Menus\Item
	 */
	public function icon($icon, $type = 'fontawesome')
	{
		switch ($type) {
			case 'fontawesome':
				$html = '<i class="fa fa-'.$icon.' fa-fw"></i>';
				break;

			case 'glyphicon':
				$html = '<span class="glyphicon glyphicon-'.$icon.'" aria-hidden="true"></span>';
				break;

			default:
				$html = '<i class="'.$icon.'"></i>';
				break;
		}

		return $this->data('icon', $html);
	}

	/**
	 * Return the title with the icon prepended automatically.
	 *
	 * @return string
	 */
	public function prependIcon()
	{
		return $this->prepend($this->data('icon'));
	}

	/**
	 * Return the title with the icon appended automatically.
	 *
	 * @return string
	 */
	public function appendIcon()
	{
		return $this->append($this->data('icon'));
	}

	/**
	 * Insert a divider after the item.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function divide($attributes = array())
	{
		$attributes['class'] = $this->builder->formatGroupClass($attributes, ['class' => 'divider']);

		$this->divider = $attributes;

		return $this;
	}

	/**
	 * Determines if the menu item has children.
	 *
	 * @return bool
	 */
	public function hasChildren()
	{
		return count($this->builder->whereParent($this->id)) or false;
	}

	/**
	 * Returns all children underneath the menu item.
	 *
	 * @return \Caffeinated\Menus\Collection
	 */
	public function children()
	{
		return $this->builder->whereParent($this->id);
	}

	/**
	 * Set or get an item's metadata.
	 *
	 * @param  mixed
	 * @return string|\Caffeinated\Menus\Item
	 */
	public function data()
	{
		$args = func_get_args();

		if (isset($args[0]) and is_array($args[0])) {
			$this->data = array_merge($this->data, array_change_key_case($args[0]));

			return $this;
		} elseif (isset($args[0]) and isset($args[1])) {
			$this->data[strtolower($args[0])] = $args[1];

			return $this;
		} elseif (isset($args[0])) {
			return isset($this->data[$args[0]]) ? $this->data[$args[0]] : null;
		}

		return $this->data;
	}

	/**
	 * Decide if the item should be active.
	 *
	 * @return null
	 */
	public function checkActiveStatus()
	{
		$path        = ltrim(parse_url($this->url(), PHP_URL_PATH), '/');
		$requestPath = Request::path();

		if ($this->builder->config['rest_base']) {
			$base = (is_array($this->builder->config['rest_base'])) ? implode('|', $this->builder->config['rest_base']) : $this->builder->conf['rest_base'];

			list($path, $requestPath) = preg_replace('@^('.$base.')/@', '', [$path, $requestPath], 1);
		}

		if ($this->url() == Request::url()) {
			$this->activate();
		}
	}

	public function activate(Item $item = null)
	{
		$item = (is_null($item)) ? $this : $item;

		$item->active();

		$item->data('active', true);

		if ($item->parent) {
            $parent = $this->builder->whereId($item->parent)->first();
            $parent->attributes['class'] = $parent->builder->formatGroupClass(['class' => 'opened'], $parent->attributes);
			$this->activate($parent);
		}
	}

	public function active($pattern = null)
	{
		if (! is_null($pattern)) {
			$pattern = ltrim(preg_replace('/\/\*/', '(/.*)?', $pattern), '/');

			if (preg_match("@^{$pattern}\z@", Request::path())) {
				$this->activate();
			}

			return $this;
		}

		$this->attributes['class'] = $this->builder->formatGroupClass(['class' => 'active'], $this->attributes);

		return $this;
	}

	/**
	 * Return either a property or attribute item value.
	 *
	 * @param  string  $property
	 * @return string
	 */
	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return $this->data($property);
	}
}
