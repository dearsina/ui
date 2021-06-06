<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;

/**
 * Static class to generate buttons
 * <code>
 * Button::generate($a);
 * </code>
 * @package App\Common
 */
class Button {

	/**
	 * Generic buttons that can be referenced by name only.
	 *
	 */
	const COMMON = [
		"save" => [
			"colour" => "green",
			"icon" => [
				"name" => "save",
				"type" => "light",
			],
			"title" => "Save",
			"type" => "submit",
		],

		"cancel" => [
			"onClick" => "window.history.back();",
			"title" => "Cancel",
			"colour" => "grey",
			"basic" => true,
		],

		"cancel_md" => [
			"title" => "Cancel",
			"colour" => "grey",
			"basic" => true,
			"data" => [
				"bs-dismiss" => "modal",
			],
			"class" => "float-right",
		],

		"close_md" => [
			"title" => "Close",
			"colour" => "grey",
			"basic" => true,
			"data" => [
				"bs-dismiss" => "modal",
			],
			"class" => "float-right",
		],

		"close_wd" => [
			"size" => "s",
			"title" => "Close",
			"colour" => "grey",
			"basic" => true,
			"class" => "window-button-close",
		],

		"return" => [
			"onClick" => "window.history.back();",
			"icon" => "chevron-left",
			"title" => "Return",
			"class" => "reset",
			"basic" => true,
		],
	];

	/**
	 * Generic, commonly used buttons.
	 * Used by the Button::generic() method.
	 *
	 */
	const GENERIC = [
		"new" => [
			"size" => "s",
			"hash" => [
				"rel_table" => "rel_table",
				"action" => "new",
				"vars" => "vars",
			],
			"icon" => "new",
			"colour" => "primary",
		],

		"edit" => [
			"size" => "s",
			"hash" => [
				"rel_table" => "rel_table",
				"rel_id" => "rel_id",
				"action" => "edit",
				"vars" => "vars",
			],
			"icon" => "edit",
			"basic" => true,
		],

		"duplicate" => [
			"size" => "s",
			"colour" => "primary",
			"hash" => [
				"rel_table" => "rel_table",
				"rel_id" => "rel_id",
				"action" => "duplicate",
				"vars" => "vars",
			],
			"icon" => "copy",
			"basic" => true,
		],

		"remove" => [
			"size" => "s",
			"hash" => [
				"rel_table" => "rel_table",
				"rel_id" => "rel_id",
				"action" => "remove",
				"vars" => "vars",
			],
			"approve" => [
				"icon" => "trash",
				"colour" => "red",
				"title" => "Remove rel_table?",
				"message" => "Are you sure you want to remove this rel_table?",
			],
			"icon" => "trash",
			"basic" => true,
			"colour" => "danger",
		],
	];

	/**
	 * Localises an array by replacing rel_table/id, action and vars value with their supplied values.
	 *
	 * @param array       $button
	 * @param string|null $rel_table
	 * @param string|null $rel_id
	 * @param string|null $action
	 * @param array|null  $vars
	 */
	private static function ajaxify(array &$button, ?string $rel_table, ?string $rel_id, ?string $action, ?array $vars): void
	{
		array_walk_recursive($button, function(&$value, $key) use ($rel_table){
			if($value == "rel_table"){
				$value = $rel_table;
			}
			else {
				$value = $value == str_replace("rel_table", $rel_table, $value) ? $value : str::title(str_replace("rel_table", $rel_table, $value));
			}

		});
		array_walk_recursive($button, function(&$value, $key) use ($rel_id){
			$value = str_replace("rel_id", $rel_id, $value);
		});
		array_walk_recursive($button, function(&$value, $key) use ($action){
			$value = str_replace("action", $action, $value);
		});
		array_walk_recursive($button, function(&$value, $key) use ($vars){
			if($value == "vars"){
				$value = $vars;
			}
		});
		array_walk_recursive($button, function(&$value, $key){
			if($key == "icon"){
				$value = Icon::get($value);
			}
		});
	}

	/**
	 * Generate generic button arrays.
	 * Make uniform edit, duplicate and remove buttons for rows, etc.
	 *
	 * <code>
	 * $buttons[] = Button::generic(["edit", "duplicate", "remove"], $rel_table, $cols["{$rel_table}_id"]);
	 * </code>
	 *
	 * @param             $name
	 * @param string|null $rel_table
	 * @param string|null $rel_id
	 * @param string|null $action
	 * @param array|null  $vars
	 * @param array|null  $overrides
	 *
	 * @return array
	 * @throws \Exception
	 */
	static function generic($name, ?string $rel_table = NULL, ?string $rel_id = NULL, ?array $vars = NULL, ?array $overrides = NULL): array
	{
		# The button name can also be an array to make the process even more efficient
		if(is_array($name)){
			foreach($name as $n){
				$buttons[] = self::generic($n, $rel_table, $rel_id, $vars, $overrides);
			}
			return $buttons;
		}

		# Ensure the common button exists
		if(!$button = self::GENERIC[$name]){
			throw new \Exception("Cannot find the generic button <code>{$name}</code>.");
		}

		# Localise
		self::ajaxify($button, $rel_table, $rel_id, $action, $vars);

		# Apply overrides
		if($overrides){
			$button = array_merge($button, $overrides);
		}

		return $button;
	}

	/**
	 * Checks to see the kind of vars submitted to the button generation method.
	 * Allows for wider use of the pre-made buttons by simply submitting
	 * name, rel_table, rel_id in an array, instead of writing out the whole button each time.
	 *
	 * @param string|array $a
	 * @param bool         $rel_table
	 * @param bool         $rel_id
	 *
	 * @param bool         $callback
	 *
	 * @return bool|array
	 */
	static function getArray($a, $rel_table = false, $rel_id = false, $callback = false)
	{
		if(str::isNumericArray($a)){
			return self::getArray($a[0], $a[1], $a[2], $a[3]);
		}

		if(!is_array($a)){
			//if the only thing passed is the name of a generic button
			if(!$a = Button::COMMON[$a]){
				//if a generic version is not found
				return false;
			}
		}

		return $a;
	}

	/**
	 * @param $a
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	static function multi($a)
	{
		if(!$a){
			return false;
		}

		if(is_array($a) && !str::isNumericArray($a)){
			$buttons[] = $a;
		}
		else if(str::isNumericArray($a)){
			$buttons = $a;
		}
		else if(is_array($a)){
			return Button::generate($a);
		}
		else if(is_string($a)){
			return $a;
		}
		else {
			return false;
		}

		foreach($buttons as $id => $button){
			if(is_array($button)){
				$html .= Button::generate($button);
			}
			else {
				$html .= $button;
			}
		}

		return $html;
	}

	/**
	 * Given an array of either a "button" or "buttons",
	 * returns HTMl with both, or returns NULL if neither are present.
	 *
	 * @param array|null $a
	 *
	 * @return string
	 * @throws \Exception
	 * @throws \Exception
	 */
	static function get(?array $a): ?string
	{
		if(!is_array($a)){
			return NULL;
		}

		# Dropdown buttons
		if($a['buttons']){
			return Dropdown::generate(["buttons" => $a['buttons']]);
			//Done this way to not drag in the other keys like icon
		}

		# Button(s) in a row
		if(!$button = Button::generate($a['button'])){
			return NULL;
		}

		# The buttons are by default wrapped in a btn-float-right div. This can be overwritten, by parent_class/style
		$parent_class_array = ["btn-float-right"];

		if(str::isAssociativeArray($a['button'])){
			$parent_class_array = str::getAttrArray($a['button']['parent_class'], $parent_class_array, $a['button']['only_parent_class']);
		}

		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $a['button']['parent_style']);

		return "<div{$parent_class}{$parent_style}>{$button}</div>";
	}

	/**
	 * Generates a button based on an array of settings
	 * <code>
	 * $html .= Button::generate([
	 *    "hash" => "{$rel_table}/{$rel_id}",
	 *    "basic" => true,
	 *    "colour" => "grey",
	 *    "icon" => "chevron-left",
	 *    "title" => "Return",
	 *    "subtitle" => "Go back",
	 *    "alt" => "Text appears when hover",
	 * ]);
	 * </code>
	 *
	 * @param array|string $a         Array of settings or name of generic button
	 * @param string|bool  $rel_table Optional, if a generic button with localisation has been chosen.
	 * @param string|bool  $rel_id    Optional, if a generic button with localisation has been chosen.
	 *
	 * @param bool         $callback
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function generate($a, $rel_table = false, $rel_id = false, $callback = false)
	{
		if(!is_array($a) && !is_string($a)){
			// A valid button value has to be either an array or a string
			return false;
		}

		if(str::isNumericArray($a)){
			$a = array_reverse($a);
			foreach($a as $b){
				$buttons[] = self::generate($b);
			}
			return implode("", $buttons);
		}

		$a = self::getArray($a, $rel_table, $rel_id, $callback);

		if(!$a['id']){
			$a['id'] = "button_" . rand();
		}

		extract($a);

		# Buttons with children are to be treated a little differently
		if($children){
			return self::generateWithChildren($a);
		}

		$href = href::generate($a);

		# Style with override
		$style_array = str::getAttrArray($style, false, $only_style);

		# Is it a basic button?
		if($basic || $outline){
			$outline = "-outline";
		}

		# What colour is the button?
		$colour = $colour ?: "dark";
		//default is a b&w theme

		# Class with override
		$class_array = str::getAttrArray($class, ["btn", "btn{$outline}-{$colour}"], $only_class);

		# Who is directing the button?
		if($approve){
			//if an approval dialogue is to prepend the action
			$approve_attr = str::getApproveAttr($a['approve']);
			$class_array[] = "approve-decision";
		}

		# OnClicks aren't treated as true buttons, fix it
		if($onClick){
			$style_array["cursor"] = "pointer";
		}

		# Does this button have children?
		if($children){
			$class_array[] = $parent;
		}

		# Does it have an SVG icon?
		if($svg){
			$svg = SVG::generate($svg, [
				"height" => "1rem",
				"position" => "relative",
				"top" => "0.2rem",
				"left" => "-0.2rem",
			]);
			$icon = false;
		}

		# Does it have an icon?
		else if($icon){
			$icon = Icon::generate($icon);
		}

		# Does it have an badge?
		if($badge){
			$badge = Badge::generate($badge);
		}

		# is it to be placed to the right?
		if($right){
			$class_array[] = "float-right";
		}

		# What tag-type is it?
		if($tag_type){
			//a tag type can be forced
		}
		else if($type == 'file'){
			$tag_type = "input";
			$name = "name=\"{$name}\"";
			if($multiple){
				$multiple = "multiple";
			}
			# Prevent vars array to be sent as [Object object]
			foreach($data as $key => $val){
				if(is_array($val)){
					foreach($val as $sub_key => $sub_val){
						$flat_data["{$key}[{$sub_key}]"] = $sub_val;
					}
				}
				else {
					$flat_data[$key] = $val;
				}
			}
			$json_data = json_encode($flat_data);
			$script .= /**@lang JavaScript */
				"
			$(function () {
				$('#{$id}').fileupload({
					url: 'ajax.php',
					formData: {$json_data},
					dataType: 'json',
					add: function (e, data) {
					    $('.spinner').show();
						data.submit();
					},
					done: function (e, data) {
					    connectionSuccess(data.result);
					}
				});
			});
			";
		}
		else if($name && $value){
			//if the button has a value that needs to be collected
			$type = "submit";
			$name = str::getAttrTag("name", $name);
			$value = str::getAttrTag("value", $value);
			$tag_type = "button";
		}
		else if($type == 'submit'){
			//for most buttons, this is the type
			$tag_type = "button";
		}
		else if($onClick || $onclick){
			$tag_type = 'a';
		}
		else {
			$tag_type = 'a';
		}

		# Is it disabled?
		if($disabled){
			$style_array["cursor"] = "default";
			$class_array[] = "btn-outline-{$colour}";
			$class_array[] = "disabled";
			$disabled = "disabled=\"disabled\"";
			$tag_type = "button";
		}

		# Size
		$class_array[] = self::getSize($size);

		# Pulsating
		if($pulsating){
			[$wrapper_pre, $wrapper_post] = self::pulsating($pulsating);
		}

		# Form submit buttons
		$form = str::getAttrTag("form", $form);

		# Script
		$script = str::getScriptTag($script);

		# Data attributes
		$data_attributes = str::getDataAttr($data);

		if($ladda !== false && !$url && !$children){
			//if Ladda has not explicitly been set to false and
			//if this not a button-link to an external site
			$class_array[] = "ladda-button";
			$span_pre = "<span class=\"ladda-label\">";
			$span_post = "</span>";
		}

		$class_tag = str::getAttrTag("class", $class_array);
		$style_tag = str::getAttrTag("style", $style_array);
		$id_tag = str::getAttrTag("id", $id);
		$type_tag = str::getAttrTag("type", $type);
		$title_tag = str::getAttrTag("title", $alt ?: strip_tags($title));
		$data_style_tag = str::getAttrTag("data-style", "slide-left");

		$button_html = /** @lang HTML */
			<<<EOF
{$wrapper_pre}
<{$tag_type}
{$id_tag}
{$href}
{$name}
{$value}
{$class_tag}
{$style_tag}
{$type_tag}
{$data_style_tag}
{$title_tag}
{$disabled}
{$multiple}
{$data_attributes}
{$approve_attr}
{$form}
>{$span_pre}
{$icon}{$svg}
{$title}
{$sub_title}
{$span_post}
{$badge}
</{$tag_type}>
{$wrapper_post}
{$script}
EOF;

		if($type == 'file'){
			$for_tag = str::getAttrTag("for", $id);
			$button_html = /** @lang HTML */
				<<<EOF
<{$tag_type}
	{$id_tag}
	{$href}
	{$name}
	{$value}
	{$type_tag}
	data-style="slide-left"
	style="display:none;"
	{$title_tag}
	{$disabled}
	{$multiple}>
<label
	{$for_tag}
	{$class_tag}
	style="margin: -3px 0 0 0;"
>
	<span class="ladda-label">
		{$icon}{$svg}
		{$title}
		{$sub_title}
	</span>
</label>
{$script}
{$approve_script}
EOF;

		}

		return $button_html;
	}

	/**
	 * If the button has children,
	 * the process is a little different.
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function generateWithChildren(array $a): string
	{
		# Separate out the children
		$children = $a['children'];
		unset($a['children']);

		# Remove the ladda from the button itself
		$a['ladda'] = false;

		$a['title'] .= "&nbsp;" . Icon::generate([
				"style" => [
					"font-weight" => "500 !important",
				],
				"name" => "chevron-down",
			]);

		return Dropdown::generateButton([
			"button" => self::generate($a),
			"children" => $children,
		]);
	}

	/**
	 * @param $a
	 *
	 * @return bool|string[]
	 */
	static function pulsating($a)
	{
		if(!$a){
			return false;
		}
		else if(is_bool($a)){
			$pulsating['colour'] = "black";
		}
		else if(is_string($a)){
			$pulsating['colour'] = $a;
		}
		else if(is_array($a)){
			$pulsating = $a;
		}
		else {
			return false;
		}

		# Class
		$class[] = "pulsating-{$pulsating['colour']}";
		$class[] = $pulsating['class'];
		$class = str::getAttrTag("class", $class);

		# Style
		$style = str::getAttrTag("style", $pulsating['style']);

		return ["<div{$class}{$style}>", "</div>"];
	}

	/**
	 * If a button size has been given, make the size uniform,
	 * prefix with btn- and return to be used in the class array.
	 *
	 * @param string|null $size
	 *
	 * @return string|null
	 */
	public static function getSize(?string $size): ?string
	{
		if(!$size){
			return NULL;
		}

		switch($size) {
		case 's':
			return "btn-sm";
		case 'small':
			return "btn-sm";
		case 'large':
			return "btn-lg";
		default:
			return "btn-{$size}";
		}
	}
}