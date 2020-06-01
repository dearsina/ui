<?php


namespace App\UI\Form;


use App\Common\str;
use App\UI\Icon;

class Select extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML (array $a) {
		extract($a);
		
		# Label
		$label = self::getLabel($label, $title, $name, $id);

		# Options
		$options_html = self::getOptionsHTML($a);

		# Multiple values allowed?
		$multiple = str::getAttrTag("multiple", $multiple ? "multiple" : false);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, ["form-group", $disabled_parent_class], $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);

		# Parent style
		$parent_style = str::getAttrTag("style", $parent_style);

		# Class
		$class_array = str::getAttrArray($class, ["form-control", $disabled_class], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Validation
		$validation = self::getValidationTags($validation);

		# Description
		$desc = self::getDesc($desc);

		# Script
		$script = str::getScriptTag(self::getSelectScript($a));

		return /** @lang HTML */ <<<EOF
<div{$parent_class}{$parent_style}>
	{$label}
	<select
		id="{$id}"
		type="$type"
		name="$name"
		{$multiple}
		{$class}
		{$style}
		{$disabled}
		{$validation}
	>
	{$options_html}
	</select>
	{$desc}
</div>
{$script}
EOF;
	}

	/**
	 * Gets an options array, generates HTML,
	 * and returns it.
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private static function getOptionsHTML($a){
		extract($a);
		$matched = false;
		$options_array = self::getOptionsArray($a, $matched);
		if (!$matched && !$multiple) {
			//if no options match on the value
			//and it's _not_ a multiple situation
			$options_array[] = [
				"value" => "",
				"title" => "",
				"selected" => true
			];
			//add a blank option that's set as selected
			//This way, the dropdown defaults to the placeholder
		}

		if(empty($options_array)){
			return false;
		}

		foreach($options_array as $option){
			$value = str::getAttrTag("value", $option['value']);
			$selected = $option['selected'] ? " selected" : false;
			$options_html .= "<option{$value}{$selected}>{$option['title']}</option>";
		}

		return $options_html;
	}

	/**
	 * Given options, will return an array with options,
	 * their titles and whether they're selected or not.
	 *
	 * @param      $a
	 * @param bool $matched Will be set to TRUE if at least one option is selected.
	 *
	 * @return bool
	 */
	private static function getOptionsArray($a, &$matched = false){
		extract($a);

		if (is_array($value)) {
			//Many values
			$value_array = $value;
		} else if($value){
			//One value
			$value_array = [$value];
		} else {
			//No value(s)
			$value_array = [];
		}

		if(!is_array($options)){
			return false;
		}

		foreach ($options as $option_value => $option) {
			if (is_array($option)) {
				$option_title = $option['title'];
			} else {
				$option_title = $option;
			}

			if ($value_array && in_array($option_value, $value_array)) {
				$selected = true;

				$matched = true;
				/**
				 * If one of the options are matched, set variable to true.
				 * There are scenarios where the values in the value array,
				 * do not match any option values.
				 */
			} else {
				$selected = false;
			}

			$options_array[] = [
				"value" => $option_value,
				"title" => $option_title,
				"selected" => $selected,
			];
		}

		return $options_array;
	}

	private static function getSelectScript($a){
		extract($a);
		
		$class_array = str::getAttrArray($class, "select2js", $only_class);

		$settings['containerCssClass'] = str::getAttrTag( false, $class_array);
		$settings['placeholder'] = self::getPlaceholder($placeholder);
		$settings['ajax'] = $ajax;
		$settings['value'] = $value;
		$settings_json = json_encode(array_filter($settings));

		return /** @lang JavaScript */<<<EOF
$("#{$id}").select2(select2Settings({$settings_json}))
	.on("change", function (e) {
		$(this).valid();
		{$onChange}
	});
{$script}
EOF;
	}
}