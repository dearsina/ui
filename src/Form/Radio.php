<?php


namespace App\UI\Form;


class Radio extends Field implements FieldInterface {

	/**
	 * Radio fields are exactly like checkboxes.
	 */
	public static function generateHTML (array $a) {
		return Checkbox::generateHTML($a);
	}
}