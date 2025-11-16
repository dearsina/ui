<?php

namespace App\UI;

use App\Common\href;
use App\Common\Img;
use App\Common\SQL\Factory;
use App\Common\str;

/**
 * Class icon
 * @package App\common
 */
class Icon {
	/**
	 * Default icons for common form field names, input type, and notifications.
	 * Can be overridden or removed if icon is specifically set or set to FALSE.
	 */
	const DEFAULTS = [
		"phone" => [
			"name" => "phone",
			"type" => "light",
		],
		"email" => [
			"name" => "envelope",
			"type" => "light",
		],
		"password" => [
			"name" => "key",
			"type" => "light",
		],
		"money" => [
			"name" => "money-bill",
			"type" => "light",
		],
		"date" => [
			"name" => "calendar",
			"type" => "light",
		],
		"error" => [
			"type" => "solid",
			"name" => "ban",
		],
		"danger" => [
			"type" => "solid",
			"name" => "ban",
		],
		"success" => [
			"type" => "solid",
			"name" => "check",
		],
		"info" => [
			"type" => "light",
			"name" => "info-circle",
		],
		"warning" => [
			"type" => "solid",
			"name" => "exclamation-triangle",
		],
	];

	/**
	 * Returns the correct name for the Font Awesome icon weights.
	 *
	 * @param $type string regular, solid or light, everything else defaults to regular.
	 *
	 * @return string far, fas, or fal
	 */
	static function getType(?string $type = NULL): string
	{
		switch($type) {
		case 'regular':
		case 'solid':
		case 'light':
		case 'duotone':
		case 'thin':
		case 'brands':
			return "fa-{$type}";

		case 'brand':
			return "fa-brands";

		case 'bold':
		case 'thick':
		case 'full':
			return "fa-solid";

		case 'flag':
			return 'flag';

		default:
			# The default if type isn't specified is LIGHT
			return "fa-light";
		}
	}

	/**
	 * Translates the $icon setting to an
	 * icon array. Useful to run before generating
	 * an icon HTML string, because you don't know
	 * whether a simple icon name, or a complex set
	 * of icon instructions have been sent thru.
	 *
	 * @param $icon array|string
	 *
	 * @return array
	 */
	static function getArray($icon)
	{
		# If complex icon instructions have been given
		if(is_array($icon)){
			$icon['type'] = self::getType($icon['type']);
			return $icon;
		}

		# If a default name has been given
		if(key_exists($icon, self::DEFAULTS)){
			$icon_array = self::DEFAULTS[$icon];
			$icon_array['type'] = self::getType($icon_array['type']);
			return $icon_array;
		}

		# If simply an icon name has been given
		return [
			"type" => self::getType(),
			"name" => $icon,
			"colour" => false,
		];
	}

	/**
	 * Gets the actual name of the icon class.
	 *
	 * <code>
	 * $icon_class = Icon::getClass($icon);
	 * </code>
	 *
	 * @param $icon_array
	 *
	 * @return bool|string
	 */
	static function getClass($icon_array)
	{
		extract(Icon::getArray($icon_array));

		if(!$name){
			return false;
		}

		$name = strtolower($name);
		//Names are case-sensitive, they're all lowercase

		if($size){
			$size = " fa-{$size}";
		}

		if($class){
			$class = " {$class}";
		}

		if($type == "flag"){
			//if this is an flag-icon-css type icon
			$squared = $squared ? " flag-icon-squared" : "";
			return "flag-icon flag-icon-{$name}{$class}{$squared}";
		}

		return "{$type} fa-{$name} fa-fw{$size}{$class}";
	}

	/**
	 * Icons can be stacked.
	 * Experimental.
	 *
	 * @param $stacked
	 *
	 * @return string
	 */
	private static function generate_stacked($stacked)
	{
		$highest_stack = 1;
		foreach($stacked as $stack){
			$icon_array = [];
			if(is_string($stack)){
				$icon_array['name'] = $stack;
			}
			else {
				$icon_array = $stack;
			}
			$icon_array['stack'] = $icon_array['stack'] ?: $highest_stack++;
			$icon_array['class'] .= "fa-stack-{$icon_array['stack']}x";
			$icon_stack[] = self::generate($icon_array);
			$highest_stack = $icon_array['stack'] > $highest_stack ? $icon_array['stack'] : $highest_stack;
		}
		$class = str::getAttrTag("class", "fa-stack");
		$style = str::getAttrTag("style", "margin:-3px 0px -8px -6px;");
		return "<span {$class} {$style}>" . implode("\r\n", $icon_stack) . "</span>";
	}

	/**
	 * Returns the actual i-tag HTMl that is the icon..
	 *  Requires either an array of icon information,
	 *  or a string with the icon name.
	 *
	 *  <code>
	 *  $icon_html = Icon::generate([
	 *     "name" => "copy",
	 *     "type" => "light",
	 *     "transform" => "left-8"
	 *  ]);
	 *
	 *  $icon_html = Icon::generate("copy");
	 *  </code>
	 *
	 * @param $a array|string|null The icon information could be as little as a string name, or a full array of icon data,
	 *                             or an array of icon data arrays to stack.
	 *
	 * @return string|null
	 */
	static function generate($a = NULL): ?string
	{
		if(!$a){
			return NULL;
		}
		if(!is_array($a)){
			//if the only thing passed is the name of the icon
			$icon_array['name'] = $a;
		}

		else if(str::isNumericArray($a)){
			//if several icons are to be stacked
			return self::generate_stacked($a);
		}

		else {
			$icon_array = $a;
		}

		# SVG icons need to be treated like images
		if($icon_array['svg']){
			# A custom default style must be applied
			$default_style_array = [
				"width" => "1.25em",
				"height" => "1.25em",
				"margin-right" => "0.5rem",
				"margin-bottom" => "-4px",
			];

			$icon_array['style'] = is_array($icon_array['style']) ? $icon_array['style'] : [$icon_array['style']];

			# The style can be overridden
			$icon_array['style'] = array_merge($default_style_array, $icon_array['style']);

			# Add a few more keys
			foreach(["tooltip", "alt"] as $key){
				$icon_array[$key] = $a[$key];
			}

			# Then generate the image
			return Img::generate($icon_array);
		}

		if($icon_array['src']){
			# A custom default style must be applied
			$default_style_array = [
				"width" => "1.25em",
				"margin-right" => "0.25rem",
				"height" => "auto"
			];

			$icon_array['style'] = is_array($icon_array['style']) ? $icon_array['style'] : [$icon_array['style']];

			# The style can be overridden
			$icon_array['style'] = array_merge($default_style_array, $icon_array['style']);

			# Add a few more keys
			foreach(["tooltip", "alt"] as $key){
				$icon_array[$key] = $a[$key];
			}

			# Then generate the image
			return Img::generate($icon_array);
		}

		# Tooltips
		Tooltip::generate($icon_array);

		extract(Icon::getArray($icon_array));

		if(!$default[] = Icon::getClass($icon_array)){
			return NULL;
		}

		if($colour){
			$default[] = " text-{$colour}";
		}

		if($approve){
			//if an approval dialogue is to prepend the action
			$id = $id ?: str::id("icon");
			// We'll need an ID

			$approve_attr = str::getApproveAttr($a['approve'], $icon_array, $colour);
			$default[] = "approve-decision";
		}

		$class_array = str::getAttrArray($class, $default, $only_class);

		$title = str::getAttrTag("title", $alt ?: $title);

		if($href = href::generate($icon_array)){
			$a_pre = "<a {$href}>";
			$a_post = "</a>";
		}

		$id = str::getAttrTag("id", $id);
		$style = str::getAttrTag("style", $style);
		$transform = str::getAttrTag("data-fa-transform", $transform);

		if($rotate){
			$class_array[] = "fa-rotate-{$rotate}";
		}

		$class = str::getAttrTag("class", $class_array);
		$data = str::getDataAttr($data);

		return "{$a_pre}<i{$id}{$class}{$style}{$transform}{$title}{$approve_attr}{$data} aria-hidden=\"true\"></i>{$a_post}";
	}

	/**
	 * Get an icon name from the database table icon.
	 * Will only call the icon table ONCE per request.
	 *
	 * @param string $rel_table
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function get(?string $rel_table): ?string
	{
		if(!$rel_table){
			return NULL;
		}

		global $icon;
		if(!is_array($icon)){
			$sql = Factory::getInstance();
			if($icons = $sql->select(["table" => "icon"])){
				foreach($icons as $row){
					$icon[$row['rel_table']] = $row;
				}
			}
		}
		return $icon[$rel_table]['icon'] ?: $rel_table;
	}
}