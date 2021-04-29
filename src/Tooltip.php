<?php


namespace App\UI;


use App\Common\str;

class Tooltip {
	/**
	 * <code>
	 * Tooltip::generate([
	 * 	"title" => $service['desc'], //The tooltip itself
	 * 	"html" => $html, //The HTML that will trigger the tooltip on hover
	 * 	"style" => [
	 * 		"cursor" => "default"
	 * 	]
	 * ]);
	 * </code>
	 * @param array $a
	 *
	 * @return string|null
	 */
	public static function generate(array $a): ?string
	{
		extract($a);

		$title = str::getAttrTag("title", htmlentities($title));

		$data = [
			"bs-placement" => $direction ?: "top",
		];

		$data = str::getDataAttr($data);
		$class = str::getAttrArray($class, "tooltip-trigger", $only_class);
		$class = str::getAttrTag("class", $class);
		$style = str::getAttrTag("style", $style);

		return "<span{$class}{$style}{$data}{$title}>{$html}</span>";
	}
}