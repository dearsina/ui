<?php


namespace App\UI\Form;


interface FieldInterface {
	/**
	 * @inheritDoc Generates the HTML for this field type.
	 *
	 * @param array $a
	 *
	 * @return string Returns an HTML string.
	 */
	public static function generateHTML(array $a);
}