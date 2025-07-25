<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;
use App\Language\Language;
use App\Translation\Translator;
use App\UI\Form\Form;

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
			"icon" => "save",
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
			"alt" => "New",
		],

		"test" => [
			"size" => "s",
			"hash" => [
				"rel_table" => "rel_table",
				"rel_id" => "rel_id",
				"action" => "test",
				"vars" => "vars",
			],
			"icon" => "test",
			"basic" => true,
			"colour" => "yellow",
			"alt" => "Test",
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
			"alt" => "Edit",
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
			"alt" => "Duplicate",
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
			"alt" => "Remove",
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
			$value = preg_replace("/\/action/", "/{$action}", $value);
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
	 *
	 * @return bool|array
	 */
	static function getArray($a)
	{
		if(!is_array($a)){
			//if the only thing passed is the name of a generic button
			if(!$a = Button::COMMON[$a]){
				//if a generic version is not found
				return false;
			}
		}

		# If the common name is passed as an array key
		if(key_exists("common", $a)){
			$a = array_merge($a, Button::COMMON[$a['common']]);
			unset($a['common']);
		}
		// This happens if the language ID needs to be passed to the common key

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
	 * Flattens buttons that are in complex numerical arrays.
	 * Prevents having to be careful when adding to the $buttons[] array.
	 *
	 * @param array $buttons
	 *
	 * @return array
	 */
	private static function flattenButtonArray(array $buttons): array
	{
		$flat_buttons = [];

		foreach($buttons as $button){
			# Ignore empty buttons
			if(!$button){
				continue;
			}

			# If the button is itself an array of buttons
			if(str::isNumericArray($button)){
				# Recursively flatten the array
				$flat_buttons = array_merge($flat_buttons, self::flattenButtonArray($button));
				# And continue
				continue;
			}

			# Add the button
			$flat_buttons[] = $button;
		}

		return $flat_buttons;
	}

	/**
	 * Get the dropdown buttons.
	 * Allows for buttons arrays to have buttons keys,
	 * and other keys like icon, style, etc.
	 *
	 *
	 * @param array $buttons
	 *
	 * @return string|null
	 */
	private static function getDropdown(array $buttons): ?string
	{
		# If the buttons array itself has a buttons-key (and perhaps other keys like icon, style, etc)
		if($buttons['buttons']){
			# Flatten the buttons key value (only)
			$buttons['buttons'] = self::flattenButtonArray($buttons['buttons']);
		}

		# Otherwise, convert the entire buttons array to a flat array and put it in the buttons key
		else {
			$buttons['buttons'] = self::flattenButtonArray($buttons);
		}

		return self::generateDropdown($buttons);
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
			return self::getDropdown($a['buttons']);
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
	 * Generates a dropdown with a default dropdown button.
	 * Primarily used by cards and modals.
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	private static function generateDropdown(array $a): ?string
	{
		extract($a);

		if(!$buttons){
			return NULL;
		}

		# The generic dropdown icon
		$icon = $icon ?: [
			"name" => "bars",
			"type" => "light",
		];

		return Dropdown::generateRootUl([
			"items" => [[
				"icon" => $icon,
				"alt" => $alt ?: "Click to open the menu",
				"direction" => $dierction ?: "left",
				"children" => [
					"direction" => $dierction ?: "left",
					"items" => $buttons,
				],
				"class" => "drop-float-right",
				"style" => $style,
			]],
		]);
	}

	private static function buildClassArray(array $a): array
	{
		extract($a);

		# Is it a basic button?
		if($basic || $outline){
			$outline = "-outline";
		}

		# What colour is the button?
		$colour = $colour ?: "dark";
		//default is a b&w theme

		# Class with override
		return str::getAttrArray($class, ["btn", "btn{$outline}-{$colour}"], $only_class);
	}

	private static function translate(array &$a): void
	{
		if(!class_exists("\\App\\Language\\Language")){
			return;
		}

		# Ensure there is a language to translate this button *to*
		if(!$a['language_id']){
			// If no to-language hs been set, there is nothing to translate
			return;
		}

		if(key_exists("approve", $a)){
			if(!is_array($a['approve'])){
				$a['approve'] = [];
			}
			$a['approve']['rtl'] = Language::getDirection($a['language_id']) == "rtl";
			$a['approve']['yes'] = "Yes";
			$a['approve']['no'] = "Cancel";
			$a['approve']['class'] = Language::getDirectionClass($a['language_id']);
		}

		if(class_exists("App\\Translation\\Translator")){
			if(!Translator::set($a, [
				"subscription_id" => $a['subscription_id'],
				"rel_table" => "button",
				"to_language_id" => $a['language_id'],
				"parent_rel_id" => $a['parent_rel_id']
			])){
				return;
			}
		}

		# Set the direction class
		Language::setDirectionClass($a['class'], $a['language_id']);
	}

	/**
	 * If the button is dependent on radio or checkbox selections,
	 * this function will add the necessary script to show/hide the button.
	 *
	 * @param array $a
	 *
	 * @return void
	 */
	public static function selectionDependency(array &$a): void
	{
		if(!$a['selection_dependency']){
			return;
		}

		if(!$a['id']){
			throw new \Exception("The button must have an ID to be able to show/hide it based on a selection.");
		}

		# Ensure they're both arrays
		$a['class'] = is_array($a['class']) ? $a['class'] : [$a['class']];
		$a['data'] = is_array($a['data']) ? $a['data'] : [];

		# Add the class and data attribute
		$a['class'][] = "selection-dependency";
		$a['data']['selection-dependency'] = $a['selection_dependency'];

		# Set the hash, and remove it so that it doesn't trigger twice
		$a['data']['hash'] = $a['hash'];
		unset($a['hash']);
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

		# Ensure the button has an ID
		$a['id'] = $a['id'] ?: str::id("button");

		# Checkboxes are to be treated a little differently
		if($a['checkbox']){
			return self::generateCheckboxes($a);
			// And must be placed before children, but they can *also* have children
		}

		# Buttons with children are to be treated a little differently
		else if($a['children']){
			return self::generateWithChildren($a);
		}

		# Buttons with splits are to be treated a little differently
		else if($a['split']){
			return self::generateWithSplit($a);
		}

		self::selectionDependency($a);

		# Translate the button
		self::translate($a);

		# Add a tooltip to the button
		Tooltip::generate($a);

		# Add copy feature
		Copy::generateButton($a);

		extract($a);

		$href = href::generate($a);

		# Style with override
		$style_array = str::getAttrArray($style, false, $only_style);

		# Build the class
		$class_array = Button::buildClassArray($a);

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

		# Who is directing the button?
		if($approve){
			//if an approval dialogue is to prepend the action
			$approve_attr = str::getApproveAttr($a['approve'], $icon, $colour);
			$class_array[] = "approve-decision";
		}

		# Does it have an icon?
		if($icon){
			$icon = Icon::generate($icon);
		}

		# Does it have a badge?
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

		$title_tag = str::getAttrTag("title", $alt ?: strip_tags($title));
		//Is up here because it is used by the disabled section

		# Is it disabled?
		if($disabled){
			$style_array["cursor"] = "default";

			$class_array[] = "btn-outline-{$colour}";
			$class_array[] = "disabled";

			$disabled = "disabled=\"disabled\"";
			$tag_type = "button";

			# Put the entire button inside a disabled wrapper div
			$wrapper_pre = "<div {$title_tag} class=\"disabled-wrapper\">";
			$wrapper_post = "</div>";
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

		# If the desktop-only flag is set
		if($desktop_only){
			$class_array[] = "btn-text-desktop-only";
			// With this set, the button will only be visible on desktop
		}

		$class_tag = str::getAttrTag("class", $class_array);
		$style_tag = str::getAttrTag("style", $style_array);
		$id_tag = str::getAttrTag("id", $id);
		$type_tag = str::getAttrTag("type", $type);
		$data_style_tag = str::getAttrTag("data-style", "slide-left");

		# A desktop title means that the *title* will only be visible on desktop
		if($desktop_title){
			$title = "<span class=\"btn-desktop-only\">$desktop_title</span>";
		}
		else {
			$title = strlen($title) ? "<span class=\"btn-text\">{$title}</span>" : $title;
		}


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

		# Add a chevron-down suffix
		$a['title'] .= "&nbsp;" . Icon::generate([
				"style" => [
					"font-weight" => "500 !important",
				],
				"name" => "chevron-down",
			]);

		$class = is_array($a['class']) ? $a['class'] : [$a['class']];
		array_unshift($class, ["navbar-nav nav-button"]);

		if($a['parent_style']){
			$parent_style = $a['parent_style'];
			unset($a['parent_style']);
		}

		return Dropdown::generateRootUl([
			"items" => [[
				"title" => self::generate($a),
				"children" => $children,
				"direction" => $a['direction'],
			]],
			"class" => $class,
			"style" => $parent_style,
		]);
	}

	static function generateCheckboxes(array $a): string
	{
		# Separate out the checkboxes
		$checkboxes = $a['checkbox'];
		unset($a['checkbox']);

		# Remove the ladda from the button itself
		$a['ladda'] = false;

		# Add a chevron-down suffix
		$a['title'] .= "&nbsp;" . Icon::generate([
				"style" => [
					"font-weight" => "500 !important",
				],
				"name" => "chevron-down",
			]);

		$class = is_array($a['class']) ? $a['class'] : [$a['class']];
		$class[] = "dropdown-checkbox";
		array_unshift($class, ["navbar-nav nav-button"]);

		if($a['parent_style']){
			$parent_style = $a['parent_style'];
			unset($a['parent_style']);
		}

		foreach($checkboxes as $checkbox){
			$form = new Form();

			# Set the type for the field generation
			$checkbox['type'] = "checkbox";

			# Checkbox style
			$checkbox['style'] = [
				"margin" => "8px",
				"margin-left" => "-25px",
			];

			# Undo the mb-3 class style
			$checkbox['grand_parent_style'] = [
				"margin" => "0 0 0 0 !important",
			];

			# Label style
			if($checkbox['label'] && !is_array($checkbox['label'])){
				$checkbox['label'] = [
					"html" => $checkbox['label'],
				];
			}
			$checkbox['label']['style'] = [
				"margin" => "0 0 0 0 !important",
			];

			# Add the HTML as a title
			$children[] = [
				"title" => $form->getFieldsHTML($checkbox),
			];
		}

		if($a['children']){
			$children = array_merge($children, $a['children']);
			unset($a['children']);
		}

		return Dropdown::generateRootUl([
			"items" => [[
				"title" => self::generate($a),
				"children" => $children,
				"direction" => $a['direction'],
			]],
			"class" => $class,
			"style" => $parent_style,
			"script" => $a['script'],
		]);
	}

	/**
	 * A split button is a button with a menu on the right.
	 * To make one, add children buttons to the split key.
	 *
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function generateWithSplit(array $a): string
	{
		# Separate out the children
		$children = $a['split'];
		unset($a['split']);

		# Build the main button
		$button = Button::generate($a);

		# Take the classes from the main and apply it to the dropdown trigger
		$class_array = Button::buildClassArray($a);
		$class_array[] = "dropdown-toggle dropdown-toggle-split";
		$class = str::getAttrTag("class", $class_array);

		# Generates the menu only
		$menu = Dropdown::generateUl([
			"children" => $children,
		]);

		$direction_class = Dropdown::getDirectionClass($children);

		return <<<EOF
<div class="btn-group {$direction_class}">
  {$button}
  <button type="button" {$class} data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <span class="visually-hidden">Toggle Dropdown</span>
  </button>
  {$menu}
</div>
EOF;

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
		case 'small':
			return "btn-sm";
		case 'l':
		case 'large':
			return "btn-lg";
		default:
			return "btn-{$size}";
		}
	}
}