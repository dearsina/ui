<?php


namespace App\UI\Form;


use App\Common\str;

class Checkbox extends Field implements FieldInterface{
	/**
	 * Generates the HTML for one to many checkboxes.
	 */
	public static function generateHTML (array $a) {
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
	 * @return array
	 */
	private static function getLabelArray($val, $type, $validation){
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

		# The label field inherits the valdation of the parent
		$val['valdation'] = $validation;

		# Adjust the label to fit the field
		$val['parent_style'] = str::getAttrArray($val['parent_style'],["margin" => "-1rem 0 0 0"], $val['only_parent_style']);

		return [
			"id" => $id,
			"script" => self::getLabelScript($id, $val['id']),
			'label' => [
				"style" => [
					"width" => "100%"
				],
				"html" => Field::getHTML($val)
			]
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
	private static function getLabelScript(string $parent_id, string $label_id){
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
	    $("#{$parent_id}").attr("value",$(this).val());
		if($(this).val().length){
		    $("#{$parent_id}").attr('disabled', false);
		    $("#{$parent_id}").prop("checked", true);
		    $("#{$parent_id}").trigger('change');
		} else {
		    $("#{$parent_id}").prop("checked", false);
		    $("#{$parent_id}").attr('disabled', true);
		}
	});
	
	// If the label field _starts_ with a value, make sure the radio/check is checked
	if($("#{$label_id}").val().length){
	    $("#{$parent_id}").attr("value",$("#{$label_id}").val());
		$("#{$parent_id}").prop("checked", true);
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
	 */
	private static function getMultiCheckboxHTML($a){
		extract($a);

		$parent_label = self::getLabel($parent_label, $parent_title, $name, $id);
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

		return /** @lang HTML */<<<EOF
<div class="form-group">
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
	private static function getSingleCheckboxHTML($a){
		# Value
		$a['value'] = $a['value'] ?: true;
		/**
		 * Value attribute is optional for single checkboxes.
		 * Instead a binary "1" or "" will be used if none
		 * has been set.
		 */

		$html = self::getCheckboxHTML($a);
		return /** @lang HTML */<<<EOF
<div class="form-group">
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
	private static function getCheckboxHTML($a){
		extract($a);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, "input-group", $only_parent_class);
		$parent_class_tag = str::getAttrTag("class", $parent_class_array);

		# Parent style
		$parent_style_tag = str::getAttrTag("style", $parent_style);

		# Validation
		$validation = self::getValidationTags($validation);

		# Checked
		$checked = $checked ? "checked" : false;

		# Disabled
		if($disabled){
			$disabled = str::getAttrTag("disabled", "disabled");
			$disabled_class = "disabled";
		}
		
		# Class
		$class_array = str::getAttrArray($class, "magic-{$type}", $aonly_class);
		$class_tag = str::getAttrTag("class", $class_array);
		
		# Style
		$style_tag = str::getAttrTag("style", $style);

		# Label
		$label = self::getCheckboxLabel($label, $desc, $name, $id);

		# $script
		$script = str::getScriptTag($script);

		return /** @lang HTML */<<<EOF
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