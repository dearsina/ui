<?php


namespace App\UI\Form;


/**
 * Class Radio
 * @package App\UI\Form
 */
class Radio extends Field implements FieldInterface {

	/**
	 * Radio fields are exactly like checkboxes.
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generateHTML (array $a) {
		return Checkbox::generateHTML($a);
	}
}