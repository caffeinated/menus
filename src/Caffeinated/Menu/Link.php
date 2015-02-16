<?php
namespace Caffeinated\Menu;

class Link
{
	public $text;

	public $url;

	public $attributes;

	public function __construct($text, $url, $attributes = array())
	{
		$this->text       = $text;
		$this->url        = $url;
		$this->attributes = $attributes;
	}

	/**
	 *
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 *
	 */
	public function getText()
	{
		return $this->text;
	}
}