<?php


namespace App\UI\Form;

use App\Common\str;
use App\UI\Button;

/**
 * Class Input
 * @package App\UI\Form
 */
class Input extends Field {
	/**
	 * @param $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function generateHTML($a){
		extract($a);

		#
		if(!$type && ($html||$button||$buttons)){
			return false;
		}
		
		# Type correction
		extract(self::getInputType($type));

		# Label
		$label = self::getLabel($label, $title, $name, $id, $for, NULL, $type);

		# Icons
		$icon = self::getIcon($icon);
		$icon_suffix = self::getIcon($icon_suffix);

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}

		# Read only (like disabled, but value is accessible)
		else if($readonly){
			$disabled = str::getAttrTag("readonly", "true");
			$disabled_class = "disabled";
		}

		# Placeholder
		$placeholder = str::getAttrTag("placeholder", self::getPlaceholder($placeholder, $name));

		# Floating label
		if(self::useFloatingLabel($a, $type, $label)){
			$floating_label = $label;
			$label = NULL;
			$default_parent_class = "input-group-floating";
			$placeholder = " placeholder=\" \"";
		}

		# Non-floating label
		else {
			$default_parent_class = "input-group";
		}

		# Parent
		$parent_id = str::getAttrTag("id", $parent_id);
		$parent_class_array = str::getAttrArray($parent_class, $default_parent_class, $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $parent_style);

		# Grand parent
		$grand_parent_id = str::getAttrTag("id", $grand_parent_id);
		$grand_parent_class_array = str::getAttrArray($grand_parent_class, "mb-3", $only_grand_parent_class);
		$grand_parent_class = str::getAttrTag("class", $grand_parent_class_array);
		$grand_parent_style = str::getAttrTag("style", $grand_parent_style);

		# Class array
		$class_array = str::getAttrArray($class, ["form-control", $disabled_class], $only_class);

		# Validation
		self::setValidationData($a, $class_array);

		# Set dependency data
		self::setDependencyData($a);

		# Class string
		$class = str::getAttrTag("class", $class_array);
		
		# Style
		$style = str::getAttrTag("style", $style);

		# Misc
		$min = str::getAttrTag("min", $min);
		$max = str::getAttrTag("max", $max);
		$step = str::getAttrTag("step", $step);
		$title = str::getAttrTag("title", $alt);
		$autocomplete = str::getAttrTag("autocomplete", $autocomplete);
		$checked = $checked ? "checked" : false;

		# Button
		$button = self::getButton($button, $type);

		# Pre button
		$pre_button = Button::generate($pre_button);

		# Post button
		$post_button = Button::generate($post_button);

		# Pre (addon)
        if($pre){
            $pre = "<span class=\"input-group-text\">$pre</span>";
        }

		# Post (addon)
        if($post){
            $post = "<span class=\"input-group-text\">$post</span>";
        }

		# Description
		$desc = self::getDesc($desc);

		# Accept and Capture
		$accept = str::getAttrTag("accept", $accept);
		$capture = str::getAttrTag("capture", $capture);

		# $data
		$data = self::getInputData($a);

		# Setting form fields in focus
		Input::setFocus($a);

		# Script (using $a because it may have changed in getSelectData())
		$script = str::getScriptTag($a['script']);

		# Hacky way to fix issue with " in the value
		$value = str_replace('"', '&quot;', $value);

		return /** @lang HTML */
			<<<EOF
<div{$grand_parent_id}{$grand_parent_class}{$grand_parent_style}>
	{$label}
	<div{$parent_id}{$parent_class}{$parent_style}>
		{$icon}
		{$pre_button}
		{$pre}
	<input
		id="{$id}"
		type="{$type}"
		name="{$name}"
		value="{$value}"
		{$class}
		{$placeholder}
		{$title}
		{$style}
		{$min}
		{$max}
		{$step}
		{$autocomplete}
		{$disabled}
		{$checked}
		{$accept}
		{$capture}
		{$data}
	/>
		{$icon_suffix}
		{$button}	
		{$post_button}
		{$post}
		{$floating_label}
	</div>
	{$desc}
	{$script}
</div>	
EOF;
	}

	public static function setFocus(array &$a): void
	{
		extract($a);
		if($focus || $autofocus){
			# Ensure an ID has been set, if not, set it
			$a['id'] = $id ?: str::id();
			# Add a line to the script tag
			$a['script'] .= "\r\nsetTimeout( function() { $('#{$a['id']}').focus(); }, 0 );";
		}
	}

	private static function getInputData(array &$a): ?string
	{
		extract($a);

		return str::getDataAttr(array_merge($a['data'] ?: [], [
			"parent" => $parent,
			"onChange" => self::getOnChange($a),
			"onDemand" => $onDemand ?: $ondemand,
		]));
	}

	/**
	 * Determines whether to use a floating label for the given input configuration.
	 *
	 * @param array       $a     An array of input attributes and options.
	 * @param string|null $type  The type of the input element, which may affect the decision to use a floating label.
	 * @param string|null $label The label associated with the input element, required for a floating label.
	 *
	 * @return bool Returns true if a floating label should be used; otherwise, false.
	 */
	private static function useFloatingLabel(array $a, ?string $type, ?string $label): bool
	{
		if(empty($a['floating_label']) || $label === NULL || trim(strip_tags($label)) === ""){
			return false;
		}

		return !in_array($type, [
			"hidden",
			"file",
			"color",
			"range",
		], true);
	}
}
