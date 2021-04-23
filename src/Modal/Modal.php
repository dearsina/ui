<?php


namespace App\UI\Modal;

use App\Common\Common;
use App\Common\str;
use App\UI\Badge;
use App\UI\Button;
use App\UI\Dropdown;
use App\UI\Grid;
use App\UI\Icon;
use App\UI\ListGroup;
use App\UI\Progress;
use Exception;

/**
 * Class Modal
 * @package App\UI
 */
class Modal extends Common {
	private $id;
	private $modalHeader;
	private $modalBody;
	private array $modalRows = [];
	private array $modalItems = [];
	private $modalFooter;

	protected $icon;
	protected $draggable;
	protected $resizable;
	protected $resizeable;
	protected $approve;
	protected $dismissible;
	protected $style;
	protected $only_class;
	protected $class;
	protected $parent_class;
	protected $parent_style;
	protected $only_parent_class;
	protected $child_class;
	protected $child_style;
	protected $only_child_class;
	protected $size;
	protected $accent;
	protected $buttons;

	/**
	 * Create a modal
	 * <code>
	 * 	$modal = new modal([
	 * 	"id" => "id",
	 * 	"size" => "xl",
	 * 	"dismissible" => false,
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
	 * @throws Exception
	 * @throws Exception
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
		$button = Button::generate($this->modalHeader['button']);

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Accent
		$this->modalHeader['class'][] = str::getColour($this->accent, "bg");

		# Icon
		if(!$icon = Icon::generate($this->modalHeader['icon'])){
			//the icon attribute can either be in the header or in the main modal
			$icon = Icon::generate($this->icon);
		}

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
		if($this->dismissible !== false){
			$dismiss = <<<EOF
<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" title="Close this window"></button>
EOF;
		}

		return <<<EOF
<div{$id}{$class}{$style}>
	<div class="container">
  		<div class="row">
    		<div class="col-auto modal-title">
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
	 * @throws Exception
	 * @throws Exception
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
		$button = Button::generate($this->modalFooter['button']);

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
		if(($left = $icon.$this->modalFooter['html'].$badge) || $id){
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
			$this->modalBody = $a;
			$this->modalBody['id'] = $this->modalBody['id'] ?: str::id("body");
			return true;
		}

		# Mixed
		if ($a){
			$this->modalBody['html'] = $a;
			$this->modalBody['id'] = str::id("body");
			return true;
		}

		# Clear
		if($a === false){
			$this->modalBody = [];
			return true;
		}

		return true;
	}

	public function setRows($a = NULL): void
	{
		if(!is_array($a)){
			return;
		}

		$this->modalRows = $a;
	}

	public function setItems($a = NULL): void
	{
		if(!is_array($a)){
			return;
		}

		$this->modalItems = $a;
	}

	/**
	 * Returns the body of the modal.
	 * Not optional.
	 *
	 * @return bool|string
	 */
	public function getBodyHTML(){
		if(!$this->modalBody){
			return false;
		}

		if(is_array($this->modalBody['html'])){
			$this->modalBody['html'] = Grid::generate($this->modalBody['html']);
		}

		$id = str::getAttrTag("id", $this->modalBody['id']);
		$class = str::getAttrTag("class", ["modal-body", $this->modalBody['class']]);
		$style = str::getAttrTag("style", $this->modalBody['style']);
		$progress = Progress::generate($this->modalBody['progress']);
		$script = str::getScriptTag($this->modalBody['script']);

		return "<div{$class}{$id}{$style}>{$progress}{$this->modalBody['html']}</div>{$script}";
	}

	/**
	 * @return false|string
	 */
	public function getRowsHTML(): ?string
	{
		if(empty($this->modalRows)){
			return NULL;
		}

		if(!key_exists("rows", $this->modalRows)){
			$this->modalRows = [
				"rows" => $this->modalRows
			];
		}

		if(!is_array($this->modalRows['rows'])){
			return NULL;
		}

		foreach($this->modalRows['rows'] as $key => $val){
			$left = [
				"class" => "small",
				"sm" => $this->modalRows['sm'],
				"html" => $key
			];
			$rows[] = [$left, $val];
		}

		if(is_array($rows)){
			$html = Grid::generate($rows);
		}

		$id = str::getAttrTag("id", $this->modalRows['id']);
		$class_array = str::getAttrArray($this->modalRows['class'], "container card-rows", $this->modalRows['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->modalRows['style']);
		$script = str::getScriptTag($this->modalRows['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
	}

	/**
	 * @return false|string
	 */
	public function getItemsHTML(): ?string
	{
		if(empty($this->modalItems)){
			return NULL;
		}

		if(!key_exists("items", $this->modalItems)){
			$this->modalItems = [
				"items" => $this->modalItems
			];
		}

		$html = ListGroup::generate($this->modalItems);

		$id = str::getAttrTag("id", $this->modalItems['id']);
		$class_array = str::getAttrArray($this->modalItems['class'], "container card-items", $this->modalItems['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->modalItems['style']);
		$script = str::getScriptTag($this->modalItems['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
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
		switch($this->size){
		case 'xs':
		case 's':
		case 'small': $size = "modal-sm"; break;
		case 'l':
		case 'lg':
		case 'large': $size = "modal-lg"; break;
		case 'xl': $size = "modal-xl"; break;
		}
		$parent_class_array = str::getAttrArray($this->parent_class, "modal", $this->only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $this->parent_style);
		
		$class_array = str::getAttrArray($this->class, ["modal-dialog","modal-dialog-centered", "modal-dialog-scrollable", $size], $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);
		
		$child_class_array = str::getAttrArray($this->child_class, "modal-content", $this->only_child_class);
		$child_class = str::getAttrTag("class", $child_class_array);
		$child_style = str::getAttrTag("style", $this->child_style);

		$data = str::getDataAttr($this->getModalDataAttr(), true);

		return /** @lang HTML */<<<EOF
<div
	{$this->getId(true)}
	{$parent_class}
	{$parent_style}
	{$data}
	>
    <div
    	{$class}
		{$style}
		>
        <div
        	{$child_class}
        	{$child_style}
        	>
        	{$this->getHeaderHTML()}
			{$this->getBodyHTML()}
			{$this->getRowsHTML()}
			{$this->getItemsHTML()}
			{$this->getFooterHTML()}
        </div>
    </div>
    {$this->getScriptHTML(true)}
</div>
EOF;

	}

	/**
	 * Returns all the modal settings as an array
	 * to be fed to the data attribute generator.
	 *
	 * @return array
	 */
	private function getModalDataAttr(){
		$modal['show'] = true;
		if($this->dismissible === false){
			$modal['backdrop'] = "static";
		}

		return [
			"settings" => $modal,
			"draggable" => $this->getDraggableSettings(),
			"resizable" => $this->getResizableSettings(),
			"approve" => $this->getApproveSettings(),
		];
	}

	/**
	 * Returns all Javascript settings related to the modal.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function getScriptHTML($as_tag = FALSE){
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

		# If approval is only required if there is a change to a form
		if($this->approve === "change"){
			$this->approve = ["change" => true];
		}

		# If an approval to close the script is required, it cannot be dismissible
		$this->dismissible = false;

		if(is_array($this->approve)){
			//the most common way
			extract($this->approve);
		}
		else if(is_string($this->approve)){
			//if just the name of the thing to be removed is given
			$colour = "grey";
			$message = str::title("Are you sure you want to {$this->approve}?");
		}
//		else {
//			//if just set to true or any other object
//			$title = "Close this window?";
//			$message = "Any changes you may have made, will be lost.";
//		}

		# If the message contains line breaks, it will break JavaScript, remove
		$message = str_replace(["\r\n","\r","\n"], " ", $message);

		# Set up icons, colour
		$icon_class = Icon::getClass($icon);
		$type = str::translate_approve_colour($colour);
		$button_colour = str::getColour($colour, "btn");

		# For this method, we _do_ want to remove empty values
		return str::array_filter_recursive([
			"change" => $change,
			"type" => $type,
			"icon" => $icon_class,
			"title" => $title,
			"content" => $message,
			"buttons" => [
				"confirm" => [
					"btnClass" => $button_colour
				]
			]
		]);
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

		# Even if there are no custom settings, we still need to return an array
		return is_array($this->resizable) ? $this->resizable : [];
	}
}
