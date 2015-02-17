<?php
namespace Caffeinated\Menu;

use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
	/**
	 *
	 */
	public function attr()
	{
		$args = func_get_args();

		$this->each(function($item) use ($args) {
			if (count($args) >= 2) {
				$item->attr($args[0], $args[1]);
			} else {
				$item->attr($args[0]);
			}
		});

		return $this;
	}

	/**
	 *
	 */
	public function data()
	{
		// 
	}

	/**
	 *
	 */
	public function append($html)
	{
		// 
	}

	/**
	 *
	 */
	public function prepend($html)
	{
		// 
	}
}