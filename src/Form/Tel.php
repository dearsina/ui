<?php


namespace App\UI\Form;

use App\Common\Geolocation\Geolocation;
use App\Common\str;
use App\FormField\FieldBuilder;
use App\Translation\Translator;

/**
 * Class Tel
 * @package App\UI\Form
 */
class Tel extends Field implements FieldInterface {
	/**
	 * Default telephone error messages.
	 */
	public const TEL_ERROR_MESSAGES = [
		"DEFAULT" => [
			"title" => "Default error message",
			"message" => "The phone number entered doesn't seem to be valid."
		],
		"INVALID_COUNTRY_CODE" => [
			"title" => "Invalid country code",
			"message" => "The country code is invalid."
		],
		"TOO_SHORT" => [
			"title" => "Number is too short",
			"message" => "The phone number is too short."
		],
		"TOO_LONG" => [
			"title" => "Number is too long",
			"message" => "The phone number is too long."
		],
	];

	/**
	 * @inheritDoc
	 */
	public static function generateHTML (array $a) {
		# Generate the ID for the a field up front
		$a['id'] = $a['id'] ?: str::id($a['type']);

		/**
		 * The b-field is a hidden field that will carry
		 * the actual telephone number value.
		 *
		 * The reason for that is that the a-field may
		 * hold the number without the international prefix,
		 * and with wonky formatting, while the b-field
		 * wil hold it in its "pure" form, with the correct
		 * international prefix.
		 */
		$b['id'] = str::id("hidden");
		$b['value'] = $a['value'];

		# Move the name from the a-field to the b-field
		$b['name'] = $a['name'];
		$a['name'] = false;

		$b['data'] = $a['data'];
		self::setTelSettings($a, $b['id']);

		return Input::generateHTML($a).Hidden::generateHTML($b);
	}

	/**
	 * Get the user's geolocation,
	 * uses the country as the default country for the
	 * telephone number international prefix.
	 *
	 * @param array  $a
	 * @param string $value_field_id
	 *
	 * @return array
	 */
	private static function setTelSettings(array &$a, string $value_field_id): void
	{
		$geolocation = Geolocation::get();

		# Backfill default error messages
		foreach(Tel::TEL_ERROR_MESSAGES as $key => $error_message){
			# But only if there isn't a custom message there already
			if($a['data']['tel_error_messages'][$key]){
				continue;
			}

			# Translate the default error if required
			Translator::set($error_message, [
				"rel_table" => "error",
				"to_language_id" => $a['language_id']
			]);

			# Add it to the array
			$a['data']['tel_error_messages'][$key] = $error_message['message'];
		}

		$a['data'] = [
			"value_field_id" => $value_field_id,
			"settings" => [
				"onlyCountries" => $a['data']['only_countries'],
				"excludeCountries" => $a['data']['except_countries'],
				"initialCountry" => $a['data']['initial_country_code'] ?: $geolocation['country_code'],
				"preferredCountries" => [
					$geolocation['country_code']
				]
			],
			"tel_error_messages" => $a['data']['tel_error_messages'],
		];
	}
}
