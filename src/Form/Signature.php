<?php


namespace App\UI\Form;


use App\ClientSignature\ClientSignature;
use App\Common\Img;
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
				"body" => str::newline($desc),
				"row_style" => [
					"padding-bottom" => "1rem",
				],
			], [
				"html" => self::generateButtonHtml($a),
				"row_style" => [
					"margin-bottom" => ".5rem",
				],
			], [
				# Enable any dependencies
				"data" => $a['data'],
			], [[
				"id" => ClientSignature::getId($a),
				"html" => [
					ClientSignature::getSignatureFields($a),
					(new Form())->getFieldsHTML(self::generateSignatureFields($a)),
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

	public static function formatSignatureVal(array &$a, string $key): void
	{
		extract($a);

		if(!$vars['signature']){
			return;
		}

		$a['vars'][$key] = $vars['signature'];
	}

	/**
	 * @inheritDoc
	 */
	/*public static function generateHTMLOld(array $a)
	{
		$signature_id = str::id("signature");

		$a['data']['signature-id'] = $signature_id;

		if($a['value']){
			$image = "<image height=\"100\" xlink:href=\"{$a['value']}\"></image>";
			if($a['created']){
				$created = "Signed on the ".\DateTime::createFromFormat("Y-m-d H:i:s", $a['created'])->format("jS \of F, Y");
			}
			$basic = true;
		} else {
			$style = str::getAttrTag("style", ["display" => "none"]);
		}

		$class = str::getAttrTag("class", ["signature-pad-svg", $signature_id]);

		$svg = <<<EOF
<svg
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	{$class}{$style}>{$image}
</svg>
<div class="signature-pad-desc {$signature_id}">{$created}</div>
EOF;
		# For the signature
		$label = str::newline($a['desc']);

		# For the field holding the signature value
		$a['style'] = [
			"opacity" => "0",
			"position" => "absolute"
		];
		// Hide the field, but don't make it "hidden"

		$a['label'] = false;
		$a['desc'] = false;
//		$a['style'] = ["display" => "none"];

		return Grid::generate([[
			"row_class" => "col-signature",
			// The class is a way of identifying the _entire_ signature block, ex. for dependencies
			"html" => [[[
				"title" => [
					"style" => [
						# The following two settings are set so that float-right badges can be applied to the field
						"width" => "max-content",
						"line-height" => "unset"
					],
					"title" => $a['title'],
				],
				"body" => $label,
				"row_style" => [
					"padding-bottom" => "1rem"
				]
			],[
				"html" => $svg
			],[
				"html" => Button::generate([
					"icon" => "signature",
					"title" => "Click here to sign",
					"colour" => "primary",
					"basic" => $basic,
					"hash" => [
						"rel_table" => "signature",
						"action" => "new",
						"vars" => [
							"signature_id" => $signature_id
						]
					]
				]),
				"row_style" => [
					"margin-bottom" => "1rem"
				]
			],[
				"html" => Input::generateHTML($a)
			]]]
		]]);
	}*/
}