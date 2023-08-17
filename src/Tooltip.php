<?php


namespace App\UI;


use App\Common\str;

class Tooltip {
	/**
	 * Will generate a tooltip wrapper around a given piece of
	 * HTML.
	 *
	 * <code>
	 * Tooltip::generateHtmlWrapper([
	 *    "title" => $service['desc'], //The tooltip itself
	 *    "direction" => "top",
	 *      "tag" => "span",
	 *    "html" => $html, //The HTML that will trigger the tooltip on hover
	 *    "style" => [
	 *        "cursor" => "default"
	 *    ]
	 * ]);
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	public static function generateHtmlWrapper(array $a): ?string
	{
		extract($a);

		$title = str::getAttrTag("title", str_replace('"', '\"', $title));

		$data = [
			"bs-placement" => $direction ?: "top",
		];

		$data = str::getDataAttr($data);
		$class = str::getAttrArray($class, "tooltip-trigger", $only_class);

		$class = str::getAttrTag("class", $class);
		$style = str::getAttrTag("style", $style);

		$tag = $tag ?: "span";

		return "<{$tag}{$class}{$style}{$data}{$title}>{$html}</{$tag}>";
	}

	/**
	 * Generate a tooltip attached to an info icon.
	 *
	 * <code>
	 * Tooltip::i("Tooltip");
	 * </code>
	 *
	 * or
	 *
	 * <code>
	 * Tooltip::i([
	 *     "title" => "Tooltip",
	 *     "icon" => [
	 *         "name" => "info-circle",
	 *         "colour" => "blue",
	 *         "size" => "sm",
	 *         "style" => [
	 *             "margin-left" => "0.25rem",
	 *         ],
	 *     ],
	 * ]);
	 * </code>
	 *
	 * @param mixed|NULL $a
	 *
	 * @return string
	 */
	public static function i($a): ?string
	{
		if(!$a){
			return NULL;
		}

		if(!is_array($a)){
			$a = [
				"title" => $a,
			];
		}

		$icon_default = [
			"name" => "info-circle",
			"colour" => "blue",
			"size" => "sm",
			"style" => [
				"cursor" => "help",
				"margin-left" => "0.25rem",
			],
		];

		$a['html'] = Icon::generate(array_merge($icon_default, $a['icon'] ?: []));

		return self::generateHtmlWrapper($a);
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
				"title" => $a['alt'] ?: $a['title'],
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