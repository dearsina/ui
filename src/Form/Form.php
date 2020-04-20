<?php


namespace App\UI\Form;


use App\Common\Hash;
use App\Common\str;
use App\UI\Button;
use App\UI\Grid;

class Form {
	/**
	 * The ID of the form
	 * @var string
	 */
	private $id;

	/**
	 * @var Hash
	 */
	private $hash;

	/**
	 * @var array
	 */
	private $fields;

	/**
	 * @var array
	 */
	private $buttons;

	/**
	 * @var string
	 */
	private $script;

	/**
	 * @var array
	 */
	private $class;

	/**
	 * @var array
	 */
	private $style;

	/**
	 * Form constructor.
	 * <code>
	 * $form = new Form([
	 * 	"action" => "insert",
	 * 	"rel_table" => NULL,
	 * 	"rel_id" => NULL,
	 * 	"fields" => $fields,
	 * 	"buttons" => $buttons
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct ($a = NULL) {
		$this->hash = Hash::getInstance();

		# Empty form
		if(!is_array($a)){
			return true;
		}

		extract($a);

		# Form ID
		$this->setId($id);
		// Every form must have an ID

		# Meta fields
		$this->action = $action;
		$this->rel_table = $rel_table;
		$this->rel_id = $rel_id;

		# Callback
		$this->hash->setCallback($callback);
		
		# Fields
		$this->setFields($fields);
		
		# Buttons
		$this->setButtons($buttons);

		# Script
		$this->setScript($script, $only_script);

		# Class
		$this->class = str::getAttrArrray($class, [], $only_class);

		# Style
		$this->style = str::getAttrArrray($style, []);

		return true;
	}

	/**
	 * Sets the default script,
	 * appends scripts, and optionally,
	 * if set, replaces the whole script
	 * with only_script.
	 *
	 * @param string $script
	 * @param string $only_script
	 *
	 * @return bool
	 */
	function setScript($script, $only_script) {
		$this->script = /** @lang ECMAScript 6 */<<<EOF

var {$this->getId()}_form_is_valid = $("#{$this->getId()}").validate(validationSettings);
$('#{$this->getId()}').submit(function(event){
    event.preventDefault();
    if({$this->getId()}_form_is_valid.form()){
		submitForm(event, "{$this->getId()}");        
    } else {
        Ladda.stopAll();
    }	
});

EOF;
		if($script){
			$this->script .= $script;
		}

		if($only_script){
			$this->script = $only_script;
		}

		return true;
	}

	/**
	 * Add buttons to the form.
	 * Can be a single button.
	 *
	 * @param $buttons
	 *
	 * @return bool
	 */
	public function setButtons($buttons){
		if (!$buttons) {
			return true;
		}
		if(str::isNumericArray($buttons)){
			foreach($buttons as $button){
				$this->buttons[] = $button;
			}
		} else {
			$this->buttons[] = $buttons;
		}
		return true;
	}

	/**
	 * Add fields to the form.
	 * Can be a single field,
	 * or a numeric array of many fields
	 *
	 * @param $fields
	 *
	 * @return bool
	 */
	public function setFields($fields){
		if($fields === FALSE){
			$this->fields = [];
			return  true;
		}

		if (!$fields) {
			return true;
		}

		if(str::isNumericArray($fields)){
			foreach($fields as $field){
				$this->fields[] = $field;
			}
		} else {
			$this->fields[] = $fields;
		}
		
		return true;
	}

	private function setId($id){
		$this->id = $id ?: str::id("form");
		return $this->id;
	}

	/**
	 * Returns the ID either as only the ID,
	 * or as a tag.
	 *
	 * @param bool $tag If set to TRUE will return the id as a tag.
	 *
	 * @return string
	 */
	function getId ($tag = NULL) {
		if ($tag){
			return str::getAttrTag("id", $this->getId());
		}
		return $this->id ?: $this->setId();
	}

	/**
	 * Fields are generated using the Field() class,
	 * and placed in a grid using the Grid() class.
	 *
	 * This method is merely a junction method.
	 *
	 * @param null $fields
	 *
	 * @return bool|string
	 */
	function getFieldsHTML($fields = NULL){
		$this->setFields($fields);

		$grid = new Grid([
			"formatter" => function($field){
				return Field::getHTML($field);
			}
		]);
		return $grid->getHTML($this->fields);
	}

	function getButtonsHTML(){
		if (!$this->buttons) {
			return false;
		}

		if(str::isNumericArray($this->buttons)){
			foreach($this->buttons as $button){
				$buttons_html[] = Button::generate($button);
			}
		} else {
			$buttons_html[] = Button::generate($this->buttons);
		}

		return implode("&nbsp;", $buttons_html);
	}

	function getScriptHTML(){
		return str::getScriptTag($this->script);
	}

	/**
	 * Returns the form as HTML.
	 *
	 * @return string
	 */
	public function getHTML(){
		
		$id = str::getAttrTag("id", $this->id);
		$class = str::getAttrTag("class", $this->class);
		$style = str::getAttrTag("style", $this->style);

		return /** @lang HTML */ <<<EOF
<form method="POST"{$id}{$class}{$style}>
	<input type="hidden" name="meta_action" value="{$this->action}"/>
	<input type="hidden" name="meta_rel_table" value="{$this->rel_table}"/>
	<input type="hidden" name="meta_rel_id" value="{$this->rel_id}"/>
	<input type="hidden" name="callback" value="{$this->hash->getCallback()}"/>
	<div class="listing">{$this->getFieldsHTML()}</div>
	{$this->getButtonsHTML()}
</form>
{$this->getScriptHTML()}
EOF;
	}
}