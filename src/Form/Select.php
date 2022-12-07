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

		# Tokenize
		if($tokenize){
			$settings['tags'] = true;
			if(is_string($tokenize)){
				switch($tokenize){
				case 'filename':
					$settings['createTag'] = "createTagFileName";
					break;
				}
			}
		}
		
		return str::getDataAttr(array_merge($a['data'] ?:[], [
			"settings" => $settings,
			"parent" => $parent,
			"onChange" => self::getOnChange($a)
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
	private static function getOptionsHTML (array &$a): ?string
	{
		extract($a);
		$matched = false;
		$options_array = self::getOptionsArray($a, $matched);
		if (!$matched && !$multiple) {
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
				"selected" => true
			];
		}

		if (empty($options_array)) {
			return NULL;
		}

		foreach ($options_array as $option) {
			$data = str::getDataAttr(array_merge([
				"onChange" => self::getOnChange($option)
			], $option['data'] ?:[]));

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
			# Is *this* option selected?
			if (in_array($option_value, $value_array ?:[])) {
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

			# If the option is an array
			if(is_array($option)){
				$option['value'] = $option_value;
				$option['selected'] = $selected;
				$options_array[] = $option;
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