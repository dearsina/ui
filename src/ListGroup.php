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
	 * 	  "pre" => "Text",
	 * 	  "post" => "Text",
	 *    "flush" => true,
	 *    "horizontal" => true,
	 *    "orderable" => [
	 * 		"" => "",
	 *     ]
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

		if($a['pre']){
			$pre = Grid::generate(is_string($a['pre']) ? [$a['pre']] : $a['pre']);
		} else if($a['html']){
			$pre = Grid::generate(is_string($a['html']) ? [$a['html']] : $a['html']);
		}

		if($a['post']){
			$post = Grid::generate(is_string($a['post']) ? [$a['post']] : $a['post']);
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

		if($a['cap'] && count($a['items']) > $a['cap']){
			while(!empty($a['items'])){
				$col = "";
				for($i = 0; $i < $a['cap']; $i++){
					if(empty($a['items'])){
						break;
					}
					$item = array_shift($a['items']);
					if(str::isNumericArray($item)){
						$item = Grid::generate($item);
					}
					$col .= self::generateListGroupItem($item);
				}
				$cols[] = $col;
			}
			$html .= Grid::generate([$cols]);
		} else {
			foreach($a['items'] as $item){
				if(str::isNumericArray($item)){
					$item = Grid::generate($item);
				}
				$html .= self::generateListGroupItem($item, $a['orderable']);
			}
		}

		if($a['orderable']){
			$default_class[] = "list-group-orderable";
			$data = str::getDataAttr($a['orderable']);
		}

		# ID
		$id = str::getAttrTag("id", $id ?: str::id("list-group"));

		# Class
		$class_array = str::getAttrArray($a['class'], $default_class, $a['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $a['style']);

		return "{$pre}<ul{$id}{$class}{$style}{$data}>{$html}</ul>{$post}";
	}

	/**
	 * Generates a single list-group item.
	 *
	 * @param $item
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function generateListGroupItem($item, ?array $orderable = []) : string
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
//		$default_class[] = str::getColour($item['colour'], "list-group-item");
		$default_class[] = $item['colour'] ? "list-group-item-{$item['colour']}" : NULL;

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

		if($orderable){
			if($item['orderable'] !== false){
				if(!$item['id']){
					throw new \Exception("Each item in an orderable array must have an ID.");
				}

				$class_array[] = "draggable list-group-handlebars";
				$class = str::getAttrTag("class", $class_array);

				$id = str::getAttrTag("id", $item["id"]);
			}

			return <<<EOF
<li{$id}{$class}{$style}><{$tag}{$href}>{$icon}{$html}{$badge}{$button}</{$tag}></li>
EOF;
		}

		return <<<EOF
<{$tag}{$id}{$class}{$style}{$href}>{$icon}{$button}{$html}{$badge}</{$tag}>
EOF;

	}

	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];
		extract($a);

		$id = str::getAttrTag("id", $id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
//		$parent_class_array = str::getAttrArray($parent_class, "d-flex w-100 justify-content-between", $only_parent_class);
		//Not sure if removing the w-100 is going to work
		$parent_class_array = str::getAttrArray($parent_class, "d-flex justify-content-between", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $parent_style);
		$class_array = str::getAttrArray($class, "mb-1", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);


		if($href = href::generate($a)){
			return "<div{$parent_class}{$parent_style}><h5{$id}{$class}{$style}><a{$href}>{$icon}{$title}</a>{$badge}{$button}</h5></div>";
		} else {
			$tag = "h5";
		}

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

		# Class
		$class_array = str::getAttrArray($class, "mb-1", $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style_array = str::getAttrArray($style, ["letter-spacing" => "-.5px"], $only_style);
		$style = str::getAttrTag("style", $style_array);

		return "<{$tag}{$id}{$class}{$style}>{$body}{$button}</{$tag}>";
	}
}