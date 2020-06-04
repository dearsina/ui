<?php


namespace App\UI;


/**
 * Class SVG
 * @package App\UI
 */
class SVG {
	/**
	 * Generate an SVG object container.
	 * Mainly used for icons.
	 *
	 * @param null $a
	 * @param null $style
	 *
	 * @return bool|string
	 */
	static function generate($a = NULL, $style = NULL){
		if(!$a){
			return false;
		}

		if(!is_array($a)){
			//if the only thing passed is the name
			$svg_array['name'] = $a;
		} else {
			$svg_array = $a;
		}

		return /** @lang HTML */<<<EOF
<object
	data="{$svg_array['name']}"
	type="image/svg+xml"
	style="{$style}"
></object>
EOF;
	}
}