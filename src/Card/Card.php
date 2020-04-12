<?php


namespace App\UI\Card;

use App\Common\Common;

use App\UI\Dropdown;
use App\UI\Icon;
use App\UI\Badge;
use App\UI\Button;

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
			$this->card_header = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->card_header['title'] = $a;
			return true;
		}

		# Clear
		if($a == false){
			$this->card_header = [];
		}

		return true;
	}

	/**
	 * Returns the header as HTML.
	 *
	 * @return bool|string
	 */
	public function get_header_html(){
		if(!is_array($this->card_header)){
			// Headers are optional
			return false;
		}

		# Header buttons can also be defined outside of the header key when defining card vales.
		$this->card_header['buttons'] = array_merge($this->card_header['buttons']?:[], $this->buttons?:[]);

		# Add the required Bootstrap header class very first
		$this->card_header['class'] = str::get_attr_array($this->card_header['class'], "card-header", $this->card_header['only_class']);

		# Styles
		$this->card_header['style'] = str::get_attr_array($this->card_header['style'], NULL, $this->card_header['only_style']);

		# Dropdown buttons
		if($this->card_header['buttons']){
			$buttons = Dropdown::generate($this->card_header);
		}

		# Button(s) in a row
		if(str::is_numeric_array($this->card_header['button'])){
			foreach($this->card_header['button'] as $b){
				$button .= Button::generate($b);
			}
		} else if ($this->card_header['button']){
			$button = Button::generate($this->card_header['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Accent
		$this->card_header['class'][] = str::get_colour($this->accent, "bg");

		# Icon
		$icon = Icon::generate($this->card_header['icon']);

		# Badge
		$badge = Badge::generate($this->card_header['badge']);

		# ID
		$id = str::get_attr_tag("id", $this->card_header['id']);

		# Style
		$style = str::get_attr_tag("style", $this->card_header['style']);

		# Title colour
		$class[] = str::get_colour($this->card_header['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-header-draggable" : false;

		# Script
		$script = str::script_tag($this->card_header['script']);

		# The header title itself
		$title = $this->card_header['header'].$this->card_header['title'].$this->card_header['html'];

		# Title class
		if(!empty(array_filter($class))){
			$title_class = str::get_attr_tag("class", $class);
			$title = "<span{$title_class}>$title</span>";
		}

		# The div class
		$class = str::get_attr_tag("class", $this->card_header['class']);

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
	public function get_footer_html(){
		if(!is_array($this->card_footer)){
			// Footers are optional
			return false;
		}

		# Add the required Bootstrap footer class very first
		$this->card_footer['class'] = str::get_attr_array($this->card_footer['class'], "card-footer", $this->card_footer['only_class']);

		# Styles
		$this->card_footer['style'] = str::get_attr_array($this->card_footer['style'], NULL, $this->card_footer['only_style']);

		# Dropdown buttons
		if($this->card_footer['buttons']){
			$buttons = Dropdown::generate($this->card_footer);
		}

		# Button(s) in a row
		if(str::is_numeric_array($this->card_footer['button'])){
			foreach($this->card_footer['button'] as $b){
				if(empty($b)){
					continue;
				}
				$button .= Button::generate($b);
			}
		} else if ($this->card_footer['button']){
			$button = Button::generate($this->card_footer['button']);
		}

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Icon
		$icon = Icon::generate($this->card_footer['icon']);

		# Badge
		$badge = Badge::generate($this->card_footer['badge']);

		# ID
		$id = str::get_attr_tag("id", $this->card_footer['id']);

		# Style
		$style = str::get_attr_tag("style", $this->card_footer['style']);

		# Text colour
		$class[] = str::get_colour($this->card_footer['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-footer-draggable" : false;

		# Script
		$script = str::script_tag($this->card_footer['script']);

		# The div class
		$class = str::get_attr_tag("class", $this->card_footer['class']);

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto">
    			{$icon}{$this->card_footer['html']}{$badge}
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
			$this->card_footer = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->card_footer['html'] = $a;
			return true;
		}

		# Clear
		if($a == false){
			$this->card_footer = [];
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

		$id = str::get_attr_tag("id", $this->body['id']);
		$class = str::get_attr_tag("class", ["card-body", $this->body['class']]);
		$style = str::get_attr_tag("style", $this->body['style']);
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
			return str::get_attr_tag("id", $this->id);
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
			return str::get_attr_tag("class", $class_array);
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
			return str::get_attr_tag("style", $this->style);
		}
		return $this->style;
	}

	function get_html(){
		return /** @lang HTML */<<<EOF
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