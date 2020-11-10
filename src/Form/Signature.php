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

		$svg = <<<EOF
<svg
	class="signature-pad-svg {$signature_id}"
	xmlns="http://www.w3.org/2000/svg"
	xmlns:xlink="http://www.w3.org/1999/xlink"
	style="display: none;">
</svg>
<div class="signature-pad-desc {$signature_id}"></div>
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
					"hash" => [
						"rel_table" => "signature",
						"action" => "new",
						"vars" => [
							"signature_id" => $signature_id
						]
					]
				])
			]]]
		]]);
	}
}