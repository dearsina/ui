<?php


namespace App\UI;


use App\Common\str;

/**
 * Class SVG
 *
 * Used by page and button.
 *
 * Should eventually be depreciated and replaced with Img::
 *
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

		# Create an SVG array
		$svg_array = is_array($a) ? $a : ["name" => $a];

		# Get the style
		$style = str::getAttrTag("style", $style);

		# Set the type
		$type = str::getAttrTag("type", "image/svg+xml");

		return "<object{$type}{$style} data=\"{$svg_array['name']}\"></object>";
	}
}