<?php


namespace App\UI\Navigation;


class Factory {
	/**
	 * Generate a particular type of navigation and footer
	 *
	 * @param string     $type
	 * @param array|null $levels
	 * @param array|null $footer
	 *
	 * @return Horizontal
	 */
	public static function generate(string $type, ?array $levels, ?array $footers){
		switch($type){
		case 'horizontal':
		default: return new Horizontal($levels, $footers);
		}
	}
}