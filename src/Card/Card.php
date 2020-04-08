<?php


namespace App\UI\Card;


use App\Common\Common;

/**
 * @var str
 */

use app\common\dropdown;
use app\common\listing;
use app\common\progress;
use App\Common\str;

class Card extends Common {

	private $id;

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
	 * 	"body" => $form->get_html(),
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
			$this->set_id();
			return true;
		}

		foreach($a as $key => $val){
			$method = "set_$key";
			if (method_exists($this, $method)) {
				//if a custom setter method exists, use it
				$this->$method($val);
			} else {
				$this->$key = $val;
			}
		}

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
	public function set_id($id = NULL){
		$this->id = $id ?: str::id("card");
		return true;
	}

	/**
	 * Set the card header.
	 * Will replace existing headers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the header
	 *
	 * @return bool
	 */
	public function set_header($a = NULL){
		# Array
		if(is_array($a)){
			if($a['class'] && !is_array($a['class'])){
				$a['class'] =  [$a['class']];
			}
			if($a['style'] && !is_array($a['style'])){
				$a['style'] =  [$a['style']];
			}
			$this->header = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->header['title'] = $a;
			return true;
		}

		# Clear
		if($a == false){
			$this->header = [];
		}

		return true;
	}

	public function get_header_html(){
		if(!is_array($this->header)){
			// Headers are optional
			return false;
		}

		# Header buttons can also be defined outside of the header key when defining card vales.
		$this->header['buttons'] = array_merge($this->header['buttons']?:[], $this->buttons?:[]);

		# Add the required Bootstrap header class very first
		$this->header['class'] = array_merge(["card-header"], $this->header['class']);

		# Dropdown buttons
		if($this->header['buttons']){
			$buttons = dropdown::generate($this->header);
		}

		# Button(s) in a row
		if(str::is_numeric_array($this->header['button'])){
			foreach($this->header['button'] as $b){
				$b['class'][] = " float-right";
				$b['style']["margin"] = "-2px 0 -9px .5rem";
				$button .= Button::generate($b);
			}
		} else if ($this->header['button']){
			$button = Button::generate($this->header['button']);
		}

		# Accent
		$this->header['class'][] = icon::get_colour($this->accent, "bg");

		# Icon
		$icon = icon::generate($this->header['icon']);

		# Badge
		$badge = Badge::generate($this->header['badge']);

		# ID
		$id = str::get_attr("id", $this->header['id']);

		# Style
		$style = str::get_attr("style", $this->header['style']);

		# Title colour
		$class[] = icon::get_colour($this->header['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-header-draggable" : false;

		# Script
		$script = str::script_tag($this->header['script']);

		# The header title itself
		$title = $this->header['header'].$this->header['title'].$this->header['html'];

		# Title class
		if(!empty(array_filter($class))){
			$title_class = str::get_attr("class", $class);
			$title = "<span{$title_class}>$title</span>";
		}

		# The div class
		$class = str::get_attr("class", $this->header['class']);

		return "<div{$id}{$class}{$style}>{$buttons}{$button}{$icon}{$title}{$badge}{$script}</div>";
	}

	public function get_footer_html(){
		if(!is_array($this->footer)){
			// Footers are optional
			return false;
		}

		# Add the required Bootstrap footer class very first
		$this->footer['class'] = array_merge(["card-footer"], $this->footer['class']);

		# Dropdown buttons
		if($this->footer['buttons']){
			$buttons = dropdown::generate($this->footer);
		}

		# Button(s) in a row
		if(str::is_numeric_array($this->footer['button'])){
			foreach($this->footer['button'] as $b){
				$b['class'][] = " float-right";
				$b['style']["margin"] = "-2px 0 -9px .5rem";
				$button .= Button::generate($b);
			}
		} else if ($this->footer['button']){
			$button = Button::generate($this->footer['button']);
		}

		# Icon
		$icon = icon::generate($this->footer['icon']);

		# Badge
		$badge = Badge::generate($this->footer['badge']);

		# ID
		$id = str::get_attr("id", $this->footer['id']);

		# Style
		$style = str::get_attr("style", $this->footer['style']);

		# Text colour
		$class[] = icon::get_colour($this->footer['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-footer-draggable" : false;

		# Script
		$script = str::script_tag($this->footer['script']);

		# The div class
		$class = str::get_attr("class", $this->footer['class']);

		return "<div{$id}{$class}{$style}>{$buttons}{$button}{$icon}{$this->footer['html']}{$badge}{$script}</div>";
	}

	/**
	 * Set the card footer.
	 * Will replace existing footers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the footer
	 *
	 * @return bool
	 */
	public function set_footer($a = NULL){
		# Array
		if(is_array($a)){
			if($a['class'] && !is_array($a['class'])){
				$a['class'] =  [$a['class']];
			}
			if($a['style'] && !is_array($a['style'])){
				$a['style'] =  [$a['style']];
			}
			$this->footer = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->footer['html'] = $a;
			return true;
		}

		# Clear
		if($a == false){
			$this->footer = [];
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
	public function set_body($a = NULL){
		# Array
		if(is_array($a)){
			if($a['class'] && !is_array($a['class'])){
				$a['class'] =  [$a['class']];
			}
			if($a['style'] && !is_array($a['style'])){
				$a['style'] =  [$a['style']];
			}
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
		if($a == false){
			$this->body = [];
		}

		return true;
	}

	/**
	 * Returns the body of the card.
	 * Not optional.
	 *
	 * @return bool|string
	 */
	public function get_body_html(){
		if(!$this->body){
			return false;
		}

		if(is_array($this->body['html'])){
			$this->body['html'] = listing::get_row($this->body['html'], $this->body['id']);
		}

		$id = str::get_attr("id", $this->body['id']);
		$class = str::get_attr("class", ["card-body", $this->body['class']]);
		$style = str::get_attr("style", $this->body['style']);
		$progress = progress::generate($this->body['progress']);
		$script = str::script_tag($this->body['script']);

		return "<div{$class}{$id}{$style}>{$progress}{$this->body['html']}</div>{$script}";
	}

	/**
	 * Return the ID.
	 *
	 * @param bool $as_tag If set to TRUE return the ID as a HTML tag.
	 *
	 * @return bool|string
	 */
	private function get_id($as_tag = FALSE){
		if($as_tag){
			return str::get_attr("id", $this->id);
		}
		return $this->id;
	}

	/**
	 * Return the class(es).
	 *
	 * @param bool $as_tag If set to TRUE return the classes as a HTML tag.
	 *
	 * @return bool|string
	 */
	private function get_class($as_tag = FALSE){
		# Generate an array of all the classes to use
		$class_array = array_filter([
			"card",
//				$this->get_accent(),
//				$this->get_border(),
			$this->class
		]);

		if($as_tag){
			return str::get_attr("class", $class_array);
		}

		return implode(" ", $class_array);
	}

	/**
	 * Checks to see if the draggable key is set,
	 * and if so, will return Javascript that
	 * allows the card to be draggable using jQuery UI.
	 *
	 * @return bool|string
	 */
	private function get_draggable_script(){
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
	
	$('#{$this->get_id()}').draggable(settings);
	$('#{$this->get_id()}').css("z-index", "9999");
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
	private function get_resizable_script(){
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

		return "$('#{$this->get_id()}').resizable({$resizable_settings_json});";
	}

	/**
	 * Returns all Javascript related to the card.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function get_script($as_tag = FALSE){
		$script = $this->script;
		$script .= $this->get_draggable_script();
		$script .= $this->get_resizable_script();

		if($as_tag){
			return str::script_tag($script);
		}
		return $script;
	}

	/**
	 * Returns the styles
	 *
	 * @param bool $as_tag
	 *
	 * @return bool|string
	 */
	private function get_style($as_tag = FALSE){
		if($as_tag){
			return str::get_attr("style", $this->style);
		}
		return $this->style;
	}

	function get_html(){
		$html = /** @lang HTML */<<<EOF
<div
	{$this->get_id(true)}
	{$this->get_class(true)}
	{$this->get_style(true)}
>
	{$this->get_header_html()}
	{$this->get_body_html()}
	{$this->get_footer_html()}
	
	{$this->get_script(true)}
</div>
EOF;

	}
}