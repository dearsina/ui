<?php


namespace App\UI\Form;


use App\Common\str;
use App\UI\Button;
use App\UI\Card\Card;
use App\UI\Grid;
use App\UI\Icon;

class Signature extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML(array $a)
	{
		$signature_id = str::id("signature");

		$a['data']['signature-id'] = $signature_id;

		if($a['value']){
			$image = "<image height=\"100\" xlink:href=\"{$a['value']}\"></image>";
			$created = "Signed on the ".\DateTime::createFromFormat("Y-m-d H:i:s", $a['created'])->format("jS \of F, Y");
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
		$a['label'] = false;
		$a['desc'] = false;
		$a['style'] = ["display" => "none"];

		return Grid::generate([[
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
	}
}