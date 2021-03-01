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
		$label = self::getLabel($label, $title, $name, $id, $for);

		# Icons
		$icon = self::getIcon($icon);
		$icon_suffix = self::getIcon($icon_suffix);

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, "input-group", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		
		# Parent style
		$parent_style = str::getAttrTag("style", $parent_style);

		# Class array
		$class_array = str::getAttrArray($class, ["form-control", $disabled_class], $only_class);

		# Validation
		$validation = self::getValidationTags($validation, $class_array);

		# Class string
		$class = str::getAttrTag("class", $class_array);
		
		# Style
		$style = str::getAttrTag("style", $style);

		# Placeholder
		$placeholder = str::getAttrTag("placeholder", self::getPlaceholder($placeholder, $name));

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

		# $data
		$data = self::getInputData($a);

		# Script (using $a because it may have changed in getSelectData())
		$script = str::getScriptTag($a['script']);

		return /** @lang HTML */
			<<<EOF
{$label}
<div class="mb-3">
	<div{$parent_class}{$parent_style}>
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
			{$validation}
			{$checked}
			{$data}
		/>
		{$icon_suffix}
		{$button}	
		{$post_button}
		{$post}
	</div>
	{$desc}
	{$script}
</div>	
EOF;
	}

	private static function getInputData(array &$a): ?string
	{
		extract($a);
		$data['onChange'] = self::getOnChange($a);
		return str::getDataAttr($data);
	}
}