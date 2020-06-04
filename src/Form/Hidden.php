<?php


namespace App\UI\Form;


use App\Common\str;

/**
 * Class Hidden
 * @package App\UI\Form
 */
class Hidden extends Field implements FieldInterface {

	/**
	 * A hidden field is treated like a normal field,
	 * in that it gets a place in the grid,
	 * which may result in empty columns if it's not
	 * placed on a row by itself.
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generateHTML (array $a) {
		extract($a);
		$id = str::getAttrTag("id", $id);
		$type = str::getAttrTag("type", "hidden");
		$name = str::getAttrTag("name", $name);
		$value = str::getAttrTag("value", $value);
		return "<input{$id}{$type}{$name}{$value}>";
	}
}