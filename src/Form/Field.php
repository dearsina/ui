<?php


namespace App\UI\Form;

use App\Common\href;
use App\Common\str;
use App\UI\Badge;
use App\UI\Button;
use App\UI\Icon;


/**
 * Class Field
 *
 * The Field() class is mostly a base class
 * and a junction between Form() and the
 * different field type classes.
 *
 * It does give each field an ID, if one hasn't
 * been given to it.
 *
 * In addition, some common methods are stored here,
 * so that they don't need to be repeated for each
 * field type class that all extend from Field().
 *
 * @package App\UI\Form
 */
class Field {
	/**
	 * @param array|null $field
	 * @link https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill
	 * @return bool|string
	 */
	public static function getHTML(array $field = NULL){
		if(!$field){
			return false;
		}

		# Evert field must have an ID
		$field['id'] = $field['id'] ?: str::id($field['type']);

		# Required is a attribute that needs to be translated if present
		$field = self::getRequiredValidation($field);

		# Potential class name (based on the field type)
		$class = "App\\UI\\Form\\".ucfirst($field['type']);

		if(class_exists($class)){
			//If a custom input type field exists
			return $class::generateHTML($field);
		} else {
			//If a custom field type class doesn't exist
			return Input::generateHTML($field);
			//Use the default "input" type
		}
	}
	
	/**
	 * Given a string, returns a corrected type
	 * for the input field type as an array.
	 * Some types will be switched to less intuitive names.
	 * At times other factors that may be influenced
	 * by the type.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	static function getInputType($type){
		switch($type){
		case 'datetime':
			$a['type'] = 'datetime-local';
			//to ensure that timezones are correct
			break;
		case 'float':
			$a['type'] = "number";
			$a['step'] = 0.01;
			break;
		case 'int':
			$a['type'] = "number";
			break;
		default:
			$a['type'] = $type;
		}
		return $a;
	}

	/**
	 * Expects a label, could be either a string,
	 * or an array (for more complex labels),
	 * and the ID of the field this label is for.
	 *
	 * @param array|string	$label
	 * @param string 		$name
	 * @param int|string	$id
	 *
	 * @return bool|string
	 */
	static function getLabel($label, $name, $id){
		if($label === false){
			return false;
		}

		if(is_array($label)) {
			$l = $label;
		} else if ($label){
			$l['title'] = $label;
		} else {
			$l = [];
		}

		# If no icon, title, badge or HTML has been supplied, use the name
		if(!count(array_intersect(["title", "icon", "html", "badge"], array_keys($l)))){
			$l['title'] = $name;
		}

		if($l['title']){
			$title = "<b>".str::title($l['title'])."</b>";
		}
		
		# Class
		$class_array = str::getAttrArray($l['class'], "field-label", $l['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $l['style']);

		# Icon
		$icon = Icon::generate($l['icon']);

		# Badge
		$badge = Badge::generate($l['badge']);

		# Link
		if($href = href::generate($l)){
			//if the label is a link
			$tag = "a";
		} else {
			$tag = "label";
		}

		# Id
		$id = str::getAttrTag("for", $id);

		# HTML
		$html = $l['html'];

		# Description
		$desc = self::getDesc($l['desc']);

		return "<{$tag}{$href}{$id}{$class}{$style}>{$icon}{$title}{$html}{$badge}{$desc}</{$tag}>";
	}
	
	static function getCheckboxLabel($label, $desc, $name, $id){
		if(is_array($label)) {
			$l = $label;
		} else if ($label){
			$l['title'] = $label;
		} else {
			$l = [];
		}

		# Embedd the desc into the label
		$l['desc'] = $l['desc'] ?: $desc;

		# If no icon, title, badge or HTML has been supplied, use the name
		if(!count(array_intersect(["title", "icon", "html", "badge"], array_keys($l)))){
			$l['title'] = $name;
		}

		# Title (is formatted)
		$title = $l['title'] ? str::title($l['title']) : false;

		# HTML (is NOT formatted)
		$html = $l['html'];

		# Class
		$class_array = str::getAttrArray($l['class'], "field-label", $l['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $l['style']);

		# Icon
		$icon = Icon::generate($l['icon']);

		# Badge
		$badge = Badge::generate($l['badge']);

		# Link
		if($href = href::generate($l)){
			//if the label is a link
			$tag = "a";
		} else {
			$tag = "label";
		}

		# Id
		$id = str::getAttrTag("for", $id);

		# Description
		$desc = self::getDesc($l['desc']);

		return "<{$tag}{$href}{$id}{$class}{$style}>{$icon}{$title}{$html}{$badge}{$desc}</{$tag}>";
		
	}

	/**
	 * Return the placeholder,
	 * or return the field name.
	 *
	 * @param      $placeholder
	 * @param null $name
	 *
	 * @return array|bool|mixed|string
	 */
	static function getPlaceholder($placeholder, $name = NULL){
		if($placeholder === false){
			return false;
		}
		if($placeholder){
			return trim(strip_tags($placeholder));
		}
		if(!$name){
			return false;
		}
		return str::title(trim(strip_tags($name)));
	}

	/**
	 * Translates the validation tree to tags.
	 *
	 * @param $validation
	 *
	 * @return bool|string
	 */
	static function getValidationTags($validation){
		if(!is_array($validation)){
			return false;
		}

		foreach($validation as $rule => $val){
			if(is_array($val)){
				$data["data-rule-{$rule}"] = $val['rule'];
				$data["data-msg-{$rule}"] = $val['msg'];
			} else {
				$data["data-rule-{$rule}"] = $val;
			}
		}

		foreach($data as $key => $val){
			$tag_array[] = str::getAttrTag($key, $val);
		}

		return implode(" ", $tag_array);
	}

	/**
	 * Translates the required attribute
	 * to a more complex validation array tree.
	 *
	 * @param $a
	 *
	 * @return mixed
	 */
	static function getRequiredValidation($a){
		if(!$a['required']){
			return $a;
		}

		$a['validation']["required"] = [
			"rule" => true,
			"msg" => is_string($a['required']) ? $a['required'] : false
		];
		return $a;
	}

	static function getButton($button, $type){
		if(!$button){
			//if no button has been requested
			return false;
		}

		$button_array = is_array($button) ? $button : ["name", $button];

		if(!$type || $type=="input"){
			$button_array['style']['z-index'] = "9";
			$button_array['style']['border-radius'] = "0 .25rem .25rem 0";
		}

		$button_array['style']['border-top-right-radius'] = "unset";
		$button_array['style']['border-bottom-right-radius'] = "unset";
		$button_array['style']['border-right'] = "none";

		return Button::generate($button_array);
	}

	static function getDesc($desc){
		if(!$desc){
			return false;
		}

		if(is_array($desc)){
			extract($desc);
		} else {
			$html = $desc;
		}

		$class_array = str::getAttrArray($class, ["form-text", "text-muted"], $only_class);
		$class_tag = str::getAttrTag("class", $class_array);
		$style_tag = str::getAttrTag("style", $style);

		return "<small{$class_tag}{$style_tag}>{$html}</small>";
	}

	/**
	 * Icon for input fields.
	 * 
	 * @param $icon
	 */
	static function getIcon($icon, $prepend_or_append = "prepend"){
		if(!$icon){
			//Icons are not mandatory
			return false;
		}

		if(str::isNumericArray($icon)){
			//only a single icon is allowed
			return false;
		}

		if(!is_array($icon)){
			$icon_array = ["name" => $icon];
		} else {
			$icon_array = $icon;
		}

		$icon_html = Icon::generate($icon_array);

		$icon_title = $icon_array['title']? " {$icon_array['title']}" : false;

		$colour = str::getColour($icon_array['colour']);
		$span_class_array = str::getAttrArray($colour, "input-group-text");
		$span_class_tag = str::getAttrTag("class", $span_class_array);

		return "<div class=\"input-group-{$prepend_or_append}\"><span{$span_class_tag}>{$icon_html}{$icon_title}</span></div>";
	}
}