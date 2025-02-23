<?php


namespace App\UI;


use App\Common\str;

/**
 * Class Page
 * @package App\UI
 */
class Page {
	private ?int $sm = NULL;
	private $title;
	private $subtitle;
	private $script;
	private $button;
	private $row_class;

	/**
	 * @return mixed
	 */
	public function getButtonHTML()
	{
		return Button::generate($this->button);
	}

	/**
	 * @param mixed $button
	 */
	public function setButton($button): void
	{
		if(str::isNumericArray($button)){
			foreach($button as $b){
				$this->button[] = $b;
			}
		}
		else {
			$this->button[] = $button;
		}
	}

	/**
	 * @var Grid
	 */
	private $grid;

	/**
	 * html constructor.
	 *
	 * <code>
	 * $page = new Page([
	 *    "title" => "Stripe",
	 *    "subtitle" => "Subtitle",
	 *    "icon" => "icon",
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct($a = NULL)
	{
		if(is_array($a)){
			foreach($a as $key => $val){
				$method = "set" . ucwords($key);
				if(method_exists($this, $method)){
					//if a custom setter method exists, use it
					$this->$method($val);
				}
				else {
					$this->$key = $val;
				}
			}
		}

		$this->grid = new Grid();
	}

	/**
	 * @param mixed $title
	 *
	 * @return void
	 */
	function setTitle($title): void
	{
		# If the title is by design set to false, strip out any existing title key values
		if($title === false){
			$this->title = [];
			return;
		}

		# If no title is set, we're done
		if($title === NULL){
			return;
		}

		# If title is an array, add it wholesale
		if(is_array($title)){
			$this->title = $title;
			return;
		}

		# Otherwise, assume the passed title is the html string itself
		$this->title["html"] = $title;
	}

	function setSm($sm): void
	{
		if($sm === false){
			$this->sm = NULL;
			return;
		}

		if(!$sm){
			return;
		}

		$this->sm = $sm;
	}

	/**
	 * @param $subtitle
	 *
	 * @return bool
	 */
	function setSubtitle($subtitle)
	{
		if($subtitle === false){
			$this->subtitle = [];
			return true;
		}
		if(!$subtitle){
			return false;
		}
		if(is_array($subtitle)){
			$this->subtitle = array_merge($this->subtitle ?: [], $subtitle);
		}
		$this->subtitle["html"] = $subtitle;
		return true;
	}

	/**
	 * @param $icon
	 *
	 * @return bool
	 */
	function setIcon($icon)
	{
		if($icon === false){
			$this->title['icon'] = [];
			return true;
		}
		if(!$icon){
			return false;
		}
		if(is_string($icon)){
			$icon = [
				"name" => $icon,
			];
		}
		$this->title['icon'] = $icon;

		# The default icon thickness is light
		$this->title['icon']['type'] = $this->title['icon']['type'] ?: "light";
		return true;
	}

	/**
	 * @param $svg
	 *
	 * @return bool
	 */
	function setSvg($svg)
	{
		if($svg === false){
			$this->title['svg'] = [];
			return true;
		}
		if(!$svg){
			return false;
		}
		$this->title['svg'] = $svg;
		return true;
	}

	/**
	 * Return the title string + icon, wrapped in a title tag and colourised.
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Exception
	 */
	private function getTitleHTML()
	{
		if(!$this->title){
			//Titles are optional
			return false;
		}

		# ID
		$id = str::getAttrTag("id", $this->title['id']);

		# Icon
		$icon = Icon::generate($this->title['icon']);

		# SVG
		$svg = SVG::generate($this->title['svg'], [
			"height" => "23px",
		]);

		# Colour
		$colour = str::getColour($this->title['colour']);

		# Badge
		$badge = Badge::generate($this->title['badge']);

		# Class
		$class_array = str::getAttrArray($this->title['class'], [$colour, "h2-header"], $this->title['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $this->title['style']);

		return "<h2{$id}{$class}{$style}>{$icon}{$svg} {$this->title['html']} {$badge}</h2>";
	}

	/**
	 * Get the page subtitle.
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Exception
	 */
	private function getSubtitleHTML()
	{
		if(!$this->subtitle){
			//subtitles are optional
			return false;
		}

		# ID
		$id = str::getAttrTag("id", $this->subtitle['id']);

		# Icon
		$icon = Icon::generate($this->subtitle['icon']);

		# SVG
		$svg = SVG::generate($this->subtitle['svg'], [
			"height" => "23px",
		]);

		# Colour
		$colour = str::getColour($this->subtitle['colour']) ?: "text-muted";

		# Badge
		$badge = Badge::generate($this->subtitle['badge']);

		# Class
		$class_array = str::getAttrArray($this->subtitle['class'], ["subtitle", $colour], $this->subtitle['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $this->subtitle['style']);

		return "<div{$id}{$class}{$style}>{$icon}{$svg} {$this->subtitle['html']} {$badge}</div>";
	}

	/**
	 * Returns a container for heads-up messages.
	 *
	 * @return string
	 */
	private function getHeadsUpHTML(): string
	{
		return "<div class=\"headsup\"></div>";
	}

	/**
	 * Get the page Javascript,
	 * encased in a script tag.
	 *
	 * @return mixed
	 */
	private function getScriptHTML()
	{
		return str::getScriptTag($this->script);
	}

	/**
	 * Add one or many grid cells.
	 * Cells can be infinately nested.
	 *
	 * Skip a column by entering an empty (no html key) cell.
	 *
	 * <code>
	 * $page->setGrid([
	 *    "sm" => "",
	 *    "id" => "",
	 *    "html" => ""
	 * ]);
	 * </code>
	 *
	 * @param $a
	 *
	 * @return bool
	 */
	public function setGrid($a)
	{
		# Add a row class to the grid
		if($this->row_class && is_array($a)){
			// But only if a row class is supplied, and $a is an array

			if(str::isNumericArray($a)){
				$a = [
					"row_class" => $this->row_class,
					"html" => $a,
				];
			}

			else {
				$a['row_class'] = is_array($a['row_class'])? $a['row_class'] : [$a['row_class']];
				$a['row_class'][] = $this->row_class;
			}
		}

		return $this->grid->set($a);
	}

	/**
	 * @return string
	 */
	public function getHTML()
	{

		$html .= $this->getScriptHTML();

		if($this->sm){
			$html .= Grid::generate([[
				"row_class" => $this->row_class,
				"html" => [[
					"sm" => $this->sm,
					"html" => "&nbsp;",
				], [
					"html" => $this->getTitleHTML() . $this->getSubtitleHTML(),
				]]
			]]);
		}

		else {
			$html .= $this->getTitleHTML() . $this->getSubtitleHTML();
		}

		$html .= $this->getButtonHTML() . $this->getHeadsUpHTML() . $this->grid->getHTML();

		return $html;
	}
}