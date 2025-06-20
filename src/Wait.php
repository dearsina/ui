<?php


namespace App\UI;


class Wait {
	/**
	 * Produces a visual for a modal or card body.
	 * Telling the user that something is happening.
	 *
	 * @param string $narrative
	 *
	 * @return string
	 */
	public static function get(?string $narrative = "Processing file"): string
	{

		$icon = Icon::generate([
			"type" => "light",
			"colour" => "primary",
			"name" => "spinner-third",
			"class" => "fa-spin",
			"style" => [
				"margin-bottom" => "1rem",
			],
		]);

		return Grid::generate([[
			"html" => "{$icon}<br>{$narrative}",
			"row_style" => [
				"min-height" => "20vh",
				"align-items" => "center",
				"text-align" => "center",
			],
		]]);
	}

	public static function ellipsis(string $narrative = "Processing file"): string
	{
		return "{$narrative}<span class=\"ellipsis\"></span>";
	}
}