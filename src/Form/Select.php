<?php


namespace App\UI\Form;


use App\Common\str;

/**
 * Class Select
 * @package App\UI\Form
 */
class Select extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML (array $a)
	{
		extract($a);

		# Label
		$label = self::getLabel($label, $title, $name, $id, $for);

		# Options
		$options_html = self::getOptionsHTML($a);

		# Multiple values allowed?
		$multiple = str::getAttrTag("multiple", $multiple ? "multiple" : false);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, ["input-group mb-3", $disabled_parent_class], $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);

		# Parent style
		$parent_style = str::getAttrTag("style", $parent_style);

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}

		# Class
		$class_array = str::getAttrArray($class, ["form-control", $disabled_class], $only_class);

		# Tokenize
		if($tokenize){
			$class_array[] ='tokenize';
		}

		# Validation
		$validation = self::getValidationTags($validation, $class_array);

		# Class string
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Description
		$desc = self::getDesc($desc);

		# Settings
		$data = self::getSelectData($a);

		# Script (using $a because it may have changed in getSelectData())
		$script = str::getScriptTag($a['script']);

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
		{$data}
	>
	{$options_html}
	</select>
	{$desc}
</div>
{$script}
EOF;
	}

	private static function getSelectData (array &$a): string
	{
		extract($a);
		$class_array = str::getAttrArray($class, "select2js", $only_class);
		$settings['containerCssClass'] = str::getAttrTag(false, $class_array);
		$settings['placeholder'] = self::getPlaceholder($placeholder);
		$settings['ajax'] = $ajax;
		$settings['value'] = $value;
		$settings['tags'] = $tags; // Allows a user to enter their own value
		return str::getDataAttr([
			"settings" => $settings,
			"parent" => $parent,
			"onChange" => self::getOnChange($a)
		]);
	}

	/**
	 * Gets an options array, generates HTML,
	 * and returns it.
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private static function getOptionsHTML ($a)
	{
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

		if (empty($options_array)) {
			return false;
		}

		foreach ($options_array as $option) {
			$value = str::getAttrTag("value", $option['value']);
			$selected = $option['selected'] ? " selected" : NULL;
			$disabled = $option['disabled'] ? " disabled" : NULL;
			$options_html .= "<option{$value}{$selected}{$disabled}>{$option['title']}</option>";
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
	 * @return array|bool
	 */
	private static function getOptionsArray ($a, &$matched = false)
	{
		extract($a);

		/**
		 * Because of the potentially non-distinct
		 * nature of tokenized options, we can trust
		 * that the tokenization methods will have already
		 * prepared the options array in the required
		 * value/title/selected format.
		 */
		if($tokenize){
			if(str::isNumericArray($options)){
				return $options;
			}
		}

		if (is_array($value)) {
			//Many values
			$value_array = $value;
		} else if ($value || "0" == (string) $value) {
			//One value (and that value could be "0" or 0
			$value_array = [$value];
		} else {
			//No value(s)
			$value_array = [];
		}

		if (!is_array($options)) {
			return false;
		}

		foreach ($options as $option_value => $option) {
			if (is_array($option)) {
				$option_title = $option['title'];
				$disabled = $option['disabled'];
			} else {
				$option_title = $option;
				$disabled = NULL;
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
				"disabled" => $disabled,
			];
		}

		return $options_array;
	}
}