<?php


namespace App\UI\Form;

use App\Common\href;
use App\Common\SQL\Factory;
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
	 * @param array|null $a
	 *
	 * @return bool|string
	 * @link https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill
	 */
	public static function getHTML(array &$a = NULL)
	{
		if(!$a){
			return false;
		}

		$field = $a;

		/**
		 * As this variable is being fed to the Grid,
		 * we need to strip away a lot of attributes
		 * we don't want double applied (both here,
		 * and in the Grid column).
		 */
		unset($a['id']);
		unset($a['icon']);
		unset($a['class']);
		unset($a['style']);

		# Evert field must have an ID
		$field['id'] = $field['id'] ?: str::id($field['type']);

		# Required is an attribute that needs to be translated if present
		$field = self::getRequiredValidation($field);

		# Potential class name (based on the field type)
		$class = str::getClassCase("App\\UI\\Form\\{$field['type']}");

		if(class_exists($class)){
			//If a custom input type field exists
			return $class::generateHTML($field);
		}
		else {
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
	static function getInputType($type)
	{
		switch($type) {
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
	 * @param array|string    $label
	 * @param string          $title Not quite sure what this is for.
	 * @param string          $name
	 * @param int|string      $id
	 * @param int|string|null $for   If set, will be the ID that this element's label is "for"
	 * @param bool|null       $all   If set, will add a checkbox so that all values can be selected. Only used by
	 *                               checkboxes.
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	static function getLabel($label, $title, $name, $id, ?string $for = NULL, ?bool $all = NULL)
	{
		if($label === false){
			return false;
		}

		if(is_array($label)){
			$l = $label;
		}
		else if($label){
			$l['title'] = $label;
		}
		else if($title){
			$l['title'] = $title;
		}
		else {
			$l = [];
		}

		# If no icon, title, badge or HTML has been supplied, use the name
		if(!count(array_intersect(["title", "icon", "html", "badge"], array_keys($l)))){
			$l['title'] = str::title($name);
		}

		if($l['title']){
			$title = "<b>{$l['title']}</b>";
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
		}
		else {
			$tag = "label";
		}

		# Id
		$id = str::getAttrTag("for", $for ?: $id);

		# HTML
		$html = $l['html'];

		# Description
		$desc = self::getDesc($l['desc']);

		# Toggle the all button
		if($all){
			$toggle_all = "<input type=checkbox class=\"form-check-input checkbox-all\" title=\"Toggle all\">";
		}

		return "<{$tag}{$href}{$id}{$class}{$style}>{$toggle_all}{$icon}{$title}{$html}{$badge}{$desc}</{$tag}>";
	}

	/**
	 * @param $label
	 * @param $desc
	 * @param $name
	 * @param $id
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function getCheckboxLabel($label, $desc, $name, $id)
	{
		if($label === false){
			$l['title'] = "";
		}
		else if(is_array($label)){
			$l = $label;
		}
		else if($label){
			$l['html'] = $label;
		}
		else {
			$l = [];
		}

		# Embed the desc into the label
		$l['desc'] = $l['desc'] ?: $desc;

		# If no icon, title, badge or HTML has been supplied, use the name
		if(!count(array_intersect(["title", "icon", "html", "badge"], array_keys($l)))){
			$l['title'] = str::title($name);
		}

		# Title (is formatted and bolded)
		$title = $l['title'] ? "<b>" . $l['title'] . "</b>" : false;

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
		}
		else {
			$tag = "label";
		}

		# Alt
		$alt = str::getAttrTag("title", $l['alt']);

		# Id
		$id = str::getAttrTag("for", $id);

		# Description
		$desc = self::getDesc($l['desc']);

		return "<{$tag}{$href}{$id}{$class}{$style}{$alt}>{$icon}{$title}{$html}{$badge}{$desc}</{$tag}>";

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
	static function getPlaceholder($placeholder, $name = NULL)
	{
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
	 * Translates the validation tree to data tags.
	 * Will also add a class to the class array if validation is to be ignored.
	 *
	 *
	 * @return bool|string
	 */
	static function setValidationData(?array &$a, ?array &$class = []): void
	{

		if($a['validation'] === false){
			//if validation is to be ignored for this field
			$class[] = "ignore-validation";
			return;
		}

		if(!is_array($a['validation'])){
			return;
		}

		foreach($a['validation'] as $rule => $val){
			if(is_array($val)){
				$a['data']["rule-{$rule}"] = $val['rule'];
				$a['data']["msg-{$rule}"] = $val['msg'];
			}

			else if($val){
				$a['data']["rule-{$rule}"] = $val;
			}
		}
	}

	/**
	 * Qualifier    Type     Description
	 * enabled      Boolean  If true, then dependency must not have the "disabled" attribute.
	 * checked      Boolean  If true, then dependency must not have the "checked" attribute.
	 *                       Used for checkboxes and radio buttons.
	 * values       Array    Dependency value must equal one of the provided values.
	 * not          Array    Dependency value must not equal any of the provided values.
	 * match        RegEx    Dependency value must match the regular expression.
	 * contains     Array    One of the provided values must be contained in an array of dependency values.
	 *                       Used for select fields with the "multiple" attribute.
	 * email        Boolean  If true, dependency value must match an email address.
	 * url          Boolean  If true, Dependency value must match a URL.
	 * function     String   Name of a custom function which returns true or false.
	 *
	 * value        Boolean     True matches on any value, false matches on any empty
	 * < <= > >=    Float    Mathematical equations
	 *
	 * Full format
	 * <code>
	 * "dependency" => [
	 * 	"and" => [[
	 * 		"selector" => "service",
	 * 		"checked" => $service['service_id']
	 * 	]],
	 * 	"settings" => [
	 * 		"wrapper" => false,
	 * 	]
	 * ]
	 * </code>
	 *
	 * Quick format
	 * <code>
	 * "dependency" => [
	 *    "col_name" => [
	 *        "checked" => true
	 *    ]
	 * ]
	 * </code>
	 * @link https://dstreet.github.io/dependsOn
	 *
	 * @param array|null $a
	 */
	static function setDependencyData(?array &$a): void
	{
		if(!is_array($a['dependency'])){
			return;
		}

		# If the full format is expressed
		if($a['dependency']['and'] || $a['dependency']['or']){
			$a['data']['dependency'] = $a['dependency'];
			return;
		}

		# If the quick format is expressed
		foreach($a['dependency'] as $selector => $condition){
			# Failsafe, shouldn't happen
			if(!is_array($condition)){
				continue;
			}
			$condition['selector'] = $selector;
			$a['data']['dependency']['and'][] = $condition;
		}
	}

	/**
	 * Translates the required attribute
	 * to a more complex validation array tree.
	 *
	 * @param $a
	 *
	 * @return mixed
	 */
	static function getRequiredValidation($a)
	{
		if(!$a['required']){
			return $a;
		}

		$a['validation']["required"] = [
			"rule" => true,
			"msg" => is_string($a['required']) ? $a['required'] : false,
		];
		return $a;
	}

	/**
	 * @param $button
	 * @param $type
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	static function getButton($button, $type)
	{
		if(!$button){
			//if no button has been requested
			return false;
		}

		$button_array = is_array($button) ? $button : ["name", $button];

		if(!$type || $type == "input"){
			$button_array['style']['z-index'] = "9";
			$button_array['style']['border-radius'] = "0 .25rem .25rem 0";
		}

		$button_array['style']['border-top-right-radius'] = "unset";
		$button_array['style']['border-bottom-right-radius'] = "unset";
		$button_array['style']['border-right'] = "none";

		return Button::generate($button_array);
	}

	/**
	 * @param $desc
	 *
	 * @return bool|string
	 */
	static function getDesc($desc)
	{
		if(!$desc){
			return false;
		}

		if(is_array($desc)){
			extract($desc);
			$html = $desc;
		}
		else {
			$html = $desc;
		}

		$class_array = str::getAttrArray($class, ["label-desc", "form-text", "text-muted"], $only_class);
		$class_tag = str::getAttrTag("class", $class_array);
		$style_tag = str::getAttrTag("style", $style);

		return "<small{$class_tag}{$style_tag}>{$html}</small>";
	}

	/**
	 * Icon for input fields.
	 *
	 * @param        $icon
	 * @param string $prepend_or_append
	 *
	 * @return bool|string
	 * @return bool|string
	 */
	static function getIcon($icon)
	{
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
		}
		else {
			$icon_array = $icon;
		}

		# Make the icons thin
		$icon_array['type'] = "thin";

		$icon_html = Icon::generate($icon_array);

		$icon_title = $icon_array['title'] ? " {$icon_array['title']}" : false;

		$colour = str::getColour($icon_array['colour']);
		$span_class_array = str::getAttrArray($colour, "input-group-text");
		$span_class_tag = str::getAttrTag("class", $span_class_array);

		return "<span{$span_class_tag}>{$icon_html}{$icon_title}</span>";
		//		return "<div class=\"input-group-{$prepend_or_append}\"><span{$span_class_tag}>{$icon_html}{$icon_title}</span></div>";
	}

	/**
	 * Given a select query, and the names of the id and title columns,
	 * run the query, if results are found, return the results in an id=>title array.
	 *
	 * @param mixed       $select
	 * @param string      $id_column
	 * @param string|null $title_column
	 *
	 * @return array
	 */
	public static function getOptions($select, string $id_column, ?string $title_column = NULL): array
	{
		if(!$title_column){
			$title_column = $id_column;
		}

		$sql = Factory::getInstance();

		if(!$rows = $sql->select($select)){
			return [];
		}

		foreach($rows as $row){
			$options[$row[$id_column]] = $row[$title_column];
		}

		return $options;
	}

	/**
	 * If this element has an onChange script,
	 * create a wrapper for it and in conjunction with the
	 * form.js script, attach the wrapper to a trigger.
	 *
	 * @param array $a
	 *
	 * @return bool|string
	 */
	public static function getOnChange(array &$a)
	{
		extract($a);

		$on = $onChange ?: $onchange;

		# A hash array can be fed to the onChange key
		if(is_array($on)){
			$rel_table = $on['rel_table'] ? "\"{$on['rel_table']}\"" : "null";
			$rel_id = $on['rel_id'] ? "\"{$on['rel_id']}\"" : "null";
			$action = $on['action'] ? "\"{$on['action']}\"" : "null";
			if($on['vars']){
				$json = substr(json_encode($on['vars']), 1, -1);
			}
			$vars = "{\"{$name}\": $(this).val(), $json}";
			$script = "ajaxCall({$action}, {$rel_table}, {$rel_id}, {$vars});";
		}

		# Otherwise a script
		else {
			$script = $on;
		}


		if(!$script){
			return false;
		}

		# Generate an arbirary name for the onChange function
		$id = str::id("function");

		# Append the function to the script key
		$a['script'] .= <<<EOF
function {$id}(e){
	{$script}
}
EOF;
		# Return the arbitrary function name so that it's added the change listener
		return $id;
	}

	/**
	 * Looks for an optional data key. Merges it with an
	 * optional onChange key. Updates the script key,
	 * must be run before the getScriptTag method, like so:
	 * <code>
	 * # Data
	 * $data = self::getData($a);
	 *
	 * # Script
	 * $script = str::getScriptTag($a['script']);
	 * //$a['script'] may be updated by getData
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	public static function getData(array &$a): ?string
	{
		extract($a);
		$data['onChange'] = self::getOnChange($a);
		return str::getDataAttr($data, true);
	}
}