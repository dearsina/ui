<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;

/**
 * Class Dropdown
 * @package App\UI
 */
class Dropdown {
	/**
	 * Generates a dropdown button,
	 * with a dropdown menu.
	 * Primarily used by cards.
	 *
	 * @param $a
	 *
	 * @return string
	 */
	static function generate($a)
	{
		extract($a);

		if(!$buttons){
			return false;
		}

		$icon = $icon ?: [
			"name" => "bars",
			"type" => "thin",
		];

		$class = str::getAttrArray($class, ["nav-right nav-dropdown"], $only_class);

		$parent = [[
			"icon" => $icon,
			"children" => $buttons,
			"class" => $class,
		]];

		$parent_class = str::getAttrArray($parent_class, ["nav-right"], $only_class);

		$html = self::getMultiLevelItemsHTML($parent, ["class" => $parent_class]);
		return "<nav>$html</nav>";
	}

	static function generateButton(array $a): string
	{
		extract($a);

		if(!$children){
			return false;
		}

		$class = str::getAttrArray($class, ["nav-right nav-dropdown"], $only_class);

		$parent = [[
			"title" => $button,
			"children" => $children,
			"class" => $class,
		]];

		$parent_class = str::getAttrArray($parent_class, ["nav-right"], $only_class);

		$html = self::getMultiLevelItemsHTML($parent, [
			"class" => $parent_class,
			"style" => [
				"padding" => "0",
				"margin" => "0",
			],
		]);

		return "<nav class=\"dropdown-button\">$html</nav>";
	}

	/**
	 * Generates a multilevel menu based on parent-children items.
	 *
	 * @param      $items
	 * @param null $ul
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Exception
	 */
	public static function getMultiLevelItemsHTML($items, $ul = NULL)
	{
		if(!is_array($items)){
			return false;
		}

		foreach($items as $item){
			if(empty($item)){
				continue;
			}

			$default_parent_class = [];
			$default_class = [];

			# If the item is just a divider
			if($item === true){
				$html .= "<li class=\"dropdown-divider\"></li>";
				continue;
			}

			# If the items is a header
			if($item['header']){
				$html .= self::getHeaderHTML($item);
				continue;
			}

			if($item['children'] || $ul){
				//if the item has children, or is a top level item
				$default_parent_class[] = "parent";
			}

			$parent_class_array = str::getAttrArray($item['parent_class'], $default_parent_class, $item['only_parent_class']);
			$parent_class = str::getAttrTag("class", $parent_class_array);
			$parent_style = str::getAttrTag("style", $item['parent_style']);

			if($href = href::generate($item)){
				$tag = "a";
			} else {
				$tag = "div";
			}
			$icon = Icon::generate($item['icon']);
			$badge = Badge::generate($item['badge']);

			# Disabled element?
			if($item['disabled']){
				$default_class[] = "disabled";
				$tag = "div";
				$href = false;
			}

			# Approval needed?
			if($approve_attr = str::getApproveAttr($item['approve'])){
				$default_class[] = "approve-decision";
				$id = str::getAttrTag("id", $item['id'] ?: str::id());
			}

			$class_array = str::getAttrArray($item['class'], $default_class, $item['only_class']);
			$class = str::getAttrTag("class", $class_array);
			$style = str::getAttrTag("style", $item['style']);

			# Hovertext
			$title = str::getAttrTag("title", $item['alt']);

			$children = self::getMultiLevelItemsHTML($item['children']);

			$html .= <<<EOF
<li{$parent_class}{$parent_style}>
	<{$tag}{$id}{$class}{$style}{$title}{$href}{$approve_attr}>{$icon}{$item['title']}{$badge}</{$tag}>
	{$children}
</li>
EOF;
		}

		# ul classes (applicable primarily to the top level)
		$class = str::getAttrTag("class", $ul['class']);
		$style = str::getAttrTag("style", $ul['style']);

		return "<ul{$class}{$style}>{$html}</ul>";

	}

	/**
	 * Formats dropdown headers
	 * <code>
	 * "buttons" => [[
	 *    "header" => "Header",
	 *    "strong" => true,
	 *    "colour" => "red"
	 * ]]
	 * </code>
	 *
	 * @param $button
	 *
	 * @return string
	 */
	static function getHeaderHTML($button)
	{
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

		//		$default_style = [
		//			"text-transform" => "uppercase",
		//			"letter-spacing" => "1.5px",
		//			"font-weight" => "400",
		//			"font-size" => "smaller",
		//		];

		# Style
		$style_array = str::getAttrArray($style, $default_style, $only_style);
		$style_tag = str::getAttrTag("style", $style_array);

		return "<li{$class_tag}{$style_tag}><span>{$html}</span></li>";
	}

}