<?php


namespace App\UI\Form;

use App\Common\Geolocation\Geolocation;
use App\Common\str;

/**
 * Class Tel
 * @package App\UI\Form
 */
class Tel extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML (array $a) {
		# Generate the ID for the a field up front
		$a['id'] = $a['id'] ?: str::id($a['type']);

		/**
		 * The b field is a hidden field that will carry
		 * the actual telephone number value.
		 *
		 * The reason for that is that the a field may
		 * hold the number without the international prefix,
		 * and with wonky formatting, while the b field
		 * wil hold it in its "pure" form, with the correct
		 * international prefix.
		 */
		$b['id'] = str::id("hidden");
		$b['value'] = $a['value'];

		# Move the name from the a field to the b field
		$b['name'] = $a['name'];
		$a['name'] = false;

		$a['data'] = self::getTelSettings($b['id']);
		return Input::generateHTML($a).Hidden::generateHTML($b);
	}

	/**
	 * Get the user's geolocation,
	 * uses the country as the default country for the
	 * telephone number international prefix.
	 * @param string $value_field_id
	 *
	 * @return array
	 */
	private static function getTelSettings(string $value_field_id): array
	{
		$geolocation = Geolocation::get();
		return [
			"value_field_id" => $value_field_id,
			"settings" => [
				"initialCountry" => $geolocation['country_code'],
				"preferredCountries" => [
					$geolocation['country_code']
				]
			]
		];
	}
}
