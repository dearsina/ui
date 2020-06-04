<?php


namespace App\UI;

use App\Common\str;

/**
 * Class Progress
 * @package App\UI
 */
class Progress {
	/**
	 * Default
	 */
	const HEIGHT = "1rem";
	const COLOUR = "primary";
	const WIDTH = "10%";

	/**
	 * Generate a progress bar.
	 *
	 * <code>
	 * progress::generate([
	 * 	"height" => "px",
	 * 	"width" => "%",
	 * 	"colour" = > "primary"
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 *
	 * @return string
	 */
	static function generate($a = NULL){
		if($a == false || $a == null){
			return false;
		}

		if(is_array($a)){
			extract($a);
		} else if($a){
			$width = $a;
		}

		if(!$height){
			$height = self::HEIGHT;
		}

		if(!$width){
			$width = self::WIDTH;
		}

		if($colour){
			$colour = str::translate_colour($colour);
		} else {
			$colour = self::COLOUR;
		}

		if($label !== false){
			$label = $label ?: $width;
		}

		return /** @lang HTML */<<<EOF
<div id="bar" class="progress" style="height: {$height};">
	<div class="bar progress-bar progress-bar-striped bg-{$colour}" style="width: {$width};">{$label}</div>
</div>
EOF;
	}
}