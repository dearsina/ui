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
	public static function generateHTML(array $a)
	{
		extract($a);

		# Label
		$label = self::getLabel($label, $title, $name, $id, $for);

		# Options
		$options_html = self::getOptionsHTML($a);

		# Multiple values allowed?
		$multiple = str::getAttrTag("multiple", $multiple ? "multiple" : false);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, ["input-group mb-3", $disabled_parent_class],
			$only_parent_class);
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

		# Validation
		self::setValidationData($a, $class_array);

		# Set dependency data
		self::setDependencyData($a);

		# Class string
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Description
		$desc = self::getDesc($desc);

		# Other
		$other = self::getOther($a);

		# Name
		$name = self::otherValueSelected($a) ? NULL : $name;
		// If an "other" value is selected, remove the name from the main select

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
		{$data}
	>
	{$options_html}
	</select>
	{$desc}
	{$other}
</div>
{$script}
EOF;
	}

	private static function otherValueSelected(array $a): bool
	{
		extract($a);

		if(!$other){
			return false;
		}

		if($multiple){
			// Other is not available for multi-select dropdowns
			return false;
		}

		# Get the select-options array (formatted)
		if(!$options_array = self::getOptionsArray($a)){
			return false;
		}

		# Get the selected value if it's an "other" value
		if(strlen($value) && !in_array($value, array_column($options_array, "value"))){
			//if the selected value is not from one of the dropdown options, it must be the "other" value
			return true;
		}

		return false;
	}

	/**
	 * Generate and return the "other" input field
	 * that will sit right under the select, if required.
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	private static function getOther(array $a): ?string
	{
		extract($a);

		if(!$other){
			return NULL;
		}

		if($multiple){
			// Other is not available for multi-select dropdowns
			return NULL;
		}

		# Get the selected value if it's an "other" value
		if(self::otherValueSelected($a)){
			//if the selected value is not from one of the dropdown options, it must be the "other" value
			$other_value = $value;
		}

		# If there isn't an "other" value, hide the field
		else {
			$grand_parent_class = "d-none";
			$disabled = true;
			$name = NULL;
		}

		# Create the "other" input field array to generate the HTML
		$other_a = [
			"id" => "{$id}-other",
			"name" => $name,
			"grand_parent_class" => $grand_parent_class,
			"grand_parent_style" => [
				"margin-top" => "0.5rem",
				"width" => "100%",
			],
			"label" => false,
			"placeholder" => false,
			"pre" => $other,
			"value" => $other_value,
			"required" => "As you've selected <i>{$other}</i>, you must enter a value here.",
			// "Other" input fields are always required (otherwise, why select "other"?)
			"disabled" => $disabled,
		];

		# Return the HTML
		return Input::getHTML($other_a);
	}

	private static function getSelectData(array &$a): string
	{
		extract($a);
		$class_array = str::getAttrArray($class, "select2js", $only_class);
		$settings['containerCssClass'] = str::getAttrTag(false, $class_array);
		$settings['placeholder'] = self::getPlaceholder($placeholder);
		$settings['ajax'] = $ajax;
		$settings['value'] = $value;
		$settings['tags'] = $tags; // Allows a user to enter their own value

		# Tokenize
		if($tokenize){
			$settings['tags'] = true;
			if(is_string($tokenize)){
				switch($tokenize) {
				case 'filename':
					$settings['createTag'] = "createTagFileName";
					break;
				}
			}
		}

		return str::getDataAttr(array_merge($a['data'] ?: [], [
			"other" => $other,
			"settings" => $settings,
			"parent" => $parent,
			"onChange" => self::getOnChange($a),
		]));
	}

	/**
	 * Gets an options array, generates HTML,
	 * and returns it.
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private static function getOptionsHTML(array &$a): ?string
	{
		extract($a);

		# Get the options array (formatted)
		if(!$options_array = self::getOptionsArray($a)){
			return NULL;
		}

		# Add a blank option if needed
		if(!$multiple && !array_filter($options_array ?: [], function($option){
				return $option['selected'];
			})){
			/**
			 * If no options match on the value,
			 * and it's _not_ a multiple situation,
			 * add a blank option that's set as
			 * selected.
			 *
			 * This way, the dropdown defaults to
			 * the placeholder.
			 */
			$options_array[] = [
				"value" => "",
				"title" => "",
				"selected" => true,
			];
		}

		foreach($options_array as $option){
			$data = str::getDataAttr(array_merge([
				"onChange" => self::getOnChange($option),
			], $option['data'] ?: []));

			if($option['script']){
				$a['script'] .= $option['script'];
			}
			/**
			 * This is a bit of a hack as the getOnChange method
			 * adds a script to the array sent to it, because
			 * it is usually the $a array. As in this case, we're
			 * adding onChange to each *option* of an $a form field,
			 * we have to add the created script back to the $a,
			 * for it then to be displayed (and callable) if the
			 * related option is selected.
			 */

			$value = str::getAttrTag("value", $option['value']);
			$selected = $option['selected'] ? " selected" : NULL;
			$disabled = $option['disabled'] ? " disabled" : NULL;
			$options_html .= "<option{$value}{$selected}{$disabled}{$data}>{$option['title']}</option>";
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
	private static function getOptionsArray($a): ?array
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

		# If there are no options, pencils down
		if(!is_array($options)){
			return NULL;
		}

		# Set the value(s) as an array
		$value_array = array_filter(is_array($value) ? $value : [$value]);

		# Go through each option, format the data and add it to the array
		foreach($options as $option_value => $option){
			# Check to see if the option is selected or not
			$selected = in_array($option_value, $value_array ?: []);

			if(!$multiple && $option_value == $other){
				# Check if the $value_array contains any values that aren't in the $options array
				if($value_array && array_diff($value_array ?: [], array_keys($options))){
					# If so, set the $other value as selected
					$selected = true;
				}
			}

			# If the option is an array
			if(is_array($option)){
				$select_option = new SelectOption($option_value, $option, $selected);
				$options_array[] = $select_option->getOption();
				continue;
			}

			# If the option is a string
			$options_array[] = [
				"value" => $option_value,
				"title" => $option,
				"selected" => $selected,
			];
		}

		return $options_array;
	}
}