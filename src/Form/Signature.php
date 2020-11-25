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


		return Grid::generate([[
			"html" => [[[
				"html" => str::newline($a['desc']),
				"row_style" => [
					"padding-bottom" => "1rem"
				]
			],[
				"html" => $svg
			],[
				"html" => Hidden::generateHTML($a)
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
			]]]
		]]);
	}
}