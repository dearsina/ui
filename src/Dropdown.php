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
			$html .= self::generateChildren($item, 0, $meta);
		}

		return $html;
	}

	private static function generateChildren(array $item, ?int $level = 0, ?array $meta = NULL): string
	{
		$icon = Icon::generate($item['icon']);
		$title = $item['title'];

		switch($item['direction']) {
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

		if($meta && !$level){
			// If we're at the root, and a meta array has been passed
			$class = str::getAttrTag("class", [$direction, $meta['class']]);
			$style = str::getAttrTag("style", $meta['style']);
		}

		else {
			$class = str::getAttrTag("class", [$direction, $item['class']]);
			$style = str::getAttrTag("style", $item['style']);
		}


		$button_class = str::getAttrTag("class", ["dropdown-item dropdown-toggle", $item['button_class']]);
		$menu = self::generateUl($item);

		return <<<EOF
<div{$class}{$style}>
  <button{$button_class} type="button" data-bs-toggle="dropdown" aria-expanded="false">
    {$icon}{$title}
  </button>
  {$menu}
</div>
EOF;
	}

	public static function generateUl(array $item): string
	{
		if($item['children']){
			$item['children'] = $item['children']['items'] ?: $item['children'];

			$children = array_filter($item['children']);
			//Removes empty (false, null) children

			foreach($children as $child){
				if($child['children']){
					$lis .= "<li>" . self::generateChildren($child, $level + 1) . "</li>";
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
				$lis .= "<li>" . self::generateChildTag($child) . "</li>";
			}
		}

		$ul_class = str::getAttrTag("class", ["dropdown-menu", $item['ul_class']]);

		return "<ul{$ul_class}>{$lis}</ul>";
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
			"bs-auto-close" => $item['auto_close'],
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

		# Data
		$data = str::getDataAttr($item['data']);

		# Title
		$title = $item['title'];

		return "<{$tag}{$class}{$style}{$href}{$alt}{$approve}{$data}>{$icon}{$title}{$badge}</{$tag}>";
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

	/**
	 * If a menu has more than the limit of items,
	 * will break it up and add a sub-child. Will
	 * keep doing it as long as the child has more
	 * than the limit.
	 *
	 * @param array|null $children
	 * @param int|null   $limit
	 *
	 * @return array|null
	 */
	public static function breakUpBigChildren(?array $children, ?int $limit = 10): ?array
	{
		if(!$children){
			return $children;
		}

		if(count($children) <= $limit){
			return $children;
		}

		$chunks = array_chunk($children, $limit);

		return self::joinChildren($chunks);
	}

	/**
	 * Recursive function that joins chunks of children
	 * together.
	 *
	 * @param array       $chunks
	 * @param string|null $title
	 *
	 * @return array
	 */
	private static function joinChildren(array $chunks, ?string $title = "More..."): array
	{
		$chunk = array_shift($chunks);

		if($chunks){
			$chunk[] = [
				"icon" => "chevrons-right",
				"title" => $title,
				"children" => self::joinChildren($chunks)
			];
			return $chunk;
		}

		else {
			return $chunk;
		}
	}
}