<?php


namespace App\UI\Navigation;


class Factory {
	public static function generate($type, $levels){
		switch($type){
		case 'horizontal':
		default: return new Horizontal($levels);
		}
	}
}