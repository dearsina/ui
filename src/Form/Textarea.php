<?php


namespace App\UI\Form;


use App\Common\str;

/**
 * Class Textarea
 * @package App\UI\Form
 */
class Textarea extends Field implements FieldInterface {

	/**
	 * Returns a textarea HTML.
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generateHTML (array $a) {
		extract($a);

		# Label
		$label = self::getLabel($label, $title, $name, $id);

		$rows = $rows ?: 2;

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}

		# Class
		$class_array = str::getAttrArray($class, ["form-control", $disabled_class], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Placeholder
		$placeholder = str::getAttrTag("placeholder", self::getPlaceholder($placeholder));

		# Description
		$desc = self::getDesc($desc);

		# Script
		$script = str::getScriptTag($script);

		# Misc
		$title = str::getAttrTag("title", $alt);
		$autocomplete = str::getAttrTag("autocomplete", $autocomplete);
		$validation = self::getValidationTags($validation);

		return /** @lang HTML */ <<<EOF
<div class="mb-3">
	{$label}
	<div class="input-group">
		<textarea
			id="{$id}"
			name="{$name}"
			rows="{$rows}"
			{$class}
			{$style}
			{$placeholder}
			{$title}
			{$disabled}
			{$autocomplete}
			{$validation}
		>{$value}</textarea>
	</div>
	{$desc}
	{$script}
</div>
EOF;


	}
}