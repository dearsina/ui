<?php


namespace App\UI\Form;

use App\Common\str;
use App\UI\Icon;

class Input extends Field {
	static function generateHTML($a){
		extract($a);
		
		# Type correction
		extract(self::getInputType($type));

		# Label
		$label = self::getLabel($label, $name, $id);

		# Icons
		$icon = self::getIcon($icon, "prepend");
		$icon_suffix = self::getIcon($icon_suffix, "append");

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}
		
		# Validation
		$validation = self::getValidationTags($validation);

		# Parent class
		$parent_class_array = str::getAttrArrray($parent_class, "input-group", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		
		# Parent style
		$parent_style = str::getAttrTag("style", $parent_style);

		# Class
		$class_array = str::getAttrArrray($class, ["form-control", $disabled_class], $only_class);
		$class = str::getAttrTag("class", $class_array);
		
		# Style
		$style = str::getAttrTag("style", $style);

		# Placeholder
		$placeholder = str::getAttrTag("placeholder", self::getPlaceholder($placeholder));

		# Misc
		$min = str::getAttrTag("min", $min);
		$max = str::getAttrTag("max", $max);
		$step = str::getAttrTag("step", $step);
		$title = str::getAttrTag("title", $alt);
		$autocomplete = str::getAttrTag("autocomplete", $autocomplete);
		$checked = $checked ? "checked" : false;

		# Button
		$button = self::getButton($button, $type);

		# Description
		$desc = self::getDesc($desc);

		# Script
		$script = str::getScriptTag($script);

		return /** @lang HTML */
			<<<EOF
<div class="form-group">
  {$label}
  <div{$parent_class}{$parent_style}>
    {$icon}
    <input
    	id="{$id}"
		type="$type"
		name="$name"
		value="$value"
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
    />
    <div class="input-group-addon form-control-feedback hidden"><i class="far fa-fw"></i></div>
    {$icon_suffix}
    {$button}
  </div>
  {$desc}
  {$script}
</div>
EOF;
	}
}