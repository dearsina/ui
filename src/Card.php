<?php


namespace App\UI;

use App\Common\Common;
use App\Common\str;

class Card extends Common {

	private $id;
	private $cardHeader;
	private $cardBody;
	private $cardFooter;
	private $cardPost;

	/**
	 * Create a card
	 * <code>
	 * 	$card = new card([
	 * 	"accent" => "blue",
	 * 	"header" => [
	 * 		"title" => "Sign in to your account.",
	 * 		"buttons" => [[
	 * 			"title" => "Fancy header",
	 * 			"strong" => true,
	 * 			"colour" => "blue"
	 * 		],[
	 * 			"hash" => "#go/somewhere",
	 * 			"title" => "Go somewhere",
	 * 			"icon" => "fire",
	 * 			"colour" => "red"
	 * 		]]
	 * 	],
	 * 	"body" => $form->getHTML(),
	 * 	"footer" => "Don't have an account yet?",
	 *
	 * 	"id" => "", 	//The unique ID of this card div
	 * 	"class" => [],	//Array or string of classes to add to this card div
	 * 	"style" => [],	//Array of overriding styles for the card div
	 * 	"draggable" => bool|[], //Make the card draggable. Can also be an array with custom settings
	 * 	"resizable" => bool|[], //Make the card draggable. Can also be an array with custom settings
	 * 	"script" => "", //Javascript, without the script tag.
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
		$this->id = $id ?: str::id("card");
		return $this->id;
	}

	/**
	 * Set the card header.
	 * Will replace existing headers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the header
	 *
	 * @return bool
	 */
	public function setHeader($a = NULL){
		# Array
		if(is_array($a)){
			$this->cardHeader = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->cardHeader['title'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->cardHeader = [];
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
		if(!is_array($this->cardHeader)){
			// Headers are optional
			return false;
		}

		# Header buttons can also be defined outside of the header key when defining card vales.
		$this->cardHeader['buttons'] = array_merge($this->cardHeader['buttons']?:[], $this->buttons?:[]);

		# Add the required Bootstrap header class very first
		$this->cardHeader['class'] = str::getAttrArray($this->cardHeader['class'], "card-header", $this->cardHeader['only_class']);

		# Draggable
		$this->cardHeader['class'][] = $this->draggable ? "card-header-draggable" : false;

		# Styles
		$this->cardHeader['style'] = str::getAttrArray($this->cardHeader['style'], NULL, $this->cardHeader['only_style']);

		# Dropdown buttons
		if($this->cardHeader['buttons']){
			$buttons = Dropdown::generate($this->cardHeader);
		}

		# Button(s) in a row
		if(str::isNumericArray($this->cardHeader['button'])){
			foreach($this->cardHeader['button'] as $b){
				$button .= Button::generate($b);
			}
		} else if ($this->cardHeader['button']){
			$button = Button::generate($this->cardHeader['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Accent
		$this->cardHeader['class'][] = str::getColour($this->accent, "bg");

		# Icon
		$icon = Icon::generate($this->cardHeader['icon']);

		# Badge
		$badge = Badge::generate($this->cardHeader['badge']);

		# ID
		$id = str::getAttrTag("id", $this->cardHeader['id']);

		# Style
		$style = str::getAttrTag("style", $this->cardHeader['style']);

		# Title colour
		$class[] = str::getColour($this->cardHeader['colour']);

		# Script
		$script = str::getScriptTag($this->cardHeader['script']);

		# The header title itself
		$title = $this->cardHeader['header'].$this->cardHeader['title'].$this->cardHeader['html'];

		# Title class
		if(!empty(array_filter($class))){
			$title_class = str::getAttrTag("class", $class);
			$title = "<span{$title_class}>$title</span>";
		}

		# The div class
		$class = str::getAttrTag("class", $this->cardHeader['class']);

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto">
    			{$icon}{$title}{$badge}
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
	 * Returns the footer as HTML.
	 *
	 * @return bool|string
	 */
	public function getFooterHTML(){
		if(!is_array($this->cardFooter)){
			// Footers are optional
			return false;
		}

		# Add the required Bootstrap footer class very first
		$this->cardFooter['class'] = str::getAttrArray($this->cardFooter['class'], "card-footer", $this->cardFooter['only_class']);

		# Styles
		$this->cardFooter['style'] = str::getAttrArray($this->cardFooter['style'], NULL, $this->cardFooter['only_style']);

		# Dropdown buttons
		if($this->cardFooter['buttons']){
			$buttons = Dropdown::generate($this->cardFooter);
		}

		# Button(s) in a row
		if(str::isNumericArray($this->cardFooter['button'])){
			foreach($this->cardFooter['button'] as $b){
				if(empty($b)){
					continue;
				}
				$button .= Button::generate($b);
			}
		} else if ($this->cardFooter['button']){
			$button = Button::generate($this->cardFooter['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Icon
		$icon = Icon::generate($this->cardFooter['icon']);

		# Badge
		$badge = Badge::generate($this->cardFooter['badge']);

		# ID
		$id = str::getAttrTag("id", $this->cardFooter['id']);

		# Style
		$style = str::getAttrTag("style", $this->cardFooter['style']);

		# Text colour
		$class[] = str::getColour($this->cardFooter['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-footer-draggable" : false;

		# Script
		$script = str::getScriptTag($this->cardFooter['script']);

		# The div class
		$class = str::getAttrTag("class", $this->cardFooter['class']);

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto">
    			{$icon}{$this->cardFooter['html']}{$badge}
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
	 * Returns the post as HTML.
	 *
	 * @return bool|string
	 */
	public function getPostHTML(){
		if(!is_array($this->cardPost)){
			// Posts are optional
			return false;
		}

		# ID
		$id = str::getAttrTag("id", $this->cardPost['id']);

		# Add the required Bootstrap post class very first
		$class_array = str::getAttrArray($this->cardPost['class'], str::getColour($this->cardPost['colour']), $this->cardPost['only_class']);

		# Styles
		$style_array = str::getAttrArray($this->cardPost['style'], NULL, $this->cardPost['only_style']);

		# Icon
		$icon = Icon::generate($this->cardPost['icon']);

		# Badge
		$badge = Badge::generate($this->cardPost['badge']);

		# Script
		$script = str::getScriptTag($this->cardPost['script']);

		$cells[] = [
			"class" => $class_array,
			"style" => $style_array,
			"html" => "{$icon}{$this->cardPost['html']}{$badge}"
		];
		$html = Grid::generate($cells);

		$class = str::getAttrTag("class", "card-post");

		return "<div{$id}{$class}>{$html}</div>{$script}";
	}

	/**
	 * Set the card footer.
	 * Will replace existing footers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the footer
	 *
	 * @return bool
	 */
	public function setFooter($a = NULL){
		# Array
		if(is_array($a)){
			$this->cardFooter = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->cardFooter['html'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->cardFooter = [];
			return true;
		}

		return true;
	}

	/**
	 * Set the card post.
	 * Will replace existing posts.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the post
	 *
	 * @return bool
	 */
	public function setPost($a = NULL){
		# Array
		if(is_array($a)){
			$this->cardPost = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->cardPost['html'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->cardPost = [];
			return true;
		}

		return true;
	}

	/**
	 * Set the card body.
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
	 * Returns the body of the card.
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
		$class = str::getAttrTag("class", ["card-body", $this->body['class']]);
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
		$class_array = str::getAttrArray($this->class, "card", $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		return /** @lang HTML */<<<EOF
<div
	{$this->getId(true)}
	{$class}
	{$style}
>
	{$this->getHeaderHTML()}
	{$this->getBodyHTML()}
	{$this->getFooterHTML()}
	
	{$this->getScriptHTML(true)}
</div>
	{$this->getPostHTML()}
EOF;

	}

	/**
	 * Returns all Javascript related to the card.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function getScriptHTML($as_tag = FALSE){
		$script = $this->script;
		$script .= $this->getDraggableScript();
		$script .= $this->getResizableScript();

		if($as_tag){
			return str::getScriptTag($script);
		}
		return $script;
	}

	/**
	 * Checks to see if the draggable key is set,
	 * and if so, will return Javascript that
	 * allows the card to be draggable using jQuery UI.
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
		handle: ".card-header-draggable",
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
	 * allows the card to be resizable using jQuery UI.
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

		return "$('#{$this->getId()}').resizable({$resizable_settings_json});";
	}
}