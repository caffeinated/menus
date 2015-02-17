<?php
namespace Caffeinated\Menu;

class Link
{
	/**
	 * @var array
	 */
	protected $path = array();

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @var array
	 */
	public $attributes = array();

	/**
	 * Constructor.
	 *
	 * @param array  $path
	 */
	public function __construct($path = array())
	{
		$this->path = $path;
	}

	/**
	 * Set the link's href property.
	 *
	 * @param  string  $href
	 * @return \Caffeinated\Menu\Link
	 */
	public function href($href)
	{
		$this->href = $href;

		return $this;
	}

	/**
	 * Add attributes to the link.
	 *
	 * @param  mixed
	 * @return \Caffeinated\Menu\Link|string
	 */
	public function attr()
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
	 * Dynamically retrieve property value.
	 *
	 * @param  string  $property
	 * @return mixed
	 */
	public function __get($property)
	{
		if (property_exists($this, $property)) {
			return $this->$property;
		}

		return $this->attr($property);
	}
}