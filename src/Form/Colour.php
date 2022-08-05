<?php

namespace App\UI\Form;

class Colour extends Field implements FieldInterface {

	/**
	 *
	 *
	 * @param array $a
	 *
	 * @return string Returns an HTML string.
	 */
	public static function generateHTML(array $a)
	{
		$a['type'] = "input";
		$a['class'] = is_array($a['class']) ? array_merge($a['class'],["coloris"]) : $a['class']." coloris";

		return Input::generateHTML($a);
	}
}