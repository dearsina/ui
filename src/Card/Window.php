<?php


namespace App\UI\Card;


use App\UI\Icon;

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

		# Window icons
		$a['header']['html'] = $this->windowControlIcons().$a['header']['html'];

		# Draggable/resizeable
//		$a['draggable'] = $a['draggable'] ?: true;
		$a['resizable'] = $a['resizable'] ?: true;

		parent::__construct($a);

		return true;
	}

	private function windowControlIcons(): string
	{
		$resize = Icon::generate([
			"type" => "light",
			"name" => "window-maximize",
			"alt" => "Maximise",
			"class" => "window-button-resize window-button-maximise"
		]);

		$close = Icon::generate([
			"type" => "light",
			"name" => "times",
			"alt" => "Close window",
			"class" => "window-button-close"
		]);

		return "<div class=\"window-controls\">{$resize}{$close}</div>";
	}
}