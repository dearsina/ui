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
	 * 	"script" => "", //Javascript, without the script tag.
	 * 	"size" => "", //small, large, xl
	 * 	"dismiss" => true, //set to false if you don't want user to be able to easily dismiss modal
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
		if(!is_array($this->modalHeader) && $this->dismissable === false){
			// Only if no header information has been sent *and* the user has been prevented from making a choice
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

		# Dismiss button top right
		if($this->approve){
			//if the dismissable has to be approved
			$close = "onClick=\"{$this->getId()}Hide();\"";
		} else {
			//if a simple close is suffient
			$close = "data-dismiss=\"modal\"";
		}

		# If the modal can be dismissed
		if($this->dismissable !== false){
			$dismiss = <<<EOF
<button type="button" class="close" {$close} aria-label="Close">
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

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto">
    			{$icon}{$this->modalFooter['html']}{$badge}
    		</div>
    		<div class="col">
    			{$buttons}{$button}
    		</div>
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
		$default_class = "modal";
		switch($this->size){
		case 'xs':
		case 'small': $default_class .= "-sm"; break;
		case 'large': $default_class .= "-lg"; break;
		case 'xl': $default_class .= "-xl"; break;
		}
		$class_array = str::getAttrArray($this->class, $default_class, $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		return /** @lang HTML */<<<EOF
<div
	{$this->getId(true)}
	{$class}
	{$style}
>
    <div class="modal-dialog modal-dialog-centered">
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
	 * Returns all Javascript related to the modal.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function getScriptHTML($as_tag = FALSE){
		# Add user enabled features
		$script .= $this->getApproveScript();
		$script .= $this->getDraggableScript();
		$script .= $this->getResizableScript();
		//Placed before as they amy influence the modal settings

		# Modal settings
		$settings['show'] = true;
		if($this->dismissable === false){
			$settings['backdrop'] = "static";
		}
		$settings_json = json_encode($settings, JSON_PRETTY_PRINT);

		$script = /** @lang JavaScript */<<<EOF
$('#{$this->getId()}').modal({$settings_json});

// Once the modal is hidden, remove it
$('#{$this->getId()}').on('hidden.bs.modal', function (e) {
	$('#{$this->getId()}').remove();
});

{$script}
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
	 * @return bool|string
	 */
	private function getApproveScript(){
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
			$message = str::title("Are you sure you want to close?");
		}

		# If the message contains line breaks, it will break JavaScript, remove
		$message = str_replace(["\r\n","\r","\n"], " ", $message);

		# Set up icons, colour
		$icon_class = Icon::getClass($icon);
		$type = str::translate_approve_colour($colour);
		$button_colour = str::getColour($colour, "btn");

		return /** @lang ECMAScript 6 */ <<<EOF
$('#{$this->getId()}').on('hidePrevented.bs.modal', function (e) {
	{$this->getId()}Hide();
});
function {$this->getId()}Hide(){
    	$.confirm({
		animateFromElement: false,
		escapeKey: true,
		backgroundDismiss: true,
		closeIcon: true,
		type: "{$type}",
		theme: "material",
		icon: "{$icon_class}",
		title: "{$title}",
		content: "{$message}",
		buttons: {
			confirm: {
				text: "Yes", // text for button
				btnClass: "{$button_colour}", // class for the button
				keys: ["enter"], // keyboard event for button
				action: function(){
					$('#{$this->getId()}').modal('hide');
				}
			},
			cancel: function () {
				//Close
			},
		}
	});
}
EOF;

	}

	/**
	 * Checks to see if the draggable key is set,
	 * and if so, will return Javascript that
	 * allows the modal to be draggable using jQuery UI.
	 *
	 * @return bool|string
	 */
	private function getDraggableScript(){
		if(!$this->draggable) {
			return false;
		}

		if(is_array($this->draggable)){
			$custom_settings_json = json_encode($this->draggable);
		} else {
			$custom_settings_json = "{}";
		}

		return /** @lang ECMAScript 6 */ <<<EOF
$(document).ready(function(){
	var default_settings = {
		handle: ".modal-header-draggable",
		scroll: false,
		start: function(event, ui){
	  		$(ui.helper).css('width', $(event.target).width() + "px");
	   }
	};
	
	var custom_settings = {$custom_settings_json};
	var settings = $.extend(default_settings, custom_settings);
	
	$('#{$this->getId()}').draggable(settings);
	$('#{$this->getId()}').css("z-index", "9999");
});
EOF;
	}

	/**
	 * Checks to see if the resizable key is set,
	 * and if so, will return Javascript that
	 * allows the modal to be resizable using jQuery UI.
	 *
	 * @return bool|string
	 */
	private function getResizableScript(){
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

		if(is_array($this->resizable)){
			$resizable_settings = array_merge($default_settings, $this->resizable);
		} else {
			$resizable_settings = $default_settings;
		}

		$resizable_settings_json = json_encode($resizable_settings, JSON_PRETTY_PRINT);

		return "$('#{$this->getId()} .modal-content').resizable({$resizable_settings_json});";
	}
}
