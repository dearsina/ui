<?php


namespace App\UI\Card;


use App\UI\Icon;

/**
 * Class Window
 *
 * The window is just a floating card.
 *
 * @package App\UI\Card
 */
class Window extends Card{
	function __construct ($a = NULL) {
		# Style
		$a['style'] = array_merge([
			"top" => "30%",
			"left" => "30vh",

		], $a['style'] ?:[]);

		# Class
		$a['class'] = is_array($a['class']) ? $a['class'] : [$a['class']];
		$a['class'][] = "card-window";

		# Header
		$a['header'] = is_array($a['header']) ? $a['header'] : [
			"html" => $a['header']
		];

		# Window controls
		$a['header']['controls'] = $this->windowControlIcons($a).$a['header']['html'];

		# Default is true, but can be set to false
		$a['resizable'] = key_exists("resizable", $a) ? $a['resizable'] : true;

		parent::__construct($a);

		return true;
	}

	private function windowControlIcons(array $a): string
	{
		extract($a);

		if($maximise !== false){
			$resize = Icon::generate([
				"type" => "light",
				"name" => "window-maximize",
				"alt" => "Maximise",
				"class" => "window-button-resize window-button-maximise"
			]);
		}

		if($close !== false){
			$close = Icon::generate([
				"type" => "light",
				"name" => "times",
				"alt" => "Close window",
				"class" => "window-button-close"
			]);
		}

		return "<div class=\"window-controls\">{$resize}{$close}</div>";
	}
}