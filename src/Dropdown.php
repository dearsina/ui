<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;

class Dropdown {
	/**
	 * Generates a dropdown button,
	 * with a dropdown menu.
	 *
	 * Primerally used by cards.
	 *
	 * @param $buttons
	 *`
	 * @return string
	 */
	static function generate($a){
		extract($a);

		if(!$buttons){
			return false;
		}

		# Is the caret disabled?
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$style = str::getAttrArray($style, ["cursor" => "default"]);
		}

		$style_tag = str::getAttrTag("style", $style);

		# The actual caret (three bars) symbol
		$icon = Icon::generate([
			"name" => "bars",
			"type" => "thin"
		]);

		# The dropdown items
		$items_html .= self::get_multilevel_dropdown_html($buttons, ["class" => "nav-right"]);

		# Caret (three bars) HTML
		$html = <<<EOF
<div
	class="dropdown-caret"
	data-toggle="dropdown"
	role="button"
	aria-haspopup="true"
	aria-expanded="false"
	{$style_tag}
	{$disabled}
>{$icon}</div>
<div class="dropdown-menu dropdown-menu-right">{$items_html}</div>
EOF;
		return $html;
	}

	/**
	 * Generates a multilevel menu based on parent-children items.
	 *
	 * @param array $items (Multilevel) dropdown items
	 * @param array $ul Attributes to give to the base ul tag.
	 *
	 * @return bool|string
	 */
	private static function get_multilevel_dropdown_html($items, $ul = NULL){
		if(!is_array($items)){
			return false;
		}

		foreach($items as $item){
			# If the item is just a divider
			if($item === true){
				$html .= "<div class=\"dropdown-divider\"></div>";
				continue;
			}

			# If the items is a header
			if($item['header']){
				$html .= self::getHeaderHTML($item);
				continue;
			}

			if($item['children']) {
				//if the item has children
				$item['class'] = str::getAttrArray($item['class'], "parent");
			}

			# Add colour
			if($item['colour']) {
				$colour = str::getAttrTag("class", "text-".str::translate_colour($item['colour']));
			} else {
				$colour = false;
			}

			if(!$href = href::generate($item)){
				$href = str::getAttrTag("href", "#");
			}
			$icon = Icon::generate($item['icon']);
			$badge = Badge::generate($item['badge']);

			if($item['disabled']){
				$item['class'] = [$item['class'], "disabled"];
				$href = str::getAttrTag("href", "#");
			}
			$class = str::getAttrTag("class", $item['class']);
			$style = str::getAttrTag("style", $item['style']);

			$html .= "
<li{$class}{$style}>
	<a {$href}{$colour}>{$icon}{$item['title']}{$badge}</a>
	".self::get_multilevel_dropdown_html($item['children'])."
</li>
";
		}

		# ul classes (applicable primarily to the top level)
		$class = str::getAttrTag("class", $ul['class']);
		$style = str::getAttrTag("style", $ul['style']);

		return "<ul{$class}{$style}>{$html}</ul>";

	}

	/**
	 * Formats dropdown headers
	 *
	 * <code>
	 * "buttons" => [[
	 * 	"header" => "Header",
	 * 	"strong" => true,
	 * 	"colour" => "red"
	 * ]]
	 * </code>
	 *
	 * @param $button
	 *
	 * @return string
	 */
	static function getHeaderHTML($button){
		extract($button);

		# The text can be colourised
		$colour = str::getColour($button['colour']);

		if($html){
			//do nothing
		} else if($strong){
			$html = "<strong>{$header}</strong>";
		} else {
			$html = $header;
		}

		# Class
		$class_array = str::getAttrArray($class, ["dropdown-header", "text-center", $colour], $only_class);
		$class_tag = str::getAttrTag("class", $class_array);

		# Style
		$style_tag = str::getAttrTag("style", $style);

		return "<div{$class_tag}{$style_tag}><span>{$html}</span></div>";
	}

}