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
	 * Buttons can be localised by including "rel_table" or "rel_id".
	 *
	 */
	const GENERIC = [
		"save" => [
			"colour" => "green",
			"icon" => [
				"name" => "save",
				"type" => "light"
			],
			"title" => "Save",
			"type" => "submit",
		],

		"cancel" => [
			"onClick" => "window.history.back();",
			"title" => "Cancel",
			"colour" => "grey",
			"basic" => true
		],

		"cancel_md" => [
			"title" => "Cancel",
			"colour" => "grey",
			"basic" => true,
			"data" => [
				"dismiss" => "modal"
			],
			"class" => "float-right"
		],

		"close_md" => [
			"title" => "Close",
			"colour" => "grey",
			"basic" => true,
			"data" => [
				"dismiss" => "modal"
			],
			"class" => "float-right"
		],

		"remove_md" => [
			"title" => "Remove rel_table",
			"basic" => true,
			"colour" => "red",
			"icon" => "trash",
			"approve" => "remove this rel_table",
			"hash" => "rel_table/rel_id/remove/callback/",
			"class" => "float-right",
			"data" => [
				"dismiss" => "modal"
			]
		],

		"return" => [
			"onClick" => "window.history.back();",
			"icon" => "chevron-left",
			"title" => "Return",
			"class" => "reset",
			"basic" => true,
		],

		// Legacy //
		"next" => [
			"colour" => "primary",
			"icon" => [
				"name" => "save",
				"type" => "light"
			],
			"title" => "Next",
			"type" => "submit",
		],
		"update" => [
			"colour" => "green",
			"icon" => [
				"name" => "save",
				"type" => "light"
			],
			"title" => "Update",
			"type" => "submit",
		],
		"update_md" => [
			"colour" => "blue",
			"icon" => [
				"name" => "save",
				"type" => "light"
			],
			"title" => "Update",
			"onClick" => "$(this).closest('form').submit();"
		],

		"match" => [
			"alt" => "In sync",
			"colour" => "green",
			"icon" => "check",
			"disabled" => true,
			"style" => "float:right;margin-top:0;",
			"class" => "btn-sm"
		],
		"view_removed" => [
			"title" => "View removed",
			"icon" => "trash-alt",
			"hash" => "rel_table//removed",
		],
		"remove" => [
			"title" => "Remove rel_table...",
			"alt" => "Remove rel_table",
			"basic" => true,
			"colour" => "red",
			"icon" => "trash",
			"approve" => "remove this rel_table",
			"hash" => "rel_table/rel_id/remove/callback/",
			"class" => "btn-sm",
			"remove" => 'closest(".container")'
		],
		"remove_all" => [
			"title" => "Remove all...",
			"alt" => "Remove all instances of rel_table",
			"basic" => true,
			"colour" => "red",
			"icon" => "trash",
			"approve" => "empty the rel_table table",
			"hash" => "rel_table//remove_all/callback/",
			"class" => "btn-sm",
		],
		"remove_sm" => [
			"alt" => "Remove rel_table",
			"basic" => true,
			"colour" => "red",
			"icon" => "trash",
			"approve" => "remove this rel_table",
			"hash" => "rel_table/rel_id/remove/callback/",
			"class" => "btn-sm",
			"remove" => 'closest(".container")'
		],
		"new" => [
			"icon" => "plus",
			"colour" => "blue",
			"title" => "New rel_table...",
			"hash" => "rel_table//new"
		],
		"edit" => [
			"icon" => "pencil",
			"title" => "Edit rel_table...",
			"hash" => "rel_table/rel_id/edit"
		],
		"edit_sm" => [
			"icon" => "pencil",
			"basic" => true,
			"alt" => "Edit rel_table",
			"hash" => "rel_table/rel_id/edit",
			"class" => "btn-sm",
		]
	];

	/**
	 * Localises a button if rel_table/id has been included in the call to the button generator.
	 *
	 * @param      $a
	 * @param bool|string $rel_table
	 * @param bool|string $rel_id
	 * @param bool|string $callback
	 *
	 * @return bool
	 */
	static function localise(&$a, $rel_table = false, $rel_id = false, $callback = false){
		if(!$rel_table) {
			return true;
		}

		foreach($a as $key => $val){
			if(is_array($val)){
				continue;
			}

			$a[$key] = $val;
			$a[$key] = str_replace("rel_table",	 $rel_table, $a[$key]);
			$a[$key] = str_replace("rel_id", $rel_id, $a[$key]);
			if($callback){
				$callback = str::urlencode($callback);
				$a[$key] = str_replace("callback/",	"callback/".$callback, 	$a[$key]);
			} else {
				$a[$key] = str_replace("callback/",	"", $a[$key]);
			}

			if(in_array($key,["title","alt"])){
				$a[$key] = str::title($a[$key]);
			}
		}

		return true;
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
	static function getArray($a, $rel_table = false, $rel_id = false, $callback = false){
		if(str::isNumericArray($a)){
			return self::getArray($a[0],$a[1],$a[2],$a[3]);
		}

		if(!is_array($a)){
			//if the only thing passed is the name of a generic button
			if(!$a = Button::GENERIC[$a]){
				//if a generic version is not found
				return false;
			}
		}

		# Infuse with relevant locally relevant vars
		self::localise($a, $rel_table, $rel_id, $callback);

		return $a;
	}

	/**
	 * @param $a
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	static function multi($a){
		if(!$a){
			return false;
		}

		if(is_array($a) && !str::isNumericArray($a)){
			$buttons[] = $a;
		} else if(str::isNumericArray($a)){
			$buttons = $a;
		} else if(is_array($a)){
			return Button::generate($a);
		} else if(is_string($a)){
			return $a;
		} else {
			return false;
		}

		foreach($buttons as $id => $button){
			if(is_array($button)){
				$html .= Button::generate($button);
			} else {
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
	 * @return string
	 * @throws \Exception
	 */
	static function generate($a, $rel_table = false, $rel_id = false, $callback = false){
		if(!$a){
			//if no data is submitted to the method, ignore it
			return false;
		}

		if(str::isNumericArray($a)){
			$a = array_reverse($a);
			foreach($a as $b){
				$buttons[] = self::generate($b);
			}
			return implode("",$buttons);
		}

		$a = self::getArray($a, $rel_table, $rel_id, $callback);

		if(!$a['id']){
			$a['id'] = "button_".rand();
		}

		extract($a);

		# Buttons with children are to be treated a little differently
		if($children){
			return self::generateWithChildren($a);
		}

		# Who is directing the button?
		if($approve){
			//if an approval dialogue is to prepend the action
			$approve_attr = str::getApproveAttr($a['approve']);
			$approve_class = "approve-decision";
		}

		$href = href::generate($a);

		# Style with override
		$style_array = str::getAttrArray($style, false, $only_style);

		# OnClicks aren't treated as true buttons, fix it
		if($onClick){
			$style_array["cursor"] = "pointer";
		}

		# Is it a basic button?
		if($basic || $outline){
			$outline = "-outline";
		}

		# What colour is the button?
		$colour = $colour ?: "dark";
		//default is a b&w theme

		# Does this button have children?
		if($children){
			$class_array[] = $parent;
		}

		# Does it have an icon?
		if($svg = SVG::generate($svg, "
		height: 1rem;
		position: relative;
		top: .2rem;
		left: -0.2rem;")){
			$icon = false;
		} else if($icon){
			$icon = Icon::generate($icon);
		}

		# Does it have an badge?
		if($badge){
			$badge = Badge::generate($badge);
		}

		# is it to be placed to the right?
		if($right){
			$right = 'float-right';
		}

		# What tag-type is it?
		if($tag_type){
			//a tag type can be forced
		} else if($type == 'file') {
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
				} else {
					$flat_data[$key] = $val;
				}
			}
			$json_data = json_encode($flat_data);
			$script .= /**@lang JavaScript*/"
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
		} else if($name && $value) {
			//if the button has a value that needs to be collected
			$type = "submit";
			$name = str::getAttrTag("name", $name);
			$value = str::getAttrTag("value", $value);
			$tag_type = "button";
		} else if($type == 'submit') {
			//for most buttons, this is the type
			$tag_type = "button";
		} else if($onClick||$onclick) {
			$tag_type = 'a';
		} else {
			$tag_type = 'a';
		}

		# Is it disabled?
		if($disabled){
			$outline = "-outline";
			$disabled = "disabled=\"disabled\"";
			$tag_type = "button";
			$disabled_class = "disabled";
			$style_array["cursor"] = "default";
		}

		# Size
		if($size){
			switch($size){
			case 's' 	: $size = "sm"; break;
			case 'small': $size = "sm"; break;
			case 'large': $size = "lg"; break;
			}
			$class = "btn-{$size} {$class}";
		}

		# Class with override override
		$class_array = str::getAttrArray($class, ["btn", "btn{$outline}-{$colour}", $right, $approve_class, $disabled_class], $only_class);

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

		$class_tag 		= str::getAttrTag("class", $class_array);
		$style_tag 		= str::getAttrTag("style", $style_array);
		$id_tag 		= str::getAttrTag("id", $id);
		$type_tag 		= str::getAttrTag("type", $type);
		$title_tag 		= str::getAttrTag("title", $alt ?: strip_tags($title));
		$data_style_tag	= str::getAttrTag("data-style", "slide-left");

		$button_html = /** @lang HTML */<<<EOF
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
			$button_html = /** @lang HTML */<<<EOF
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

		$a['title'] .= "&nbsp;".Icon::generate([
			"style" => [
				"font-weight" => "600 !important"
			],
			"name" => "chevron-down"
		]);

		return Dropdown::generateButton([
			"button" => self::generate($a),
			"children" => $children
		]);
	}

	/**
	 * @param $a
	 *
	 * @return bool|string[]
	 */
	static function pulsating($a){
		if(!$a){
			return false;
		} else	if(is_bool($a)){
			$pulsating['colour'] = "black";
		} else if (is_string($a)){
			$pulsating['colour'] = $a;
		} else if (is_array($a)){
			$pulsating = $a;
		} else {
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
	 * Generic edit button for a table.
	 *
	 * @param string     $rel_table
	 * @param string     $rel_id
	 * @param array|null $vars
	 *
	 * @return array
	 */
	static function edit(string $rel_table, string $rel_id, ?array $vars = NULL): array
	{
		return [
			"size" => "s",
			"hash" => [
				"rel_table" => $rel_table,
				"rel_id" => $rel_id,
				"action" => "edit",
				"vars" => $vars
			],
			"icon" => Icon::get("edit"),
			"basic" => true,
		];
	}

	/**
	 * Generic remove button for a table.
	 *
	 * @param string     $rel_table
	 * @param string     $rel_id
	 * @param array|null $vars
	 *
	 * @return array
	 */
	static function remove(string $rel_table, string $rel_id, ?array $vars = NULL): array
	{
		return [
			"size" => "s",
			"hash" => [
				"rel_table" => $rel_table,
				"rel_id" => $rel_id,
				"action" => "remove",
				"vars" => $vars
			],
			"approve" => [
				"icon" => Icon::get("trash"),
				"colour" => "red",
				"title" => str::title("Remove {$rel_table}?"),
				"message" => str::title("Are you sure you want to remove this {$rel_table}?"),
			],
			"icon" => Icon::get("trash"),
			"basic" => true,
			"colour" => "danger",
		];
	}
}