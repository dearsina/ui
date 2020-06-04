<?php


namespace App\UI\Form;


/**
 * Interface FieldInterface
 * @package App\UI\Form
 */
interface FieldInterface {
	/**
	 *
	 *
	 * @param array $a
	 *
	 * @return string Returns an HTML string.
	 */
	public static function generateHTML(array $a);
}