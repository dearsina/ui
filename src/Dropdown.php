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
			$disabled = str::get_attr_tag("disabled", "disabled");
			$style = str::get_attr_array($style, ["cursor" => "default"]);
		}

		$style_tag = str::get_attr_tag("style", $style);

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
				$html .= self::get_header_html($item);
				continue;
			}

			if($item['children']) {
				//if the item has children
				$item['class'] = str::get_attr_array($item['class'], "parent");
			}

			# Add colour
			if($item['colour']) {
				$colour = str::get_attr_tag("class", "text-".str::translate_colour($item['colour']));
			} else {
				$colour = false;
			}

			if(!$href = href::generate($item)){
				$href = str::get_attr_tag("href", "#");
			}
			$icon = Icon::generate($item['icon']);
			$badge = Badge::generate($item['badge']);

			if($item['disabled']){
				$item['class'] = [$item['class'], "disabled"];
				$href = str::get_attr_tag("href", "#");
			}
			$class = str::get_attr_tag("class", $item['class']);
			$style = str::get_attr_tag("style", $item['style']);

			$html .= "
<li{$class}{$style}>
	<a {$href}{$colour}>{$icon}{$item['title']}{$badge}</a>
	".self::get_multilevel_dropdown_html($item['children'])."
</li>
";
		}

		# ul classes (applicable primarily to the top level)
		$class = str::get_attr_tag("class", $ul['class']);
		$style = str::get_attr_tag("style", $ul['style']);

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
	static function get_header_html($button){
		extract($button);

		# The text can be colourised
		$colour = str::get_colour($button['colour']);

		if($html){
			//do nothing
		} else if($strong){
			$html = "<strong>{$header}</strong>";
		} else {
			$html = $header;
		}

		# Class
		$class_array = str::get_attr_array($class, ["dropdown-header", "text-center", $colour], $only_class);
		$class_tag = str::get_attr_tag("class", $class_array);

		# Style
		$style_tag = str::get_attr_tag("style", $style);

		return "<div{$class_tag}{$style_tag}><span>{$html}</span></div>";
	}

}