<?php


namespace App\UI\Form;


use App\Common\str;

class Recaptcha extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML (array $a) {
		if(!$_ENV['recaptcha_key'] || !$_ENV['recaptcha_secret']){
			//if no recaptcha_key has been assigned, ignore this field type
			return false;
		}

		# Id of the hidden field
		$id = str::id("recaptcha_response");

		$hiddenInput = Hidden::generateHTML([
			"id" => $id,
			"name" => "recaptcha_response"
		]);

		if(!$a['action']){
			throw new \Exception("Recaptha field is missing the action variable.");
		}

		return <<<EOF
{$hiddenInput}
<script>
$.getScript( "https://www.google.com/recaptcha/api.js?render={$_ENV['recaptcha_key']}" )
.done(function( script, textStatus ) {
    const r = grecaptcha;
	r.ready(function() {
		r.execute('{$_ENV['recaptcha_key']}', {
		    action: '{$a['action']}'		
		}).then(function(token){
			$("#{$id}").val(token);
			delete r;
		});
	});
})
.fail(function( jqxhr, settings, exception ) {
	// If the reCAPTCHA script fails to load
});
</script>
EOF;
	}

	/**
	 * Privacy policy to place in the card post
	 * whenver the reCAPTCHA field is used.
	 *
	 * @return string
	 */
	public static function getPrivacyPolicy(){
		return <<<EOF
This site is protected by reCAPTCHA and the Google
    <a href="https://policies.google.com/privacy">Privacy Policy</a> and
    <a href="https://policies.google.com/terms">Terms of Service</a> apply.
EOF;
	}
}