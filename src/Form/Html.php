<?php


namespace App\UI\Form;


use App\UI\Grid;

class Html extends Field implements FieldInterface {

	/**
	 * If a full on HTML grid needs to be passed as part of the form.
	 * Make sure type = html. Everything else is as normal.
	 *
	 */
	public static function generateHTML(array $a)
	{
		$row_id = $a['id'];

		# This will just confuse the Grid class.
		unset($a['type']);
		unset($a['id']);

		# Set dependency data
		self::setDependencyData($a);

		return Grid::generate([[
			"row_id" => $row_id,
			"row_class" => $a['row_class'],
			"html" => $a
		]]);
	}
}