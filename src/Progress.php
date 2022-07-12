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
	 * 	"colour" => "primary",
	 * 	"seconds" => 10,
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

		$id = $id ?: str::id("progress");

		$style_array = str::getAttrArray($style, ["height" => $height ?: self::HEIGHT], $only_style);
		$style = str::getAttrTag("style", $style_array);

		if(!$width){
			$width = self::WIDTH;
		}

		# Colour, or default colour
		$colour = $colour ?: self::COLOUR;

		if($label !== false){
			$label = $label ?: $width;
		}

		if($seconds){
			$transition = <<<EOF
<script>
setTimeout(function(){
	$("#{$id} > .progress-bar").css({"width":"100%"});
}, 1000);
</script>
EOF;
			$width = "0%";
		}

		return /** @lang HTML */<<<EOF
<div id="{$id}" class="progress"{$style}>
	<div class="bar progress-bar progress-bar-striped progress-bar-animated bg-{$colour}" style="width: {$width}; transition-duration:{$seconds}s;">{$label}</div>
	{$transition}
</div>
EOF;
	}
}