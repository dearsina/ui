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
		$options = \App\Common\Country\Country::getAllCountries(true, $a['language_id']);

		# Include only specific countries
		if($a['data']['only_countries']){
			foreach($options as $country_code => $country_name){
				if(!in_array($country_code, $a['data']['only_countries'])){
					unset($options[$country_code]);
				}
			}
		}

		# Exclude specific countries
		if($a['data']['except_countries']){
			foreach($options as $country_code => $country_name){
				if(in_array($country_code, $a['data']['except_countries'])){
					unset($options[$country_code]);
				}
			}
		}

		$a = array_merge($a, [
			"type" => "select",
			"options" => $options,
			"autocomplete" => "country",
		]);

		return Select::generateHTML($a);
	}
}