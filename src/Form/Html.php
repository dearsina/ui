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
		# This will just confuse the Grid class.
		unset($a['type']);
		unset($a['id']);

		return Grid::generate([[
			"html" => $a
		]]);
	}
}