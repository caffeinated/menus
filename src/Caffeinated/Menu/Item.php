<?php
namespace Caffeinated\Menu;

class Item
{
	/**
	 * @var array
	 */
	public $attributes = array();

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var \Caffeinated\Menu\Menu
	 */
	private $menu;

	/**
	 * @var array
	 */
	public $meta;

	/**
	 * @var int
	 */
	private $pid;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * Constructor.
	 *
	 * @param \Caffeinated\Menu\Menu  $menu
	 * @param string                  $title
	 * @param string                  $url
	 * @param array                   $attributes
	 * @param int                     $pid
	 */
	public function __construct($menu, $title, $url, $attributes = array(), $pid = 0)
	{
		$this->menu       = $menu;
		$this->title      = $title;
		$this->url        = $url;
		$this->attributes = $attributes;
		$this->pid        = $pid;

		$this->id         = $this->id();
		$this->link       = new Link($title, $url);
	}

	/**
	 * Add a subitem to the menu
	 *
	 * @param  string        $title
	 * @param  string|array  $action
	 * @return \Caffeinated\Menu\Menu
	 */
	public function add($title, $action)
	{
		if (! is_array($action)) {
			$url           = $action;
			$action        = array();
			$action['url'] = $url;
		}

		$action['pid'] = $this->id;

		return $this->menu->add($title, $action);
	}

	/**
	 * 
	 */
	protected function id()
	{
		return count($this->menu->menu) + 1;
	}

	/**
	 *
	 */
	public function getPid()
	{
		return $this->pid;
	}

	/**
	 *
	 */
	public function hasChildren()
	{
		return (count($this->menu->whereParent($this->id))) ? true : false;
	}

	/**
	 *
	 */
	public function link()
	{
		return "<a href=\"{$this->link->getUrl()}\"{$this->menu->html->attributes($this->link->attributes)}>{$this->link->getText()}</a>";
	}
}