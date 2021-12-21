<?php


namespace App\UI\Card;

use App\Common\Img;
use App\Common\str;
use App\UI\Badge;
use App\UI\Button;
use App\UI\Dropdown;
use App\UI\Grid;
use App\UI\Icon;
use App\UI\ListGroup;
use App\UI\Progress;
use Exception;
use Pelago\Emogrifier\CssInliner;

/**
 * Class Card
 * @package App\UI
 */
class Card extends \App\Common\Prototype {

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
	public $items;
	public $only_class;
	public $class;
	public $accent;
	public $buttons;
	public $style;
	public $data;

	/**
	 * Create a card
	 * <code>
	 *    $card = new card([
	 *    "accent" => "blue",
	 *    "header" => [
	 *        "title" => "Sign in to your account.",
	 *        "buttons" => [[
	 *            "title" => "Fancy header",
	 *            "strong" => true,
	 *            "colour" => "blue"
	 *        ],[
	 *            "hash" => "#go/somewhere",
	 *            "title" => "Go somewhere",
	 *            "icon" => "fire",
	 *            "colour" => "red"
	 *        ]]
	 *    ],
	 *    "body" => $form->getHTML(),
	 *    "footer" => "Don't have an account yet?",
	 *
	 *    "id" => "",    //The unique ID of this card div
	 *    "class" => [],    //Array or string of classes to add to this card div
	 *    "style" => [],    //Array of overriding styles for the card div
	 *    "draggable" => bool|[], //Make the card draggable. Can also be an array with custom settings
	 *    "resizable" => bool|[], //Make the card draggable. Can also be an array with custom settings
	 *    "script" => "", //Javascript, without the script tag.
	 *    "header" => [
	 *        "title" => "",    //If the header is just a string, it's assumed to be this key value
	 *        "html" => "",
	 *        "buttons" => [],
	 *        "button" => []
	 *    ],
	 *    "body" => [
	 *        "html" => "", //If the body is just a string, it's assumed to be this key value
	 *        "class" => [],
	 *        "style" => [],
	 *    ],
	 *    "footer" => [
	 *        "html" => "", //If the footer is just a string, it's assumed to be this key value
	 *        "class" => [],
	 *        "style" => [],
	 *    ]
	 * ]);
	 * </code>
	 *
	 * @param array|NULL $a
	 *
	 */
	function __construct(?array $a = NULL)
	{
		parent::__construct();

		# ID is always set
		$this->setId($a['id']);
		// Unless it's explicitly set to false

		if(!is_array($a)){
			return;
		}

		$this->setAttr($a);
	}

	/**
	 * Generate an ID.
	 * If one is given, use that.
	 * If set to false, will set the ID to false.
	 * IDs shouldn't be set to false
	 *
	 * @param null $id
	 *
	 * @return void
	 */
	public function setId($id = NULL): void
	{
		if($id === false){
			$this->id = false;
			return;
		}

		$this->id = $id ?: str::id("card");
	}

	/**
	 * Set the card header.
	 * Will replace existing headers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the header
	 *
	 * @return void
	 */
	public function setHeader($a = NULL): void
	{
		# Array
		if(is_array($a)){
			$this->cardHeader = $a;
			return;
		}

		# Mixed
		if($a){
			$this->cardHeader['title'] = $a;
			return;
		}

		# Clear
		if($a === false){
			$this->cardHeader = [];
			return;
		}
	}

	/**
	 * Set the card footer.
	 * Will replace existing footers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the footer
	 *
	 * @return void
	 */
	public function setFooter($a = NULL): void
	{
		# Array
		if(is_array($a)){
			$this->cardFooter = $a;
			return;
		}

		# Mixed
		if($a){
			$this->cardFooter['html'] = $a;
			return;
		}

		# Clear
		if($a === false){
			$this->cardFooter = [];
			return;
		}
	}

	/**
	 * Set the card post.
	 * Will replace existing posts.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the post
	 *
	 * @return bool
	 */
	public function setPost($a = NULL): void
	{
		# Array
		if(is_array($a)){
			$this->cardPost = $a;
			return;
		}

		# Mixed
		if($a){
			$this->cardPost['html'] = $a;
			return;
		}

		# Clear
		if($a === false){
			$this->cardPost = [];
			return;
		}
	}

	public function setRows(?array $rows = NULL): void
	{
		$this->rows = $rows;
	}

	/**
	 * Set the card body.
	 * Will replace existing bodies.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the body
	 *
	 * @return bool
	 */
	public function setBody($a = NULL): void
	{
		# Array
		if(is_array($a)){
			$a['id'] = $a['id'] ?: str::id("body");
			$this->cardBody = $a;
			return;
		}

		# Mixed
		if($a){
			$this->cardBody['html'] = $a;
			$this->cardBody['id'] = str::id("body");
			return;
		}

		# Clear
		if($a === false){
			$this->cardBody = [];
			return;
		}
	}

	/**
	 * @param array|false $a
	 */
	public function setItems($a = NULL): void
	{
		# Numeric arrays
		if(str::isNumericArray($a)){
			$this->items['items'] = $a;
			$this->items['id'] = str::id("items");
			return;
		}

		# Associative arrays
		if(str::isAssociativeArray($a)){
			$a['id'] = $a['id'] ?: str::id("items");
			$this->items = $a;
			return;
		}

		# Clear
		if($a === false){
			$this->items = [];
			return;
		}
	}

	/**
	 * Handle buttons in the header of a card.
	 *
	 * @return string|null
	 * @throws Exception
	 */
	private function getHeaderButtonsHTML(): ?string
	{
		if($this->cardHeader['buttons']){
			$buttons = Button::get($this->cardHeader);
		}

		if($this->cardHeader['button']){
			if(str::isNumericArray($this->cardHeader['button'])){
				$this->cardHeader['button'] = array_reverse($this->cardHeader['button']);
			}
			$button = Button::generate($this->cardHeader['button']);
		}

		if($buttons || $button){
			return "<div class=\"col col-buttons btn-float-right\">{$button}{$buttons}</div>";
		}

		return NULL;
	}

	/**
	 * Handle buttons in the footer of a card.
	 *
	 * @param string|null $content
	 *
	 * @return string|null
	 * @throws Exception
	 */
	private function getFooterButtonsHTML(?string $content): ?string
	{
		if($this->cardFooter['buttons']){
			$buttons = Button::get($this->cardFooter);
		}

		if($this->cardFooter['button']){
			$button = Button::generate($this->cardFooter['button']);
		}

		if($buttons || $button){
			if($content){
				return "<div class=\"col-auto col-buttons btn-float-right\">{$buttons}{$button}</div>";
			}
			return "<div class=\"col col-buttons btn-float-right\">{$buttons}{$button}</div>";
		}

		return NULL;
	}

	/**
	 * Returns the header as HTML.
	 *
	 * @return bool|string
	 * @throws Exception
	 * @throws Exception
	 */
	public function getHeaderHTML(): ?string
	{
		if(!$this->cardHeader){
			// Headers are optional
			return NULL;
		}

		# Header buttons can also be defined outside of the header key when defining card vales.
		$this->cardHeader['buttons'] = array_merge($this->cardHeader['buttons'] ?: [], $this->buttons ?: []);

		# Add the required Bootstrap header class very first
		$this->cardHeader['class'] = str::getAttrArray($this->cardHeader['class'], "card-header", $this->cardHeader['only_class']);

		# Draggable
		$this->cardHeader['class'][] = $this->draggable ? "card-header-draggable" : false;

		# Styles
		$this->cardHeader['style'] = str::getAttrArray($this->cardHeader['style'], NULL, $this->cardHeader['only_style']);

		# Dropdown buttons and/or button(s) in a row
		$buttons = $this->getHeaderButtonsHTML();

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
		$title = $this->cardHeader['header'] . $this->cardHeader['title'] . $this->cardHeader['html'];

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
	public function getFooterHTML(?array $footer = NULL): ?string
	{
		# Override
		if($footer){
			$this->cardFooter = $footer;
		}

		if(!$this->cardFooter){
			// Footers are optional
			return NULL;
		}

		# Add the required Bootstrap footer class very first
		$this->cardFooter['class'] = str::getAttrArray($this->cardFooter['class'], "card-footer", $this->cardFooter['only_class']);

		# Styles
		$this->cardFooter['style'] = str::getAttrArray($this->cardFooter['style'], NULL, $this->cardFooter['only_style']);

		# ID
		$id = str::getAttrTag("id", $this->cardFooter['id']);

		# Style
		$style = str::getAttrTag("style", $this->cardFooter['style']);

		# Text colour
		$class[] = str::getColour($this->cardFooter['colour']);

		# Draggable
		$class[] = $this->draggable ? "card-footer-draggable" : false;

		# The div class
		$class = str::getAttrTag("class", $this->cardFooter['class']);

		return <<<EOF
<div{$id}{$class}{$style}>
	{$this->getSockHTML($footer)}
</div>
EOF;
	}

	/**
	 * Broken the footer and "sock" into two different methods, because
	 * the sock can be called separately. The analogy should actually be
	 * reversed, as the sock is everything except the card-footer div.
	 *
	 * This allows for the contents (only) of the div to be updated via AJAX.
	 *
	 * @param array|null $footer
	 *
	 * @return false|string
	 * @throws Exception
	 */
	public function getSockHTML(?array $footer = NULL): ?string
	{
		# Override
		if($footer){
			$this->cardFooter = $footer;
		}

		if(!$this->cardFooter){
			// Footers are optional
			return NULL;
		}

		# Icon
		$icon = Icon::generate($this->cardFooter['icon']);

		# Badge
		$badge = Badge::generate($this->cardFooter['badge']);

		# Script
		$script = str::getScriptTag($this->cardFooter['script']);

		if(is_array($this->cardFooter['html'])){
			$this->cardFooter['html'] = Grid::generate($this->cardFooter['html']);
		}

		if($html = $icon . $this->cardFooter['footer'] . $this->cardFooter['html'] . $badge){
			$row_style = str::getAttrTag("style", $this->cardFooter['row_style']);
			$row_class_array = str::getAttrArray($this->cardFooter['row_class'], ["col", "card-title"], $this->cardFooter['row_class_only']);
			$row_class = str::getAttrTag("class", $row_class_array);
			$html = "<div{$row_class}{$row_style}>{$html}</div>";
		}

		# Dropdown buttons and/or button(s) in a row
		$buttons = $this->getFooterButtonsHTML($html);

		return <<<EOF
	<div class="container-fluid">
  		<div class="row">
    		{$html}
    		{$buttons}
    	</div>
	</div>{$script}
EOF;
	}

	/**
	 * Returns the post as HTML.
	 *
	 * @return bool|string
	 * @throws Exception
	 * @throws Exception
	 */
	public function getPostHTML(): ?string
	{
		if(!is_array($this->cardPost)){
			// Posts are optional
			return NULL;
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
			"html" => "{$icon}{$this->cardPost['html']}{$badge}",
		];
		$html = Grid::generate($cells);

		$class = str::getAttrTag("class", "card-post");

		return "<div{$id}{$class}>{$html}</div>{$script}";
	}

	/**
	 * Appends array keys or HTML to the card body.
	 *
	 * @param null $a
	 */
	public function addBody($a = NULL): void
	{
		if(!$a){
			return;
		}

		# If an array is sent, get really particular
		if(is_array($a)){
			foreach($a as $key => $val){
				$this->cardBody[$key] .= $val;
			}
			return;
		}

		# Otherwise, just append to the HTML
		$this->cardBody['html'] .= $a;
	}

	/**
	 * Returns the body of the card.
	 * Not optional.
	 *
	 * @return NULL|string
	 */
	public function getBodyHTML(): ?string
	{
		if(!$this->cardBody){
			return NULL;
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
	private function getId($as_tag = NULL)
	{
		if($as_tag){
			return str::getAttrTag("id", $this->id);
		}

		return $this->id;
	}

	/**
	 * Returns the card classes.
	 * Includes accents.
	 *
	 * @return string|null
	 */
	private function getClassTag(): ?string
	{
		$class_array = str::getAttrArray($this->class, "card", $this->only_class);

		# Add the (optional) accent colour
		if($this->accent){
			$class_array[] = "card-bg-{$this->accent}";
		}
		
		return str::getAttrTag("class", $class_array);
	}

	/**
	 * Returns the card style. Is made into a method for the sake
	 * of uniformity only.
	 *
	 * @return string|null
	 */
	private function getStyleTag(): ?string
	{
		return str::getAttrTag("style", $this->style);
	}

	/**
	 * Get any data keys.
	 * This is where draggable/resizable settings are also set.
	 *
	 * @return string|null
	 */
	private function getDataTag(): ?string
	{
		$draggable = $this->getDraggableData();
		$resizable = $this->getResizableData();
		if($draggable !== NULL){
			$this->data['draggable'] = $draggable;
		}
		if($resizable !== NULL){
			$this->data['resizable'] = $resizable;
		}
		return str::getDataAttr($this->data, true);
	}

	/**
	 * @return bool|string
	 */
	private function getImgHTML()
	{
		return Img::generate($this->img);
	}

	/**
	 * @return bool|string
	 * @throw Exception
	 */
	public function getItemsHTML(): ?string
	{
		if(!$this->items){
			return NULL;
		}

		$html = ListGroup::generate($this->items);

		$id = str::getAttrTag("id", $this->items['id']);
		$class_array = str::getAttrArray($this->items['class'], "container card-items", $this->items['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->items['style']);
		$script = str::getScriptTag($this->items['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
	}

	/**
	 * @return bool|string
	 * @throws Exception
	 */
	public function getRowsHTML()
	{
		if(!$html = Grid::generateRows($this->rows)){
			return false;
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
	private function getScriptHTML(?bool $as_tag = NULL): ?string
	{
		if($as_tag){
			return str::getScriptTag($this->script);
		}
		return $this->script;
	}

	/**
	 * Returns a card as HTML.
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getHTML(): string
	{
		return /** @lang HTML */ <<<EOF
<div
	{$this->getId(true)}
	{$this->getDataTag()}
	{$this->getClassTag()}
	{$this->getStyleTag()}
>
	{$this->getHeaderHTML()}
	{$this->getImgHTML()}
	{$this->getBodyHTML()}
	{$this->getRowsHTML()}
	{$this->getItemsHTML()}
	{$this->getFooterHTML()}	
	{$this->getScriptHTML(true)}
</div>
	{$this->getPostHTML()}
EOF;
	}

	/**
	 * If the car is to be draggable, will return an
	 * array with draggable settings. The array could be empty,
	 * if the card is to be draggable but have no custom
	 * settings. If NULL is returned, assume no dragging is
	 * necessary.
	 *
	 * This method should *NOT* be used, and instead,
	 * the Window() class should be used for a better UI
	 * experience.
	 *
	 * @return array|null
	 */
	private function getDraggableData(): ?array
	{
		if(!$this->draggable){
			return NULL;
		}

		if(is_array($this->draggable)){
			return $this->draggable;
		}
		else {
			return [];
		}
	}

	/**
	 * If the card is to be resizable, will return an array
	 * with resizable settings. The array could be empty,
	 * if the card is to be resizable but have no custom
	 * settings. If NULL is returned, assume no resizing is
	 * necessary.
	 *
	 * @return array|null
	 */
	private function getResizableData(): ?array
	{
		if($this->resizeable){
			//If resizable has been written incorrectly
			$this->resizable = $this->resizeable;
		}

		if(!$this->resizable){
			return NULL;
		}

		if(is_array($this->resizable)){
			return $this->resizable;
		}
		else {
			return [];
		}
	}
}