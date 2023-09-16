<?php


namespace App\UI;


use App\Common\href;
use App\Common\Img;
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
	 *      "pre" => "Text",
	 *      "post" => "Text",
	 *    "flush" => true,
	 *    "horizontal" => true,
	 *    "orderable" => [
	 *        "rel_table" => "doc_type_col_string",
	 *        "limiting_key" => "doc_type_col_id",
	 *        "limiting_val" => $doc_type_col_id,
	 *    ]
	 * ]);
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 * @throws \Exception
	 */
	public static function generate(?array $a): ?string
	{
		if(!$a){
			return NULL;
		}

		$default_class[] = "list-group";

		if(str::isNumericArray($a)){
			$a = ["items" => $a];
		}

		if($a['pre']){
			$pre = Grid::generate(is_string($a['pre']) ? [$a['pre']] : $a['pre']);
		}
		else if($a['html']){
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
			}
			else {
				$default_class[] = "list-group-horizontal";
			}
		}

		# If there is a cap on the number of items that can be display per line
		if($a['cap'] && count($a['items']) > $a['cap']){
			while(!empty($a['items'])) {
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
		}

		# If there is no cap (most common)
		else if(is_array($a['items'])){
			# For each item
			foreach($a['items'] as $item){

				# Generate the item if it's not in the simple items format
				if(str::isNumericArray($item)){
					// If the item itself is a numeric array (wrapped with [[ instead of [)

					# Assume it's not HTML and needs to be generated
					$item = Grid::generate($item);
				}

				# Then feed it to the list group item.
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
	private static function generateListGroupItem($item, ?array $orderable = []): string
	{
		if(!is_array($item)){
			$item = ["html" => $item];
		}

		$default_class[] = "list-group-item";

		if($href = href::generate($item)){
			$tag = "a";
			$default_class[] = "list-group-item-action";
		}
		else if($orderable){
			$tag = "span style=\"padding: 0.5rem 1rem;display: block;\"";
			//little bit of a fudge in the cases where the item is NOT a link, but needs to be orderable
		}
		else {
			$tag = "li";
		}

		if($item['disabled']){
			$default_class[] = "disabled";
		}

		if($item['active']){
			$default_class[] = "active";
		}

		# ID
		$id = str::getAttrTag("id", $item['id'] ?: str::id("list-group-item"));

		# Colour
		$default_class[] = $item['colour'] ? "list-group-item-{$item['colour']}" : NULL;

		# Accordion

		# Title + Subtitle + Body
		$html .= $item['title'] ? self::generateTitle($item['title']) : NULL;
		$html .= $item['subtitle'] ? self::generateSubtitle($item['subtitle']) : NULL;
		$html .= $item['body'] ? self::generateBody($item['body']) : NULL;

		# HTML (After/instead of title/subtitle/body)
		$html .= $item['html'];

		# Image
		$image = Img::generate($item['img'] ?: $item['image']);

		# Icon
		$icon = Icon::generate($item['icon']);

		# Badge
		$badge = Badge::generate($item['badge'], [
			"style" => [
				"margin-left" => ".5rem",
			],
		]);

		# Left badge (badge on the LEFT side of the item)
		if(is_array($item['left_badge'])){
			$left_badge = Badge::generate($item['left_badge'], [
				"style" => [
					"float" => "left",
				],
			]);
		}
		else if($item['left_badge']){
			$left_badge = Grid::generate([[
				"html" => $item['left_badge'],
				"row_style" => [
					"float" => "left",
				],
			]]);
		}

		# Button(s)
		if($button = Button::generate($item["button"])){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Tooltips
		Tooltip::generate($item);

		# Class
		$class_array = str::getAttrArray($item['class'], $default_class, $item['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $item['style']);

		# Alt
		$title = str::getAttrTag("title", str_replace('"', "''", $item['alt']));
		// We have to convert the " to '' because otherwise it breaks the HTML when wrapped in JSON

		# Draggable
		$draggable = str::getAttrTag("draggable", $item['draggable'] ? "true" : NULL);

		# Data
		$data = str::getDataAttr($item['data']);

		# OnDragStart
		$ondragstart = str::getAttrTag("ondragstart", $item['ondragstart']);

		if($orderable){
			if($item['orderable'] !== false){
				if($item['id']){
					$class_array[] = "draggable";
					$class = str::getAttrTag("class", $class_array);
					$id = str::getAttrTag("id", $item["id"]);
					$handlebars = "<span class=\"list-group-handlebars\"></span>";
				}
			}

			return <<<EOF
<li{$id}{$class}{$style}{$draggable}{$ondragstart}{$data}{$title}>{$handlebars}{$button}<{$tag}{$href}>{$image}{$icon}{$left_badge}{$html}{$badge}</{$tag}></li>
EOF;
		}

		return <<<EOF
<{$tag}{$id}{$class}{$style}{$href}{$draggable}{$ondragstart}{$data}{$title}>{$button}{$image}{$icon}{$left_badge}{$html}{$badge}</{$tag}>
EOF;

	}

	/**
	 * @param string|array|null $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];
		Tooltip::generate($a);
		extract($a);

		$id = str::getAttrTag("id", $id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$parent_class_array = str::getAttrArray($parent_class, "d-flex justify-content-between", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $parent_style);
		$class_array = str::getAttrArray($class, "list-group-item-title", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);
		$parent_alt = str::getAttrTag("title", $parent_alt);
		$alt = str::getAttrTag("title", $alt);
		$data = str::getDataAttr($data);

		if($href = href::generate($a)){
			$href_start = "<a{$href}>";
			$href_end = "</a>";
		}

		return <<<EOF
<div{$parent_class}{$parent_style}{$parent_alt}>
	<div{$id}{$class}{$style}{$alt}{$data}>
		{$href_start}{$icon}{$title}{$href_end}{$badge}{$button}
	</div>
</div>
EOF;

	}

	/**
	 * @param string|array|null $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateSubtitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];
		Tooltip::generate($a);
		extract($a);

		$id = str::getAttrTag("id", $id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$parent_class_array = str::getAttrArray($parent_class, "d-flex justify-content-between", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $parent_style);
		$class_array = str::getAttrArray($class, "list-group-item-subtitle", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);
		$parent_alt = str::getAttrTag("title", $parent_alt);
		$alt = str::getAttrTag("title", $alt);
		$data = str::getDataAttr($data);

		if($href = href::generate($a)){
			$href_start = "<a{$href}>";
			$href_end = "</a>";
		}

		return <<<EOF
<div{$parent_class}{$parent_style}{$parent_alt}>
	<h5{$id}{$class}{$style}{$alt}{$data}>
		{$href_start}{$icon}{$title}{$href_end}{$badge}{$button}
	</h5>
</div>
EOF;
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
		$style_array = str::getAttrArray($style, [
			"letter-spacing" => "-.5px",
			"line-height" => "16pt",
		], $only_style);
		$style = str::getAttrTag("style", $style_array);

		return "<{$tag}{$id}{$class}{$style}>{$body}{$button}</{$tag}>";
	}
}