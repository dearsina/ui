<?php


namespace App\UI\Form;


use App\Common\str;

/**
 * Class Checkbox
 * @package App\UI\Form
 */
class Checkbox extends Field implements FieldInterface {
	/**
	 * Generates the HTML for one to many checkboxes.
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generateHTML(array $a)
	{
		if(!is_array($a['values']) && $a['value']){
			$a['values'] = is_array($a['value']) ? $a['value'] : [$a['value']];
		}

		if(is_array($a['options'])){
			return self::getMultiCheckboxHTML($a);
		}

		return self::getSingleCheckboxHTML($a);
	}

	/**
	 * A checkbox or a radio item label can itself be a field.
	 *
	 * @param $val
	 * @param $type
	 *
	 * @param $validation
	 *
	 * @return array
	 */
	private static function getLabelArray($val, $type, $validation)
	{
		# Label is just a string (not an array)
		if(!is_array($val)){
			return ['label' => $val];
		}

		# Label arrays is just attribute array
		if(!$val['type']){
			//No type has been designated
			return $val;
		}

		# The ID of the parent field
		$id = str::id($type);

		# The ID of the child field
		$val['id'] = $val['id'] ?: str::id($val['type']);

		# The label field inherits the validation of the parent
		$val['validation'] = $validation;

		# Adjust the label to fit the field
		$val['parent_style'] = str::getAttrArray($val['parent_style'], $val['default_parent_style'], $val['only_parent_style']); //["margin" => "-1rem 0 0 0"]

		if(!is_array($val['label'])){
			$val['label'] = [
				"title" => $val['label'],
			];
		}

		# Shift the label a bit closer to the field
		$val['label']['style']["margin-bottom"] = $val['label']['style']["margin-bottom"] ?: "-0.3rem";

		return [
			"id" => $id,
			"script" => self::getLabelScript($id, $val['id']),
			'label' => [
				"style" => [
					"width" => "100%",
				],
				"html" => Field::getHTML($val),
			],
		];
	}

	/**
	 * Returns the script that binds the (optional)
	 * radio/checkbox label field with its radio/checkbox parent.
	 *
	 * The script connects the radio/check field and the label field.
	 * It checks the value of the label, and if it's set (on start only),
	 * it will set the related radio/checkbox to checked.
	 *
	 * The assumption here is that if the label field has a value selected,
	 * That option is the "right" one.
	 *
	 * @param string $parent_id
	 * @param string $label_id
	 *
	 * @return string
	 */
	private static function getLabelScript(string $parent_id, string $label_id)
	{
		return /** @lang JavaScript */
			<<<EOF
$(document).ready(function() {
    // Clicking on the radio/check will open the dropdown
	$("#{$parent_id}").on("click",function(){
	    if($('#{$label_id}').is("select")){
			$('#{$label_id}').select2('open');	        
	    }	    
	});
	
	// When the label field value changes, check it, if it has value, add it to the radio/check
	$("#{$label_id}").on("change paste keyup",function(){
	    if(!$("#{$label_id}").attr("name").length){
	        //if the label field DOESN'T have its own name, move the value to the parent radio/check
			$("#{$parent_id}").attr("value",$(this).val());
			if($(this).val().length){
				$("#{$parent_id}").attr('disabled', false);
				$("#{$parent_id}").prop("checked", true);
				$("#{$parent_id}").trigger('change');
			} else {
				$("#{$parent_id}").prop("checked", false);
				$("#{$parent_id}").attr('disabled', true);
			}
	    }
	});
	
	// If the label field _starts_ with a value, make sure the radio/check is checked
	if($("#{$label_id}").val().length){
	    if(!$("#{$label_id}").attr("name").length){
	        //but only if the label field doesn't have a name
	    	$("#{$parent_id}").attr("value",$("#{$label_id}").val());
	    	$("#{$parent_id}").prop("checked", true);
	    	/**
	    	* otherwise, because more than one "independent" (aka has a name)
	    	* label field may have values, the wrong field may be checked.
			*/
		} else if($("#{$label_id}").attr("name") != $("input[name='"+$("#{$parent_id}").attr("name")+"']:checked").val()){
			//TODO Fix it so that label elements that are not selected don't have a value
			console.log($("#{$label_id}").val());
		}
	}
});
EOF;
	}

	/**
	 * One field, with one name,
	 * can consist of several checkboxes.
	 * They share some common attributes.
	 *
	 * @param $a
	 *
	 * @return string
	 * @return string
	 */
	private static function getMultiCheckboxHTML($a)
	{
		extract($a);

		$parent_label = self::getLabel($label, $parent_title, $name, $id);

		if(is_array($parent_desc)){
			if(is_array($parent_desc['class'])){
				$parent_desc['class'][] = "checkbox-parent-desc";
			}
			else {
				$parent_desc['class'] .= "checkbox-parent-desc";
			}
		}
		else {
			$parent_desc = [
				"desc" => $parent_desc,
				"class" => "checkbox-parent-desc",
			];
		}
		$parent_desc = self::getDesc($parent_desc);

		foreach($options as $key => $val){
			# The label can be a string, or a whole separate field
			$val_array = self::getLabelArray($val, $type, $validation);

			# Each option must have a unique ID
			$val_array['id'] = $val_array['id'] ?: str::id($type);

			# If this option has been selected, mark it as checked
			if(!empty($values)){
				$val_array['checked'] = in_array($key, $values) ? "checked" : false;
			}

			# The key holds the value
			$val_array['value'] = $key;

			# Not sure if this will work
			$option_array = array_merge($a, $val_array);

			# Get the HTML for the checkbox
			$options_html .= self::getCheckboxHTML($option_array);
		}

		$parent_script = str::getScriptTag($parent_script);

		return /** @lang HTML */ <<<EOF
<div class="mb-3">
	{$parent_label}{$parent_desc}
	{$options_html}
</div>
{$parent_script}
EOF;

	}

	/**
	 * Returns a single checkbox.
	 *
	 * @param $a
	 *
	 * @return string
	 */
	private static function getSingleCheckboxHTML($a)
	{
		# Value
		$a['value'] = $a['value'] ?: true;
		/**
		 * Value attribute is optional for single checkboxes.
		 * Instead a binary "1" or "" will be used if none
		 * has been set.
		 */

		$html = self::getCheckboxHTML($a);
		return /** @lang HTML */ <<<EOF
<div class="mb-3">
	{$html}
</div>
EOF;
	}

	/**
	 * Generates the HTMl for a single checkbox.
	 *
	 * @param $a
	 *
	 * @return string
	 */
	private static function getCheckboxHTML($a)
	{
		extract($a);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, "form-check", $only_parent_class);
		$parent_class_tag = str::getAttrTag("class", $parent_class_array);

		# Parent style
		$parent_style_tag = str::getAttrTag("style", $parent_style);

		# Checked
		$checked = $checked ? "checked" : false;

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}

		# Class
		$class_array = str::getAttrArray($class, ["form-check-input", $disabled_class], $aonly_class);

		# Validation
		$validation = self::getValidationTags($validation, $class_array);

		# Class tag
		$class_tag = str::getAttrTag("class", $class_array);

		# Style
		$style_tag = str::getAttrTag("style", $style);

		# Label
		$label = self::getCheckboxLabel($label, $desc, $name, $id);

		# $script
		$script = str::getScriptTag($script);

		return /** @lang HTML */ <<<EOF
<div{$parent_class_tag}{$parent_style_tag}>
	<input
		type="{$type}"
		id="{$id}"
		name="{$name}"
		value="{$value}"
		{$class_tag}
		{$style_tag}
		{$disabled}
		{$validation}
		{$checked}
	>{$label}
</div>
{$script}
EOF;

	}
}