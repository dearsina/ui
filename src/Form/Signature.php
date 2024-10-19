<?php


namespace App\UI\Form;


use App\ClientSignature\ClientSignature;
use App\Common\Img;
use App\Common\SQL\Info\Info;
use App\Common\str;
use App\UI\Button;
use App\UI\Grid;

class Signature extends Field implements FieldInterface {

	public static function generateHTML(array $a): string
	{
		extract($a);

		# Set dependency data
		self::setDependencyData($a);

		return Grid::generate([[
			# Enable any dependencies
			"row_data" => $a['data'],

			"row_class" => "col-signature",
			// The class is a way of identifying the _entire_ signature block, ex. for dependencies
			"html" => [[[
				"title" => [
					"style" => [
						# The following two settings are set so that float-right badges can be applied to the field
						"width" => "max-content",
						"line-height" => "unset",
					],
					"title" => $title,
				],
				"body" => $desc,
				"row_style" => [
					"padding-bottom" => "1rem",
				],
			], [
				"html" => self::generateButtonHtml($a),
				"row_style" => [
					"margin-bottom" => ".5rem",
				],
			], [[
				"id" => ClientSignature::getId($a),
				"html" => [
					Form::getFieldsAsHtml(self::generateSignatureFields($a)),
				],
			]],
			]],
		]]);


	}

	/**
	 * Displays the signature for internal signatures captured only.
	 *
	 * @param $a
	 *
	 * @return array|null
	 */
	public static function generateSignatureFields($a): ?array
	{
		extract($a);

		if(!$value){
			return NULL;
		}

		if(str::isUuid($value)){
			$client_signature = Info::getInstance()->getInfo("client_signature", $value);
			if(!$value = $client_signature['signature']){
				return NULL;
			}
		}

		$fields[] = [
			"type" => "html",
			"html" => Img::generate([
				"style" => [
					"max-height" => "100px",
					"margin" => "1rem 0",
					"max-width" => "100%",
				],
				"src" => $value,
			]),
		];

		$fields[] = [
			"type" => "hidden",
			"name" => "signature",
			"value" => $value,
		];

		return $fields;
	}

	/**
	 * We add a hidden field so that we can trigger
	 * requirement validation.
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function generateHiddenField(array $a): string
	{
		# For the field holding the signature value
		$a['style'] = [
			"opacity" => "0",
			"position" => "absolute",
		];
		// Hide the field, but don't make it "hidden"

		$a['label'] = false;
		$a['desc'] = false;

		if($a['validation']['required'] || $a['form_group_form_field']['required']){
			/**
			 * As this method is primarily called from outside the class,
			 * we need to check for the required flag from the form group
			 * form field also.
			 */
			$a['validation']['required'] = [
				"rule" => true,
				"msg" => "A signature is required.",
			];
		}

		return Input::generateHTML($a);
	}

	/**
	 * Generates the button that opens the modal that controls
	 * the signature flow.
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function generateButtonHtml(array $a): string
	{
		extract($a);

		# AES
		if($aes){
			$alt = "Click here to sign securely";
		}

		# SES
		else {
			$alt = "Click here to sign";
		}

		if($client_id){
			# The hash to either pick up an existing client signature record or create a new one
			$hash = [
				"rel_table" => "client_signature",
				"vars" => [
					"client_id" => $client_id,
					"form_group_form_field_id" => $form_group_form_field_id,
				],
			];
		}

		else {
			# If no client ID has been passed, disable the signature button
			$hash = [
				"rel_table" => "client_signature",
				"action" => "new",
				"vars" => [
					"id" => $id,
				],
			];
		}

		return Button::generate([
			"icon" => ClientSignature::getIcon($aes),
			"alt" => $alt,
			"title" => "Click here to sign",
			"colour" => "primary",
			"hash" => $hash,
			"disabled" => $disabled,
		]);
	}

	/**
	 * Used a catch-all method to clean up signature
	 * meta data.
	 *
	 * @param array       $a
	 * @param string|null $key
	 *
	 * @return void
	 */
	public static function formatSignatureVal(array &$a, ?string $key = NULL): void
	{
		extract($a);

		# Break up the signature parts if they have been passed
		$parts = explode(":", $vars['form_group_form_field_id']);
		if(count($parts) == 2){
			$a['vars']['form_group_form_field_id'] = $parts[0];
			$a['vars']['signature_part'] = $parts[1];
		}

		if(!$vars['signature']){
			return;
		}

		if(!$key){
			return;
		}

		$a['vars'][$key] = $vars['signature'];
	}
}