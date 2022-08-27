<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;

/**
 * Class Dropdown
 *
 * Builds the HTML for dropdown menus, split buttons,
 * and buttons that are dropdowns.
 *
 * Should not be accessed directly, but instead accessed
 * through the Buttons class.
 *
 * @package App\UI
 */
class Dropdown {
	private static function generateChildren(array $item, ?int $level = 0): string
	{
		$icon = Icon::generate($item['icon']);
		$title = $item['title'];

		if($item['children']){
			if($item['children']['items']){
				$meta = $item['children'];
				$item['children'] = $item['children']['items'];
				unset($meta['items']);
			}
			foreach($item['children'] as $child){
				if($child['children']){
					$lis .= "<li>".self::generateChildren($child, $level + 1)."</li>";
					continue;
				}

				# If the child is just a divider
				if($child === true){
					$lis .= "<li class=\"dropdown-divider\"></li>";
					continue;
				}

				# If the child is a header
				if($child['header']){
					$lis .= self::getHeaderHTML($child);
					continue;
				}

				$lis .= "<li>".self::generateChildTag($child)."</li>";
			}
		}

		switch($item['direction']){
		case 'left':
		case 'start':
			$direction = "dropstart";
			break;
		case 'right':
		case 'end':
			$direction = "dropend";
			break;
		case 'up':
			$direction = "dropup";
			break;
		default:
			$direction = $level ? "dropend" : "dropdown";
			break;
		}

		$class = str::getAttrTag("class", [$direction, $item['class']]);
		$style = str::getAttrTag("style", $item['style']);

		$button_class = str::getAttrTag("class", ["dropdown-item dropdown-toggle", $item['button_class']]);
		$ul_class = str::getAttrTag("class", ["dropdown-menu", $item['ul_class']]);

		return <<<EOF
<div{$class}{$style}>
  <button{$button_class} type="button" data-bs-toggle="dropdown" aria-expanded="false">
    {$icon}{$title}
  </button>
  <ul{$ul_class}>{$lis}</ul>
</div>
EOF;
	}
	/**
	 * Generates the root UL element that contains the
	 * dropdown menu.
	 *
	 * @param array|null $items
	 * @param int|null   $level
	 *
	 * @return string|null
	 */
	static function generateRootUl(?array $items, ?int $level = 0): ?string
	{
		if(!$items){
			return NULL;
		}
		
		if($items['items']){
			$meta = $items;
			$items = $items['items'];
			unset($meta['items']);
		}

		foreach($items as $item){
			# If the line item has html instead of children
			if($item['html']){
				$class = str::getAttrTag("class", self::getDirectionClass($item, $level));
				$parent_div = self::generateParentDiv($item);
				$content = self::generateMenuContent($item);
				$html .= "<div{$class}>{$parent_div}{$content}</div>";
				continue;
			}

			# Otherwise, generate the item children
			$html .= self::generateChildren($item);
		}

		return $html;
	}

	/**
	 * If the entire menu has been passed as a single HTML string,
	 * wrap it in a div and return it.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	private static function generateMenuContent(array $item): string
	{
		$class[] = "dropdown-menu";
		$class[] = $item['class'];
		$class = str::getAttrTag("class", $class);
		$style = str::getAttrTag("style", $item['style']);
		$data = str::getDataAttr($item['data']);
		return "<div{$class}{$style}{$data}>{$item['html']}</div>";
	}

	/**
	 * The class of the <ui> tag root.
	 *
	 * The root class can be passed, or it's generated based
	 * on the level.
	 *
	 * @param int               $level
	 * @param array|string|null $root_class
	 *
	 * @return string
	 */
	private static function getRootClass(int $level, $root_class): string
	{
		# Custom class
		if(is_array($root_class)){
			$root_class = implode(" ", str::flatten($root_class));
		}
		if($root_class){
			$class = $root_class;
		}

		# Level 1-n class
		else if($level){
			$class = "dropdown-menu dropdown-submenu shadow";
		}

		# Level 0 class
		else {
			$class = "navbar-nav";
		}

		# Add animation
		$class .= " animate slideIn";

		return $class;
	}

	/**
	 * Unless specified by the direction key, will
	 * send child menu items down for level 0,
	 * then right for all subsequent levels.
	 *
	 * @param array $item
	 * @param int   $level
	 *
	 * @return string
	 */
	private static function getDirectionClass(array $item, int $level): string
	{
		switch($item['direction']) {
		case 'left':
			return "dropstart";
		case 'right':
			return "dropend";
		case 'up':
			return "dropup";
		}

		return $level ? "dropend" : "dropdown";
	}

	/**
	 * Separated out to avoid confusion as some attributes
	 * are shared with the parent <li> tag.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	private static function generateParentDiv(array $item): string
	{
		$data = str::getDataAttr([
			"bs-auto-close" => $item['auto_close']
		]);
		$alt = str::getAttrTag("title", $item['alt'] ?: trim(strip_tags($item['title'])));
		$class = str::getAttrTag("class", "dropdown-item dropdown-toggle");
		$icon = Icon::generate($item['icon']);
		$title = $item['title'];

		# Wrap titles that are longer than 25 chars in a span to be picked up by CSS
		if($item['title'] == strip_tags($item['title']) && strlen($item['title']) > 25){
			$title = "<span>{$title}</span>";
		}

		return "<div data-bs-toggle=\"dropdown\"{$data}{$auto_close}{$class}{$alt}>{$icon}{$title}</div>";
	}

	/**
	 * Separated out to match the parent separation.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	private static function generateChildTag(array $item): string
	{
		# Href (can be onClick)
		$href = href::generate($item);

		# Tag, based on whether there is a href or not
		$tag = $href ? "a" : "div";

		# Icon
		$icon = Icon::generate($item['icon']);

		# Badges
		$badge = Badge::generate($item['badge']);

		# Disabled element
		if($item['disabled']){
			$default_class[] = "disabled";
			$tag = "div";
			$href = false;
		}

		# Approval
		if($approve = str::getApproveAttr($item['approve'], $item['icon'], $item['colour'])){
			$default_class[] = "approve-decision";
		}

		# Alt
		$alt = str::getAttrTag("title", $item['alt'] ?: $item['title']);

		# Class
		$default_class[] = "dropdown-item";
		$class_array = str::getAttrArray($item['class'], $default_class, $item['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $item['style']);

		# Title
		$title = $item['title'];

		return "<{$tag}{$class}{$style}{$href}{$alt}{$approve}>{$icon}{$title}{$badge}</{$tag}>";
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
	private static function getHeaderHTML($button)
	{
		extract($button);

		# The text can be colourised
		$colour = str::getColour($button['colour']);

		if($html){
			//do nothing
		}
		else if($strong){
			$html = "<strong>{$header}</strong>";
		}
		else {
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