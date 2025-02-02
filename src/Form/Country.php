<?php


namespace App\UI\Form;

use App\Common\SQL\Info\Info;

/**
 * Class Country
 * Country is a dropdown listing all countries in the world
 * generated from the country table.
 *
 * @package App\UI\Form
 */
class Country extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML(array $a)
	{
		$a = array_merge($a, [
			"type" => "select",
			"options" => \App\Common\Country\Country::getAllCountries(true, $a['language_id']),
			"autocomplete" => "country",
		]);

		return Select::generateHTML($a);
	}
}