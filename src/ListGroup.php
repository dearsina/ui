<?php


namespace App\UI;


use App\Common\href;
use App\Common\str;

/**
 * Class ListGroup
 * @package App\UI
 */
class ListGroup {
	/**
	 * Given an array of items,
	 * and metadata, produce a list-group.
	 * <code>
	 * $items = [[
	 *    "html" => "Complex",
	 *    "colour" => "info",
	 *    "badge" => "999",
	 *    "hash" => "rel_table",
	 *    "active" => true,
	 *    "disabled" => true,
	 * ],
	 *    "Simple"
	 * ];
	 *
	 * # Simple
	 * ListGroup::generate($items);
	 *
	 * # Complex
	 * ListGroup::generate([
	 *    "items" => $items,
	 *    "flush" => true,
	 *    "horizontal" => true
	 * ]);
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 * @throws \Exception
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

		# Title + Body
		if($item['title'] || $item['body']){
			$html = self::generateTitle($item['title']);
			$html .= self::generateBody($item['body']);
		}

		# HTML (After/instead of title/body)
		$html .= $item['html'];

		# Icon
		$icon = Icon::generate($item['icon']);

		# Badge
		if($badge = Badge::generate($item['badge'])){
//			$default_class[] = "d-flex justify-content-between align-items-center";
		}

		# Button(s)
		if($button = Button::generate($item["button"])){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Class
		$class_array = str::getAttrArray($item['class'], $default_class, $item['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $item['style']);

		return <<<EOF
<{$tag}{$id}{$class}{$style}{$href}>{$icon}{$html}{$badge}{$button}</{$tag}>
EOF;

	}

	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];
		extract($a);

		if($href = href::generate($a)){
			$tag = "a";
		}
		$tag = $tag ?: "h5";
		$id = str::getAttrTag("id", $id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$parent_class_array = str::getAttrArray($parent_class, "d-flex w-100 justify-content-between", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $parent_style);
		$class_array = str::getAttrArray($class, "mb-1", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);

		return "<div{$parent_class}{$parent_style}><{$tag}{$id}{$class}{$style}{$href}>{$icon}{$title}{$badge}{$button}</{$tag}></div>";
	}

	private static function generateBody($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["body" => $a];
		extract($a);

		$tag = $tag ?: "p";
		$id = str::getAttrTag("id", $id);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "mb-1", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);

		return "<{$tag}{$id}{$class}{$style}>{$body}{$button}</{$tag}>";
	}
}