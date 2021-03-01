<?php


namespace App\UI\Card;

use App\Common\Common;
use App\Common\Img;
use App\Common\str;
use App\UI\Badge;
use App\UI\Button;
use App\UI\Dropdown;
use App\UI\Grid;
use App\UI\Icon;
use App\UI\Progress;
use Exception;
use Pelago\Emogrifier\CssInliner;

/**
 * Class Card
 * @package App\UI
 */
class Card extends Common {

	public $id;
	public $cardHeader;
	public $cardBody;
	public $cardFooter;
	public $cardPost;
	public $resizable;
	public $draggable;
	public $script;
	public $img;
	public $rows;
	public $only_class;
	public $class;
	public $accent;
	public $buttons;
	public $style;
	public $data;

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
	 * @throws Exception
	 * @throws Exception
	 */
	public function getHeaderHTML(){
		if(!$this->cardHeader){
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

		# Dropdown buttons and/or button(s) in a row
		if($buttons = Button::get($this->cardHeader)){
			$buttons = "<div class=\"col col-buttons\">{$buttons}</div>";
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

		# Title parent class and style
		[$parent_class, $parent_style] = str::getClassAndStyle($this->cardHeader, ["col-auto", "card-title"]);

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container-fluid">
  		<div class="row">
    		<div{$parent_class}{$parent_style}>
    			{$icon}{$title}{$badge}
    		</div>{$buttons}
    	</div>
	</div>{$script}
</div>
EOF;
	}

	/**
	 * Returns the footer as HTML.
	 *
	 * @return bool|string
	 * @throws Exception
	 * @throws Exception
	 */
	public function getFooterHTML(?array $footer = NULL){
		# Override
		if($footer){
			$this->cardFooter = $footer;
		}

		if(!$this->cardFooter){
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

		# Dropdown buttons and/or button(s) in a row
		if($buttons = Button::get($this->cardFooter)){
			$buttons = "<div class=\"col\">{$buttons}</div>";
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

		if($html = $icon.$this->cardFooter['footer'].$this->cardFooter['html'].$badge){
			$row_style = str::getAttrTag("style", $this->cardFooter['row_style']);
			$row_class_array = str::getAttrArray($this->cardFooter['row_class'], ["col-auto", "card-title"], $this->cardFooter['row_class_only']);
			$row_class = str::getAttrTag("class", $row_class_array);
			$html = "<div{$row_class}{$row_style}>{$html}</div>";
		}

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container-fluid">
  		<div class="row">
    		{$html}
    		{$buttons}
    	</div>
	</div>{$script}
</div>
EOF;

	}

	/**
	 * Returns the post as HTML.
	 *
	 * @return bool|string
	 * @throws Exception
	 * @throws Exception
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
			"row_class" => $class_array,
			"row_style" => $style_array,
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
	 * Will replace existing bodies.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the body
	 *
	 * @return bool
	 */
	public function setBody($a = NULL){
		# Array
		if(is_array($a)){
			$a['id'] = $a['id'] ?: str::id("body");
			$this->cardBody = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->cardBody['html'] = $a;
			$this->cardBody['id'] = str::id("body");
			return true;
		}

		# Clear
		if($a === false){
			$this->cardBody = [];
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
		if(!$this->cardBody){
			return false;
		}

		if(is_array($this->cardBody['html'])){
			$this->cardBody['html'] = Grid::generate($this->cardBody['html']);
		}

		$id = str::getAttrTag("id", $this->cardBody['id']);
		$class_array = str::getAttrArray($this->cardBody['class'], "card-body", $this->cardBody['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->cardBody['style']);
		$progress = Progress::generate($this->cardBody['progress']);
		$script = str::getScriptTag($this->cardBody['script']);

		$data = str::getDataAttr($this->cardBody['data'], true);

		$ondrop = str::getAttrTag("ondrop", $this->cardBody['ondrop']);
		$ondragover = str::getAttrTag("ondragover", $this->cardBody['ondragover']);

		return "<div{$class}{$id}{$style}{$data}{$ondrop}{$ondragover}>{$progress}{$this->cardBody['html']}</div>{$script}";
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

	/**
	 * @return string
	 * @throws Exception
	 */
	function getHTML(){
		$class_array = str::getAttrArray($this->class, "card", $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		$data = str::getDataAttr($this->data, true);
		return /** @lang HTML */<<<EOF
<div{$this->getId(true)}{$data}{$class}{$style}>
	{$this->getHeaderHTML()}
	{$this->getImgHTML()}
	{$this->getBodyHTML()}
	{$this->getRowsHTML()}
	{$this->getFooterHTML()}
	
	{$this->getScriptHTML(true)}
</div>
	{$this->getPostHTML()}
EOF;

	}

	private function getImgHTML(){
		return Img::generate($this->img);
	}

	function getEmailHTML(): string
	{
		$class_array = str::getAttrArray($this->class, ["main", "", "card-email"], $this->only_class);
		$class = str::getAttrTag("class", $class_array);

		$default_style = [
			"border-collapse" => "separate",
			"mso-table-lspace" => "0pt",
			"mso-table-rspace" => "0pt",
			"width" => "100%",
			"background" => "#ffffff",
			"border" => "0.9px solid #d8e2e9"
//			"border" => "0.9px solid red"
		];
		$style_array = str::getAttrArray($this->style, $default_style, $this->only_style);
		$style = str::getAttrTag("style", $style_array);

		$html = <<<EOF
	{$this->getHeaderHTML()}
	{$this->getBodyHTML()}
	{$this->getRowsHTML()}
	{$this->getFooterHTML()}
	{$this->getScriptHTML(true)}
EOF;

//		$css_url = "https://app.{$_ENV['domain']}/css/app.css";
		$css_url = "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.0.0-beta1/css/bootstrap.min.css";
		if(!$css = @file_get_contents($css_url)){
			throw new \Exception("The CSS file at <code>{$css_url}</code> could not be accessed. The email was not sent.");
		}
		$html = CssInliner::fromHtml($html)->inlineCss($css)->render();


//		return <<<EOF
//            <!-- White box -->
//            <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 3px; border:0.9px solid #d8e2e9;">
//              <tr>
//                <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 20px;"><!-- First line of the white box. -->
//                  <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
//                    <tr>
//                      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;"><!-- Second line of the white box -->
//						{$html}
//					  </td>
//                    </tr>
//                  </table>
//                </td>
//              </tr>
//            </table>
//			<!-- White box end -->
//EOF;


		return /** @lang HTML */<<<EOF
<table
	{$this->getId(true)}
	{$class}
	{$style}
	cellspacing="0"
	cellpadding="0"
><tr><td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box; padding: 10px;">
	{$html}
</td></tr></table>
	{$this->getPostHTML()}
EOF;
	}

	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function getRowsHTML(){
		if(!is_array($this->rows)){
			return false;
		}

		if(!key_exists("rows", $this->rows)){
			$this->rows = [
				"rows" => $this->rows
			];
		}

		if(!is_array($this->rows['rows'])){
			return false;
		}

		# If the breakpoint key is set to false, the columns won't fold on small screens
		if($this->rows['breakpoint'] === false){
			$row_class = "row-cols-2";
		}

		foreach($this->rows['rows'] as $key => $val){
			$left = [
				"class" => "small",
				"sm" => $this->rows['sm'],
				"html" => $key,
			];
			$rows[] = [
				"row_class" => $row_class,
				"html" => [$left, $val]
			];
		}

		if(is_array($rows)){
			$html = Grid::generate($rows);
		}

		$id = str::getAttrTag("id", $this->rows['id']);
		$class_array = str::getAttrArray($this->rows['class'], "container card-rows", $this->rows['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->rows['style']);
		$script = str::getScriptTag($this->rows['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
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
		if($this->resizable){
			//If resizable has been written incorrectly
			$this->resizable = $this->resizable;
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