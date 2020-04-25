<?php


namespace App\UI\Form;

use App\Common\str;
use App\Common\User\User;

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

		$a['script'] = self::getScript($a, $b);
		return Input::generateHTML($a).Hidden::generateHTML($b);
	}

	/**
	 * Get the user's geolocation,
	 * uses the country as the default country for the
	 * telephone number international prefix.
	 *
	 * @param array $a The field used for the UI
	 * @param array $b The hidden field used to keep the cleaned up international number
	 *
	 * @return string
	 */
	private static function getScript(array $a, array $b){
		$user = new User();
		$geolocation = $user->getGeolocation();
		$preferredCountries = array_values(array_unique(["us", "gb", "us", $geolocation['country_code']]));
		sort($preferredCountries);
		$preferredCountries_json = json_encode($preferredCountries);
		return /** @lang JavaScript */<<<EOF
$("#{$a['id']}").intlTelInput({
	initialCountry: "{$geolocation['country_code']}",
	preferredCountries: {$preferredCountries_json},
	utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.0/js/utils.js"
});
var handleChange = function() {
    if($("#{$a['id']}").intlTelInput("isValidNumber")){
        //if the number conforms to the country format
        $("#{$b['id']}").val($("#{$a['id']}").intlTelInput("getNumber"));
        //Add it to the field that will carry the value
    } else {
        //If the number is incorrect
        $("#{$b['id']}").val("");
        //Remove it from the field that will cary the value
    }
};

// listen to "keyup", but also "change" to update when the user selects a country
$("#{$a['id']}").on('change', handleChange);
$("#{$a['id']}").on('keyup', handleChange);
EOF;

	}
}
