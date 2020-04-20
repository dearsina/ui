<?php


namespace App\UI\Form;


use App\Common\str;

class Hidden implements FieldInterface {

	/**
	 * A hidden field is treated like a normal field,
	 * in that it gets a place in the grid,
	 * which may result in empty columns if it's not
	 * placed on a row by itself.
	 */
	public static function generateHTML (array $a) {
		extract($a);
		$id = str::getAttrTag("id", $id);
		$type = str::getAttrTag("type", $type);
		$name = str::getAttrTag("name", $name);
		$value = str::getAttrTag("value", $value);
		return "<input{$id}{$type}{$name}{$value}>";
	}
}