<?php

namespace App\UI\Form;

use App\Common\str;

class Colour extends Field implements FieldInterface {

	/**
	 * The step colours available.
	 */
	public const COLOURS = [
		"plum",
		"red",

		"rust",
		"orange",
		"salmon",

		"amber",
		"yellow",
		"lemon",

		"meadow",
		"green",
		"pine",

		"navy",
		"blue",
		"sky",

		"grey",
		"black",
	];

	public static function getColourOptions(): array
	{
		$colour_options = [];
		foreach(Colour::COLOURS as $c){
			$colour_options[$c] = [
				"icon" => [
					"colour" => $c,
					"name" => "square",
					"type" => "thick",
				],
				"title" => str::title($c),
			];
		}
		return $colour_options;
	}

	/**
	 *
	 *
	 * @param array $a
	 *
	 * @return string Returns an HTML string.
	 */
	public static function generateHTML(array $a)
	{
		$a['type'] = "input";
		$a['class'] = is_array($a['class']) ? array_merge($a['class'],["coloris"]) : $a['class']." coloris";

		return Input::generateHTML($a);
	}
}