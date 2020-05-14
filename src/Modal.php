<?php


namespace App\UI;

use App\Common\Common;
use App\Common\str;

class Modal extends Common {
	private $id;
	private $modalHeader;
	private $modalBody;
	private $modalFooter;
	private $modalPost;

	protected $draggable;
	protected $resizable;
	protected $approve;

	/**
	 * Create a modal
	 * <code>
	 * 	$modal = new modal([
	 * 	"id" => "id",
	 * 	"size" => "xl",
	 * 	"dismissable" => false,
	 * 	"draggable" => true,
	 * 	"resizable" => true,
	 * 	"approve" => true,
	 * 	"header" => [
	 * 		"title" => "",	//If the header is just a string, it's assumed to be this key value
	 * 		"html" => "",
	 * 		"buttons" => [],
	 * 		"button" => []
	 * 	],
	 * 	"body" => [
	 * 		"html" => "", //If the body is just a string, it's assumed to be this key value
	 * 		"class" => [],
	 * 		"style" => [],
	 * 	],
	 * 	"footer" => [
	 * 		"html" => "", //If the footer is just a string, it's assumed to be this key value
	 * 		"class" => [],
	 * 		"style" => [],
	 * 	]
	 * ]);
	 * </code>
	 *
	 * @param array|NULL $a
	 *
	 */
	function __construct ($a = NULL) {
		parent::__construct();

		if(!is_array($a)){
			$this->setId();
			return true;
		}

		$this->setAttr($a);

		return true;
	}

	/**
	 * Generate an ID.
	 * If one is given, use that.
	 *
	 * @param bool|string $id
	 *
	 * @return bool
	 */
	public function setId($id = NULL){
		if($id === false){
			$this->id = false;
			return true;
		}
		$this->id = $id ?: str::id("modal");
		return $this->id;
	}

	/**
	 * Set the modal header.
	 * Will replace existing headers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the header
	 *
	 * @return bool
	 */
	public function setHeader($a = NULL){
		# Array
		if(is_array($a)){
			$this->modalHeader = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->modalHeader['title'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->modalHeader = [];
			return true;
		}

		return true;
	}

	/**
	 * Returns the header as HTML.
	 *
	 * @return bool|string
	 */
	public function getHeaderHTML(){
		if(!is_array($this->modalHeader)){
			// Headers are optional
			return false;
		}

		# Header buttons can also be defined outside of the header key when defining modal vales.
		$this->modalHeader['buttons'] = array_merge($this->modalHeader['buttons']?:[], $this->buttons?:[]);

		# Add the required Bootstrap header class very first
		$this->modalHeader['class'] = str::getAttrArray($this->modalHeader['class'], "modal-header", $this->modalHeader['only_class']);

		# Draggable
		$this->modalHeader['class'][] = $this->draggable ? "modal-header-draggable" : false;

		# Styles
		$this->modalHeader['style'] = str::getAttrArray($this->modalHeader['style'], NULL, $this->modalHeader['only_style']);

		# Dropdown buttons
		if($this->modalHeader['buttons']){
			$buttons = Dropdown::generate($this->modalHeader);
		}

		# Button(s) in a row
		if(str::isNumericArray($this->modalHeader['button'])){
			foreach($this->modalHeader['button'] as $b){
				$button .= Button::generate($b);
			}
		} else if ($this->modalHeader['button']){
			$button = Button::generate($this->modalHeader['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Accent
		$this->modalHeader['class'][] = str::getColour($this->accent, "bg");

		# Icon
		$icon = Icon::generate($this->modalHeader['icon']);

		# Badge
		$badge = Badge::generate($this->modalHeader['badge']);

		# ID
		$id = str::getAttrTag("id", $this->modalHeader['id']);

		# Style
		$style = str::getAttrTag("style", $this->modalHeader['style']);

		# Title colour
		$class[] = str::getColour($this->modalHeader['colour']);

		# Script
		$script = str::getScriptTag($this->modalHeader['script']);

		# The header title itself
		$title = $this->modalHeader['header'].$this->modalHeader['title'].$this->modalHeader['html'];

		# Title class
		if(!empty(array_filter($class))){
			$title_class = str::getAttrTag("class", $class);
			$title = "<span{$title_class}>$title</span>";
		}

		# The div class
		$class = str::getAttrTag("class", $this->modalHeader['class']);

		# If the modal can be dismissed
		if($this->dismissable !== false){
			$dismiss = <<<EOF
<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close this window">
	<span aria-hidden="true">&times;</span>
</button>
EOF;
		}

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto">
    			{$icon}{$title}{$badge}
    		</div>
    		<div class="col">
    			{$buttons}{$button}{$dismiss}
    		</div>
    	</div>
	</div>{$script}
</div>
EOF;
	}

	/**
	 * Returns the footer as HTML.
	 *
	 * @return bool|string
	 */
	public function getFooterHTML(){
		if(!is_array($this->modalFooter)){
			// Footers are optional
			return false;
		}

		# Add the required Bootstrap footer class very first
		$this->modalFooter['class'] = str::getAttrArray($this->modalFooter['class'], "modal-footer", $this->modalFooter['only_class']);

		# Styles
		$this->modalFooter['style'] = str::getAttrArray($this->modalFooter['style'], NULL, $this->modalFooter['only_style']);

		# Dropdown buttons
		if($this->modalFooter['buttons']){
			$buttons = Dropdown::generate($this->modalFooter);
		}

		# Button(s) in a row
		if(str::isNumericArray($this->modalFooter['button'])){
			foreach($this->modalFooter['button'] as $b){
				if(empty($b)){
					continue;
				}
				$button .= Button::generate($b);
			}
		} else if ($this->modalFooter['button']){
			$button = Button::generate($this->modalFooter['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Icon
		$icon = Icon::generate($this->modalFooter['icon']);

		# Badge
		$badge = Badge::generate($this->modalFooter['badge']);

		# ID
		$id = str::getAttrTag("id", $this->modalFooter['id']);

		# Style
		$style = str::getAttrTag("style", $this->modalFooter['style']);

		# Text colour
		$class[] = str::getColour($this->modalFooter['colour']);

		# Draggable
		$class[] = $this->draggable ? "modal-footer-draggable" : false;

		# Script
		$script = str::getScriptTag($this->modalFooter['script']);

		# The div class
		$class = str::getAttrTag("class", $this->modalFooter['class']);

		# Only include left side if it has values (or a custom ID)
		if($left = $icon.$this->modalFooter['html'].$badge || $id){
			$left = "<div class=\"col-auto\">{$left}</div>";
		}

		# Only include right side if it has values (buttons)
		if($right = $buttons.$button){
			$right = "<div class=\"col\">{$right}</div>";
		}

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		{$left}
    		{$right}    		
    	</div>
	</div>{$script}
</div>
EOF;

	}

	/**
	 * Set the modal footer.
	 * Will replace existing footers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the footer
	 *
	 * @return bool
	 */
	public function setFooter($a = NULL){
		# Array
		if(is_array($a)){
			$this->modalFooter = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->modalFooter['html'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->modalFooter = [];
			return true;
		}

		return true;
	}

	/**
	 * Set the modal body.
	 * Will replace existing bodys.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the body
	 *
	 * @return bool
	 */
	public function setBody($a = NULL){
		# Array
		if(is_array($a)){
			$a['id'] = $a['id'] ?: str::id("body");
			$this->body = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->body['html'] = $a;
			$this->body['id'] = str::id("body");
			return true;
		}

		# Clear
		if($a === false){
			$this->body = [];
			return true;
		}

		return true;
	}

	/**
	 * Returns the body of the modal.
	 * Not optional.
	 *
	 * @return bool|string
	 */
	public function getBodyHTML(){
		if(!$this->body){
			return false;
		}

		if(is_array($this->body['html'])){
			$grid = new Grid();
			$this->body['html'] = $grid->getHTML($this->body['html']);
		}

		$id = str::getAttrTag("id", $this->body['id']);
		$class = str::getAttrTag("class", ["modal-body", $this->body['class']]);
		$style = str::getAttrTag("style", $this->body['style']);
		$progress = Progress::generate($this->body['progress']);
		$script = str::getScriptTag($this->body['script']);

		return "<div{$class}{$id}{$style}>{$progress}{$this->body['html']}</div>{$script}";
	}

	/**
	 * Return the ID.
	 *
	 * @param bool $as_tag If set to TRUE return the ID as a HTML tag.
	 *
	 * @return bool|string
	 */
	private function getId($as_tag = NULL){
		$this->id = $this->id ?: $this->setId();
		if($as_tag){
			return str::getAttrTag("id", $this->id);
		}
		return $this->id;
	}

	function getHTML(){
		switch($this->size){
		case 'xs':
		case 's':
		case 'small': $size = "modal-sm"; break;
		case 'lg':
		case 'large': $size = "modal-lg"; break;
		case 'xl': $size = "modal-xl"; break;
		}
		$parent_class_array = str::getAttrArray($this->parent_class, "modal", $this->only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $this->parent_style);
		
		$class_array = str::getAttrArray($this->class, ["modal-dialog","modal-dialog-centered", $size], $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);

		return /** @lang HTML */<<<EOF
<div
	{$this->getId(true)}
	{$parent_class}
	{$parent_style}
>
    <div
    	{$class}
		{$style}
	>
        <div class="modal-content">
        	{$this->getHeaderHTML()}
			{$this->getBodyHTML()}
			{$this->getFooterHTML()}
        </div>
    </div>
    {$this->getScriptHTML(true)}
</div>
EOF;

	}

	/**
	 * Returns all Javascript settings related to the modal.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function getScriptHTML($as_tag = FALSE){
		# Modal settings
		$modal['show'] = true;
		if($this->dismissable === false){
			$modal['backdrop'] = "static";
		}

		$settings = [
			"settings" => $modal,
			"draggable" => $this->getDraggableSettings(),
			"resizable" => $this->getResizableSettings(),
			"approve" => $this->getApproveSettings(),
		];

		$settings_json = json_encode($settings, JSON_PRETTY_PRINT);

		$script = /** @lang JavaScript */<<<EOF
$.modal["{$this->getId()}"] = {$settings_json}
showModal("{$this->getId()}");
{$this->script}
EOF;

		if($as_tag){
			return str::getScriptTag($script);
		}
		return $script;
	}

	/**
	 * If set, closing of the modal will need approval by the user.
	 *
	 * @return array|bool
	 */
	private function getApproveSettings(){
		if(!$this->approve){
			return false;
		}

		# If an approval to close the script is required, it cannot be dismissable
		$this->dismissable = false;

		if(is_array($this->approve)){
			//the most common way
			extract($this->approve);
		}
		else if(is_string($this->approve)){
			//if just the name of the thing to be removed is given
			$colour = "grey";
			$message = str::title("Are you sure you want to {$this->approve}?");
		}
		else {
			//if just set to true or any other object
			$title = "Close this window?";
			$message = "Any changes you may have made, will be lost.";
		}

		# If the message contains line breaks, it will break JavaScript, remove
		$message = str_replace(["\r\n","\r","\n"], " ", $message);

		# Set up icons, colour
		$icon_class = Icon::getClass($icon);
		$type = str::translate_approve_colour($colour);
		$button_colour = str::getColour($colour, "btn");

		return [
			"type" => $type,
			"icon" => $icon_class,
			"title" => $title,
			"content" => $message,
			"btnClass" => $button_colour,
//			"buttons" => [
//				"confirm" => [
//					"btnClass" => $button_colour
//				]
//			]
		];
	}

	/**
	 * Checks to see if the draggable key is set,
	 * and if so, will return Javascript that
	 * allows the modal to be draggable using jQuery UI.
	 *
	 * @return array|bool
	 */
	private function getDraggableSettings()
	{
		if(!$this->draggable) {
			return false;
		}

		if(is_array($this->draggable)){
			return $this->draggable;
		} else {
			return [];
		}
	}

	/**
	 * Checks to see if the resizable key is set,
	 * and if so, will return Javascript that
	 * allows the modal to be resizable using jQuery UI.
	 *
	 * @return array|bool
	 */
	private function getResizableSettings()
	{
		if($this->resizeable){
			//If resizable has been written incorrectly
			$this->resizable = $this->resizeable;
		}
		if(!$this->resizable) {
			return false;
		}

		$default_settings = [
			"handles" => "se",
		];

		# Merge and return default settings with (optional) custom settings
		return array_merge($default_settings, is_array($this->resizable) ? $this->resizable : []);
	}
}
