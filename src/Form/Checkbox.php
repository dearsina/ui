<?php


namespace App\UI\Form;


use App\Common\str;
use App\UI\Button;
use App\UI\Icon;
use App\UI\Tooltip;

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

		if($a['dropdown']){
			return self::getDropdownCheckboxHTML($a);
		}

		if($a['button']){
			return self::getButtonCheckboxHTML($a);
		}

		if(is_array($a['options'])){
			return self::getMultiCheckboxHTML($a);
		}

		return self::getSingleCheckboxHTML($a);
	}

	private static function getDropdownCheckboxHTML(array $a): string
	{
		extract($a);

		$a['label'] = false;
		$a['dropdown'] = NULL;
		$checkboxes = self::generateHTML($a);

		if(is_string($label)){
			$title = $label;
		}

		else if(is_array($label)){
			$title = $label['title'] ?: $label['html'];
		}

		else {
			$title = str::title($name);
		}

		if($checked){
			$basic = false;
		}

		else if(is_array($value)){
			$basic = false;
		}

		else {
			$basic = true;
		}

		$button = Button::generate([
			"icon" => $icon,
			"title" => "{$title} " . Icon::generate("chevron-down"),
			"alt" => $alt,
			"basic" => $basic,
			"colour" => $colour,
			"data" => [
				"bs-toggle" => "dropdown",
			],
			"ladda" => false,
			"size" => "s",
		]);

		return <<<EOF
<div class="checkbox-dropdown">
	{$button}
	<div class="dropdown-menu checkbox-menu allow-focus">
		{$checkboxes}
	</div>
</div>
EOF;

	}

	/**
	 * A checkbox or a radio item label can itself be a field.
	 *
	 * @param string|array $val
	 * @param string       $type
	 * @param array|null   $validation
	 *
	 * @return array
	 */
	private static function getLabelArray($val, string $type, ?array $validation): ?array
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

		# So that the label field's label is also _for_ the parent
		$val['for'] = $id;

		# The ID of the child field
		$val['id'] = $val['id'] ?: str::id($val['type']);

		# The validations of the parent are inherited by the label field
		unset($validation['minLength'], $validation['maxLength']);
		// Except minlength and maxlength, because they mean different things for different form field types

		# Parent validation can be overwritten by child verification
		$val['validation'] = array_merge( $validation ?: [], $val['validation'] ?: []);

		# Adjust the label to fit the field
		$val['parent_style'] = str::getAttrArray($val['parent_style'], $val['default_parent_style'], $val['only_parent_style']); //["margin" => "-1rem 0 0 0"]

		if(!is_array($val['label'])){
			$val['label'] = [
				"title" => $val['label'],
			];
		}

		return [
			"id" => $id,
			"script" => NULL,
			'label' => [
				"style" => [
					"width" => "100%",
				],
				"html" => Field::getHTML($val),
			],
		];
	}

	private static function generateOneButtonCheckboxHTML(string $name, $key, $a): string
	{
		if(!is_array($a)){
			$a = ["title" => $a];
		}

		# Tooltips
		Tooltip::generate($a);

		extract($a);

		# onChange + data
		$data = self::getInputData($a);

		# Id
		$id = $id ?: str::id("btncheck");

		# Style
		$style_array = str::getAttrArray($style, false, $only_style);

		# What colour is the button?
		$colour = $colour ?: "black";
		//default is a b&w theme

		# Class with override
		$class_array = str::getAttrArray($class, ["btn", "btn-outline-{$colour}"], $only_class);

		# Size
		$class_array[] = Button::getSize($size);

		# Icon
		$icon = Icon::generate($icon);

		$style = str::getAttrTag("style", $style_array);
		$class = str::getAttrTag("class", $class_array);
		$script = str::getScriptTag($script);

		return <<<EOF
<input type="checkbox" class="btn-check" id="{$id}" name="{$name}" autocomplete="off" value="{$key}">
<label{$data}{$class}{$style} for="{$id}">{$icon}{$title}{$script}</label>
EOF;

	}

	private static function getButtonCheckboxHTML(array $a): string
	{
		extract($a);

		if($options){
			if(str::isNumericArray($options)){
				foreach($options as $option){
					$options_html .= self::generateOneButtonCheckboxHTML($name, $option, $option);
				}
			}
			else {
				foreach($options as $key => $value){
					$options_html .= self::generateOneButtonCheckboxHTML($name, $key, $value);
				}
			}
		}

		$parent_label = self::getLabel($label, $parent_title, $name, $id);
		$class_array = str::getAttrArray($parent_class, "btn-group", $only_parent_class);
		$class = str::getAttrTag("class", $class_array);
		$style_array = str::getAttrArray($parent_style, NULL, $only_parent_style);
		$style = str::getAttrTag("style", $style_array);
		$parent_script = str::getScriptTag($parent_script);

		return <<<EOF
{$parent_label}{$parent_desc}
<div{$class}{$style} role="group">{$options_html}</div>
{$parent_script}
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
	 * @throws \Exception
	 */
	private static function getMultiCheckboxHTML($a)
	{
		extract($a);

		$parent_label = self::getLabel($label, $parent_title, $name, $id, NULL, $all);

		$parent_class = str::getAttrArray($parent_class, "mb-3", $only_parent_class);

		if(array_filter($options, function($option){
			return is_array($option) && $option['type'];
		})){
			//if at least _ONE_ of the radio/check fields has a label-field
			$parent_class[] = "label-field";
		}

		if(is_array($parent_desc)){
			if(is_array($parent_desc['class'])){
				$parent_desc['class'][] = "checkbox-parent-desc";
			}
			else {
				$parent_desc['class'] .= "checkbox-parent-desc";
			}
		}

		else if($parent_desc){
			$parent_desc = [
				"desc" => $parent_desc,
				"class" => "checkbox-parent-desc",
			];

			$parent_desc = self::getDesc($parent_desc);
		}

		if($label === false && $all){
			# Toggle the all button
			$all_id = str::id("all");
			$options_html .= <<<EOF
			<div class="form-check">
				<input id="{$all_id}" type=checkbox class="form-check-input checkbox-all" title="Toggle all">
				<label for="{$all_id}" class="field-label"><i>Select all</i></label>
			</div>
EOF;
		}

		foreach($options as $key => $val){
			# The label can be a string, or a whole separate field
			$val_array = self::getLabelArray($val, $type, $validation);

			# Each option must have a unique ID
			$val_array['id'] = $val_array['id'] ?: str::id($type);

			# If this option has been selected, mark it as checked
			if(!is_array($value) && strlen($value)){
				//Applies to radio
				$val_array['checked'] = $key == $value ? "checked" : false;
			}

			if(!empty($values)){
				//Applies to checkbox
				$val_array['checked'] = strlen($key) && in_array((string)$key, $values) ? "checked" : false;
				//if the key is int 0 it will be boolean true in the in_array, so we convert it to string 0, this is no longer an issue in PHP8
			}

			# The key holds the value
			$val_array['value'] = $key;

			# Not sure if this will work
			$option_array = array_merge($a, $val_array);

			# Get the HTML for the checkbox
			$options_html .= self::getCheckboxHTML($option_array);
		}

		$parent_script = str::getScriptTag($parent_script);
		$parent_class = str::getAttrTag("class", $parent_class);

		return /** @lang HTML */ <<<EOF
<div{$parent_class}>
	{$parent_label}{$parent_desc}
	<div>{$options_html}</div>
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
		 * Instead, a binary "1" or "" will be used if none
		 * has been set.
		 */

		# Single checkboxes cannot be placed inline
		$a['inline'] = NULL;

		# Grandparent class
		$grand_parent_class_array = str::getAttrArray($a['grand_parent_class'], "mb-3", $a['only_grand_parent_class']);
		$grand_parent_class = str::getAttrTag("class", $grand_parent_class_array);

		# Grandparent style
		$grand_parent_style = str::getAttrTag("style", $a['grand_parent_style']);

		$html = self::getCheckboxHTML($a);
		return /** @lang HTML */ <<<EOF
<div{$grand_parent_class}{$grand_parent_style}>
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

		# Is the option to be placed inline (horizontally)?
		$form_check_inline = $inline ? " form-check-inline" : NULL;

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, "form-check{$form_check_inline}", $only_parent_class);
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
		self::setValidationData($a, $class_array);

		# Set dependency data
		self::setDependencyData($a);

		# Class tag
		$class_tag = str::getAttrTag("class", $class_array);

		# Style
		$style_tag = str::getAttrTag("style", $style);

		# Label
		$label = self::getCheckboxLabel($label, $desc, $name, $id);

		# $data
		$data = self::getInputData($a);

		# $script
		$script = str::getScriptTag($a['script']);

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
		{$checked}
		{$data}
	>{$label}
</div>
{$script}
EOF;

	}

	private static function getInputData(array &$a): ?string
	{
		extract($a);
		$data['onChange'] = self::getOnChange($a);
		return str::getDataAttr($data);
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
	//	private static function getLabelScript(string $parent_id, string $label_id)
	//	{
	//		return /** @lang JavaScript */
	//			<<<EOF
	//$(document).ready(function() {
	//    // Clicking on the radio/check will open the dropdown
	//    console.log("The parent ID is {$parent_id}");
	//	$("#{$parent_id}").on("click",function(){
	//	    console.log("A parent was clicked on");
	//	    if($("#{$parent_id}").prop("checked")){
	//			console.log("The parent is checked.");
	//
	//	        //Disable all the _other_ label fields for this parent
	//			let parent_name = $("#{$parent_id}").attr("name");
	//			console.log("The parent name is " + parent_name);
	//
	//			$("input[name='"+parent_name+"']").parent().find(".form-control").attr('disabled', true);
	//
	//			$('#{$label_id}').attr('disabled', false);
	//			if($('#{$label_id}').is("select")){
	//				$('#{$label_id}').select2('open');
	//			}
	//	    } else {
	//	        $('#{$label_id}').attr('disabled', true);
	//
	//			validator[$(this).closest("form").attr("id")].resetForm();
	//			$(this).closest("form").valid();
	//	    }
	//	});
	//
	//	// When the label field value changes, check it, if it has value, add it to the radio/check
	//	$("#{$label_id}").on("change paste keyup",function(){
	//	    if(!$("#{$label_id}").attr("name").length){
	//	        //if the label field DOESN'T have its own name, move the value to the parent radio/check
	//			$("#{$parent_id}").attr("value",$(this).val());
	//	    }
	//
	//		if($(this).val().length){
	//			$("#{$parent_id}").attr('disabled', false);
	//			$("#{$parent_id}").prop("checked", true);
	//			$("#{$parent_id}").trigger('change');
	//		} else {
	//			$("#{$parent_id}").prop("checked", false);
	//			$(this).attr('disabled', true);
	//
	//			validator[$(this).closest("form").attr("id")].resetForm();
	//			$(this).closest("form").valid();
	//			//re-validates the form (now that a field has been disabled)
	//		}
	//	});
	//
	//	// If the label field _starts_ with a value, make sure the radio/check is checked
	//	if($("#{$label_id}").val().length){
	//	    if(!$("#{$label_id}").attr("name").length){
	//	        //if the label field doesn't have a name
	//	    	$("#{$parent_id}").attr("value",$("#{$label_id}").val());
	//	    	//feed the child value to the parent
	//		}
	//
	//	    // Either way, check the parent
	//		$("#{$parent_id}").prop("checked", true);
	//	}
	//
	//	else if(!$("#{$parent_id}").prop("checked")){
	//	    	//if the parent field isn't checked, disable the label (child) field
	//			$("#{$label_id}").attr('disabled', true);
	//	}
	//});
	//EOF;
	//	}
}