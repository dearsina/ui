<?php


namespace App\UI;


use App\Common\str;

class Tooltip {
	/**
	 * Will generate a tooltip wrapper around a given piece of
	 * HTML.
	 *
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
	public static function generateHtmlWrapper(array $a): ?string
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

	/**
	 * Make an element tooltip-able.
	 *
	 * Generate a tooltip when hovering over the element.
	 * Tooltips need to be generated prior to extraction of
	 * the array containing the element details, because
	 * it edits the alt, class and data keys.
	 *
	 * It requires a data attribute.
	 *
	 * @param array $a
	 */
	 public static function generate(array &$a): void
	 {
		 if(!$tooltip = $a['tooltip']){
			 return;
		 }

		 /**
		  * If the tooltip is boolean, it will assume the
		  * element alt or title is the tooltip itself.
		  */
		 if(is_bool($tooltip)){
			 $tooltip = [
				 "title" => $a['alt'] ?: $a['title']
			 ];
		 }

		 else if(!is_array($tooltip)){
			 $tooltip = ["title" => $tooltip];
		 }

		 # Ensure the class value is an array
		 if(!is_array($a['class'])){
			 $a['class'] = [$a['class']];
		 }

		 # We need to add a class to the parent
		 $a['class'][] = "tooltip-trigger";

		 # Set the tooltip as a data attribute
		 $a['data']['bs-original-title'] = $tooltip['title'];

		 # Settings
		 $a['data']['bs-toggle'] = "tooltip";
		 $a['data']['bs-html'] = "true";
		 $a['data']['bs-placement'] = $tooltip['placement'] ?: "top";

		 # Tooltips can also have their own custom classes
		 if($tooltip['class']){
			 $a['data']['bs-custom-class'] = $tooltip['class'];
		 }
	 }
}