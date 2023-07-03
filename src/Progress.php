<?php


namespace App\UI;

use App\Common\Output;
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
	 *    "height" => "px",
	 *    "width" => "%",
	 *    "colour" => "primary",
	 *    "seconds" => 10,
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 *
	 * @return string
	 */
	static function generate($a = NULL)
	{
		if($a == false || $a == NULL){
			return false;
		}

		if(is_array($a)){
			extract($a);
		}
		else if($a){
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

		return /** @lang HTML */ <<<EOF
<div id="{$id}" class="progress"{$style}>
	<div class="bar progress-bar progress-bar-striped progress-bar-animated bg-{$colour}" style="width: {$width}; transition-duration:{$seconds}s;">{$label}</div>
	{$transition}
</div>
EOF;
	}

	static function updatePre(string $id, string $html): void
	{
		Output::getInstance()->update("#{$id} > .progress-bar-pre", $html);
	}

	static function updatePost(string $id, string $html): void
	{
		Output::getInstance()->update("#{$id} > .progress-bar-post", $html);
	}

	static function updateProgressBar(string $id, int $completed_count, ?string $session_id = NULL): void
	{
		Output::getInstance()->function("updateProgressBar", [
			"id" => $id,
			"completed_count" => $completed_count,
		], [
			"session_id" => $session_id,
		]);
	}

	/**
	 * A more complete way of building out a progress bar.
	 *
	 * @param string            $id
	 * @param array|null        $a
	 * @param int|null          $total_count
	 * @param string|array|null $pre
	 * @param string|array|null $post
	 *
	 * @return string
	 */
	static function build(string $id, ?int $total_count = NULL, ?array $kill = NULL, $pre = NULL, $post = NULL, ?array $a = NULL): string
	{
		if(is_array($a)){
			extract($a);
		}

		$style_array = str::getAttrArray($style, NULL, $only_style);
		$class_array = str::getAttrArray($class, ["progress-bar bg-primary progress-bar-striped progress-bar-animated"], $only_class);

		$style = str::getAttrTag("style", $style_array);
		$class = str::getAttrTag("class", $class_array);

		$data['total-count'] = $total_count;
		$data['current-count'] = 0;
		$data['start-time'] = time();

		$script = str::getScriptTag($script);
		$data = str::getDataAttr($data);

		$pre = self::buildPrePost("progress-bar-pre", $pre);
		$post = self::buildPrePost("progress-bar-post", $post);

		if($kill){
			$button = Button::generate([
				"class" => "progress-bar-cancel",
				"size" => "xs",
				"basic" => true,
				"colour" => "danger",
				"icon" => "times",
				"onClick" => "ajaxCall(".json_encode($kill).");",
				"style" => [
					"float" => "right",
					"margin-left" => "1rem",
					"margin-top" => "-3px",
				],
				"alt" => "Cancel",
				"approve" => true
			]);
		}


		return /** @lang HTML */ <<<EOF
<div id="{$id}"{$data}>
	{$pre}{$button}
	<div class="progress">
	  <div{$class}{$style} role="progressbar" style="width: 0%"></div>{$script}
	</div>
	{$post}
</div>
EOF;
	}

	/**
	 * @param string $default_style
	 * @param        $a
	 *
	 * @return string
	 */
	private static function buildPrePost(string $default_style, $a = NULL): string
	{
		if(is_string($a)){
			$html = $a;
		}
		else if(is_array($a)){
			extract($a);
		}

		$style_array = str::getAttrArray($style, NULL, $only_style);
		$class_array = str::getAttrArray($class, [$default_style], $only_class);

		$style = str::getAttrTag("style", $style_array);
		$class = str::getAttrTag("class", $class_array);

		$script = str::getScriptTag($script);
		$data = str::getDataAttr($data);

		return /** @lang HTML */ <<<EOF
<div{$class}{$style}{$data}>{$html}</div>{$script}
EOF;
	}
}