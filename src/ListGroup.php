<?php


namespace App\UI;


use App\Common\href;
use App\Common\str;

class ListGroup {
	/**
	 * Given an array of items,
	 * and metadata, produce a list-group.
	 * <code>
	 * $items = [[
	 * 	"html" => "Complex",
	 * 	"colour" => "info",
	 * 	"badge" => "999",
	 * 	"hash" => "rel_table"
	 * 	"active" => true,
	 * 	"disabled" => true,
	 * 	"hash" => "rel_table"
	 * ],
	 * 	"Simple"
	 * ];
	 *
	 * # Simple
	 * ListGroup::generate($items);
	 *
	 * # Complex
	 * ListGroup::generate([
	 * 	"item" => $items,
	 * 	"flush" => true,
	 * 	"horizontal" => true
	 * ]);
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generate(array $a) : string
	{
		$default_class[] = "list-group";

		if(str::isNumericArray($a)){
			$a = ["items" => $a];
		}

		if($a['flush']){
			$default_class[] = "list-group-flush";
		}

		if($a['horizontal']){
			if(is_string($a['horizontal'])){
				$default_class[] = "list-group-horizontal-{$a['horizontal']}";
			} else {
				$default_class[] = "list-group-horizontal";
			}
		}

		foreach($a['items'] as $item){
			$html .= self::generateListGroupItem($item);
		}

		# ID
		$id = str::getAttrTag("id", $id ?: str::id("list-group"));

		# Class
		$class_array = str::getAttrArray($a['class'], $default_class, $a['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $a['style']);

		return "<ul{$id}{$class}{$style}>{$html}</ul>";
	}

	/**
	 * Generates a single list-group item.
	 *
	 * @param $item
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function generateListGroupItem($item) : string
	{
		if(!is_array($item)){
			$item = ["html" => $item];
		}

		$default_class[] = "list-group-item";

		if($href = href::generate($item)){
			$tag = "a";
			$default_class[] = "list-group-item-action";
		} else {
			$tag = "li";
		}

		if($item['disabled']){
			$default_class[] = "disabled";
		}

		if($item['active']){
			$default_class[] = "active";
		}

		# ID
		$id = str::getAttrTag("id", $id ?: str::id("list-group-item"));

		# Colour
		$default_class[] = str::getColour($item['colour'], "list-group-item");

		# HTML (could also be referred to as the title)
		$html = $item['html'] ?: $item['title'];

		# Icon
		$icon = Icon::generate($item['icon']);

		# Badge
		if($badge = Badge::generate($item['badge'])){
			$default_class[] = "d-flex justify-content-between align-items-center";
		}

		# Class
		$class_array = str::getAttrArray($item['class'], $default_class, $item['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $item['style']);

		return <<<EOF
<{$tag}{$id}{$class}{$style}{$href}>{$icon}{$html}{$badge}</{$tag}>
EOF;

	}
}