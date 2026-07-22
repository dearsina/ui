<?php


namespace App\UI;


class Wait {

	public static function getIcon(): ?string
	{
		return Icon::generate([
			"type" => "light",
			"colour" => "primary",
			"name" => "spinner-third",
			"class" => "fa-spin",
			"style" => [
				"margin-bottom" => "1rem",
			],
		]);
	}
	/**
	 * Produces a visual for a modal or card body.
	 * Telling the user that something is happening.
	 *
	 * @param string $narrative
	 *
	 * @return string
	 */
	public static function get(?string $narrative = "Processing file", ?array $style = []): string
	{
		$style = array_merge([
			"min-height" => "20vh",
			"align-items" => "center",
			"text-align" => "center",
		], $style);

		return Grid::generate([[
			"html" => implode("<br>", array_filter([
				self::getIcon(),
				$narrative
			])),
			"row_style" => $style
		]]);
	}

	public static function ellipsis(string $narrative = "Processing file"): string
	{
		return "{$narrative}<span class=\"ellipsis\"></span>";
	}
}