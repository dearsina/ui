<?php


namespace App\UI\Modal;

use App\Common\Resizable\Resizable;
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
class Modal extends \App\Common\Prototype {
	private $id;
	private $elements = [];

	protected $icon;
	protected $draggable;
	protected $resizable;
	protected $resizeable;
	protected $approve;
	protected $dismissible;
	protected ?array $data = NULL;
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
	protected ?string $script = NULL;

	/**
	 * Create a modal
	 * <code>
	 *    $modal = new modal([
	 *    "id" => "id",
	 *    "size" => "xl",
	 *    "dismissible" => false,
	 *    "draggable" => true,
	 *    "resizable" => true,
	 *    "approve" => true,
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

		# If nothing is passed, just set the modal ID
		if(!is_array($a)){
			$this->setId();
			return true;
		}

		# Set the attributes
		$this->setAttr($a);

		return true;
	}

	/**
	 * Set an ID for the modal.
	 *
	 * If the ID has been given explicitly, use that.
	 * Otherwise, generate an id based on the calling class:
	 * modal-[class]-[function]
	 *
	 * Each modal must have an ID.
	 *
	 * @param null     $id
	 * @param int|null $n The steps back to take in the debug trace to find the calling class
	 *
	 * @return bool
	 */
	public function setId($id = NULL, ?int $n = 3): void
	{
		# If an ID has been explicitly set, use that
		if($id){
			$this->id = $id;
			return;
		}

		# If the ID has explicitly been set to false, don't use an ID
		if($id === false){
			$this->id = false;
			return;
		}

		# Otherwise, generate an ID based on who called the modal
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $n + 1);

		# If the modal was called from a prototype, use the filename as a faux class name
		if($trace[$n]['class'] == "App\\UI\\Modal\\Prototype"){
			# Convert the file path to a class path and remove the .php file suffix
			$trace[$n]['class'] = str_replace(".php", "", str_replace("/", "\\", $trace[$n]['file']));
		}

		# If for some reason there still isn't a class name, just use a generic ID
		if(!$full_class_name = $trace[$n]['class']){
			$this->id = $id ?: str::id("modal");
			return;
		}

		# Get the class name, ignore the Modal class name
		$class_name_parts = explode("\\", $full_class_name);
		$class_name = array_pop($class_name_parts);
		if($class_name == "Modal"){
			$class_name = array_pop($class_name_parts);
		}

		# Get the function name
		$function = $trace[$n]['function'];

		# Build the ID
		$id_parts[] = "modal";
		$id_parts[] = str::camelToSnakeCase($class_name, "-");
		$id_parts[] = str::camelToSnakeCase($function, "-");
		$this->id = implode("-", $id_parts);
	}

	/**
	 * Set the modal header.
	 * Will replace existing headers.
	 *
	 * @param mixed $a Can be an array or a string, if set to false, will clear the header
	 *
	 * @return bool
	 */
	public function setHeader($a = NULL)
	{
		# Array
		if(is_array($a)){
			$this->elements['header'] = $a;
			return true;
		}

		# Mixed
		if($a){
			$this->elements['header']['title'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->elements['header'] = [];
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
	public function getHeaderHTML()
	{
		if(!is_array($this->elements['header'])){
			// Headers are optional
			return false;
		}

		# Header buttons can also be defined outside of the header key when defining modal vales.
		$this->elements['header']['buttons'] = array_merge($this->elements['header']['buttons'] ?: [], $this->buttons ?: []);

		# Add the required Bootstrap header class very first
		$this->elements['header']['class'] = str::getAttrArray($this->elements['header']['class'], "modal-header", $this->elements['header']['only_class']);

		# Draggable
		$this->elements['header']['class'][] = $this->draggable ? "modal-header-draggable" : false;

		# Styles
		$this->elements['header']['style'] = str::getAttrArray($this->elements['header']['style'], NULL, $this->elements['header']['only_style']);

		# Dropdown buttons
		if($this->elements['header']['buttons']){
			$buttons = Button::get($this->elements['header']);
		}

		# Button(s) in a row
		$button = Button::generate($this->elements['header']['button']);

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Accent
		$this->elements['header']['class'][] = str::getColour($this->accent, "bg");

		# Icon
		if(!$icon = Icon::generate($this->elements['header']['icon'])){
			//the icon attribute can either be in the header or in the main modal
			$icon = Icon::generate($this->icon);
		}

		# Badge
		$badge = Badge::generate($this->elements['header']['badge']);

		# ID
		$id = str::getAttrTag("id", $this->elements['header']['id']);

		# Style
		$style = str::getAttrTag("style", $this->elements['header']['style']);

		# Title colour
		$class[] = str::getColour($this->elements['header']['colour']);

		# Script
		$script = str::getScriptTag($this->elements['header']['script']);

		# The header title itself
		$title = $this->elements['header']['header'] . $this->elements['header']['title'] . $this->elements['header']['html'];

		# Title class
		if(!empty(array_filter($class))){
			$title_class = str::getAttrTag("class", $class);
			$title = "<span{$title_class}>$title</span>";
		}

		# The div class
		$class = str::getAttrTag("class", $this->elements['header']['class']);

		# If the modal can be dismissed
		if($this->dismissible !== false){
			$dismiss = <<<EOF
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" title="Close this window"></button>
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
	public function getFooterHTML()
	{
		if(!is_array($this->elements['footer'])){
			// Footers are optional
			return false;
		}

		# Add the required Bootstrap footer class very first
		$this->elements['footer']['class'] = str::getAttrArray($this->elements['footer']['class'], "modal-footer", $this->elements['footer']['only_class']);

		# Styles
		$this->elements['footer']['style'] = str::getAttrArray($this->elements['footer']['style'], NULL, $this->elements['footer']['only_style']);

		# Dropdown buttons
		if($this->elements['footer']['buttons']){
			$buttons = Button::get($this->elements['footer']);
		}

		# Button(s) in a row
		$button = Button::generate($this->elements['footer']['button']);

		if($button){
			$button = "<div class=\"btn-float-right\">{$button}</div>";
		}

		# Icon
		$icon = Icon::generate($this->elements['footer']['icon']);

		# Badge
		$badge = Badge::generate($this->elements['footer']['badge']);

		# ID
		$id = str::getAttrTag("id", $this->elements['footer']['id']);

		# Style
		$style = str::getAttrTag("style", $this->elements['footer']['style']);

		# Text colour
		$class[] = str::getColour($this->elements['footer']['colour']);

		# Draggable
		$class[] = $this->draggable ? "modal-footer-draggable" : false;

		# Script
		$script = str::getScriptTag($this->elements['footer']['script']);

		# The div class
		$class = str::getAttrTag("class", $this->elements['footer']['class']);

		# Only include left side if it has values (or a custom ID)
		if(($left = $icon . $this->elements['footer']['html'] . $badge) || $id){
			$left = "<div class=\"col-auto\">{$left}</div>";
		}

		# Only include right side if it has values (buttons)
		if($right = $buttons . $button){
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
	public function setFooter($a = NULL)
	{
		# Array
		if(is_array($a)){
			$this->elements['footer'] = $a;
			return true;
		}

		# Mixed
		if($a){
			$this->elements['footer']['html'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->elements['footer'] = [];
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
	public function setBody($a = NULL)
	{
		# Array
		if(is_array($a)){
			$this->elements['body'] = $a;
			$this->elements['body']['id'] = $this->elements['body']['id'] ?: str::id("body");
			return true;
		}

		# Mixed
		if($a){
			$this->elements['body']['html'] = $a;
			$this->elements['body']['id'] = str::id("body");
			return true;
		}

		# Clear
		if($a === false){
			$this->elements['body'] = [];
			return true;
		}

		return true;
	}

	/**
	 * @param null $a
	 */
	public function setRows($a = NULL): void
	{
		if(!is_array($a)){
			return;
		}

		$this->elements['rows'] = $a;
	}

	/**
	 * @param null $a
	 */
	public function setItems($a = NULL): void
	{
		if(!is_array($a)){
			return;
		}

		$this->elements['items'] = $a;
	}

	/**
	 * @param null $a
	 */
	public function setTabs($a = NULL): void
	{
		if(!is_array($a)){
			return;
		}

		$this->elements['tabs'] = $a;
	}

	/**
	 * Returns the body of the modal.
	 * Not optional.
	 *
	 * @return bool|string
	 */
	public function getBodyHTML()
	{
		if(!$this->elements['body']){
			return false;
		}

		if(is_array($this->elements['body']['html'])){
			$this->elements['body']['html'] = Grid::generate($this->elements['body']['html']);
		}

		$id = str::getAttrTag("id", $this->elements['body']['id']);
		$class = str::getAttrTag("class", ["modal-body", $this->elements['body']['class']]);
		$style = str::getAttrTag("style", $this->elements['body']['style']);
		$progress = Progress::generate($this->elements['body']['progress']);
		$script = str::getScriptTag($this->elements['body']['script']);

		return "<div{$class}{$id}{$style}>{$progress}{$this->elements['body']['html']}</div>{$script}";
	}

	/**
	 * @return false|string
	 */
	public function getRowsHTML(): ?string
	{
		if(empty($this->elements['rows'])){
			return NULL;
		}

		if(!key_exists("rows", $this->elements['rows'])){
			$this->elements['rows'] = [
				"rows" => $this->elements['rows'],
			];
		}

		if(!is_array($this->elements['rows']['rows'])){
			return NULL;
		}

		foreach($this->elements['rows']['rows'] as $key => $val){
			$left = [
				"class" => "small",
				"sm" => $this->elements['rows']['sm'],
				"html" => $key,
			];
			$rows[] = [$left, $val];
		}

		if(is_array($rows)){
			$html = Grid::generate($rows);
		}

		$id = str::getAttrTag("id", $this->elements['rows']['id']);
		$class_array = str::getAttrArray($this->elements['rows']['class'], "container card-rows", $this->elements['rows']['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->elements['rows']['style']);
		$script = str::getScriptTag($this->elements['rows']['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
	}

	public function getTabsHTML(): ?string
	{
		if(empty($this->elements['tabs'])){
			return NULL;
		}

		$html = Grid::generate([[
			"tabs" => [
				"tabs" => $this->elements['tabs'],
				"class" => $this->draggable ? "modal-header-draggable" : NULL
			]
		]]);

		$class = str::getAttrTag("class", ["modal-tabs"]);

		return "<div{$class}{$style}>{$html}</html>";
	}

	/**
	 * @return false|string
	 */
	public function getItemsHTML(): ?string
	{
		if(empty($this->elements['items'])){
			return NULL;
		}

		if(!key_exists("items", $this->elements['items'])){
			$this->elements['items'] = [
				"items" => $this->elements['items'],
			];
		}

		$html = ListGroup::generate($this->elements['items']);

		$id = str::getAttrTag("id", $this->elements['items']['id']);
		$class_array = str::getAttrArray($this->elements['items']['class'], "container card-items", $this->elements['items']['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->elements['items']['style']);
		$script = str::getScriptTag($this->elements['items']['script']);

		return "<div{$class}{$id}{$style}>{$html}</div>{$script}";
	}

	/**
	 * Return the ID.
	 *
	 * @param bool $as_tag If set to TRUE return the ID as an HTML tag.
	 *
	 * @return bool|string
	 */
	private function getId($as_tag = NULL)
	{
		if(!$this->id && $this->id !== false){
			$this->setId();
		}

		if($as_tag){
			return str::getAttrTag("id", $this->id);
		}
		return $this->id;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	function getHTML()
	{
		switch($this->size) {
		case 'xs':
		case 's':
		case 'small':
			$size = "modal-sm";
			break;
		case 'm':
		case 'medium':
			$size = "modal-md";
			break;
		case 'l':
		case 'lg':
		case 'large':
			$size = "modal-lg";
			break;
		case 'xl':
			$size = "modal-xl";
			break;
		case 'xxl':
			$size = "modal-xxl";
			break;
		}
		$parent_class_array = str::getAttrArray($this->parent_class, "modal", $this->only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);
		$parent_style = str::getAttrTag("style", $this->parent_style);

		$class_array = str::getAttrArray($this->class, ["modal-dialog", "modal-dialog-centered", "modal-dialog-scrollable", $size], $this->only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $this->style);

		$child_class_array = str::getAttrArray($this->child_class, "modal-content", $this->only_child_class);
		$child_class = str::getAttrTag("class", $child_class_array);
		$child_style = str::getAttrTag("style", $this->child_style);

		$data = str::getDataAttr($this->getModalDataAttr(), true);

		# You either have tabs, or you have header/body/rows/items, not both
		if(!$html = $this->getTabsHTML()){
			$html = $this->getHeaderHTML()
				. $this->getBodyHTML()
				. $this->getRowsHTML()
				. $this->getItemsHTML();

		}

		return /** @lang HTML */ <<<EOF
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
        	{$html}
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
	private function getModalDataAttr()
	{
		$modal['show'] = true;

		if($this->dismissible === false){
			$modal['backdrop'] = "static";
		}

		# If there are logged dimensions for this modal for this user, use them
		Resizable::setDimensions($this->data, $this->id);

		return array_merge($this->data ?:[], [
			"settings" => $modal,
			"draggable" => $this->getDraggableSettings(),
			"resizable" => $this->getResizableSettings(),
			"approve" => $this->getApproveSettings(),
		]);
	}

	/**
	 * Returns all Javascript settings related to the modal.
	 *
	 * @param bool $as_tag If set, will return enclosed with script tag.
	 *
	 * @return bool|string
	 */
	private function getScriptHTML($as_tag = false): ?string
	{
		if($as_tag){
			return str::getScriptTag($this->script);
		}
		return $this->script;
	}

	/**
	 * If set, closing of the modal will need approval by the user.
	 *
	 * @return array|bool
	 */
	private function getApproveSettings()
	{
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
		$message = str_replace(["\r\n", "\r", "\n"], " ", $message);

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
					"btnClass" => $button_colour,
				],
			],
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
		if(!$this->draggable){
			return false;
		}

		if(is_array($this->draggable)){
			return $this->draggable;
		}
		else {
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
		if(!$this->resizable){
			return false;
		}

		# Even if there are no custom settings, we still need to return an array
		return is_array($this->resizable) ? $this->resizable : [];
	}
}
