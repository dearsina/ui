<?php


namespace App\UI;


use App\Common\str;

class Page {
	private $title;
	private $subtitle;
	/**
	 * @var Grid
	 */
	private $grid;

	/**
	 * html constructor.
	 *
	 * <code>
	 * $page = new Page([
	 * 	"title" => "Stripe",
	 * 	"subtitle" => "Subtitle",
	 * 	"icon" => "icon",
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct ($a = NULL) {
		if(is_array($a)){
			foreach($a as $key => $val){
				$method = "set".ucwords($key);
				if (method_exists($this, $method)) {
					//if a custom setter method exists, use it
					$this->$method($val);
				} else {
					$this->$key = $val;
				}
			}
		}

		$this->grid = new Grid();
	}
	
	function setTitle($title){
		if($title === false){
			$this->title = [];
			return true;
		}
		if(!$title){
			return false;
		}
		if(is_array($title)){
			$this->title = array_merge($this->title?:[],$title);
		}
		$this->title["html"] = $title;
		return true;
	}

	function setSubtitle($subtitle){
		if($subtitle === false){
			$this->subtitle = [];
			return true;
		}
		if(!$subtitle){
			return false;
		}
		if(is_array($subtitle)){
			$this->subtitle = array_merge($this->subtitle?:[],$subtitle);
		}
		$this->subtitle["html"] = $subtitle;
		return true;
	}
	
	function setIcon($icon){
		if($icon === false){
			$this->title['icon'] = [];
			return true;
		}
		if(!$icon){
			return false;
		}
		$this->title['icon'] = $icon;
		return true;
	}
	
	function setSvg($svg){
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
	 */
	private function getTitleHTML(){
		if(!$this->title){
			//Titles are optional
			return false;
		}

		# ID
		$id = str::getAttrTag("id", $this->title['id']);
		
		# Icon
		$icon = Icon::generate($this->title['icon']);

		# SVG
		$svg = SVG::generate($this->title['svg'], "height:23px;");

		# Colour
		$colour = str::getColour($this->title['colour']);

		# Badge
		$badge = Badge::generate($this->title['badge']);

		# Tag
		$tag = $this->modal ? "span" : "h2";

		# Class
		$class_array = str::getAttrArray($this->title['class'], [$colour, "{$tag}-header"], $this->title['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $this->title['style']);
		
		return "<{$tag}{$id}{$class}{$style}>{$icon}{$svg} {$this->title['html']} {$badge}</{$tag}>";
	}

	/**
	 * Get the page subtitle.
	 *
	 * @return bool|string
	 */
	private function getSubtitleHTML(){
		if(!$this->subtitle){
			//subtitles are optional
			return false;
		}

		# ID
		$id = str::getAttrTag("id", $this->subtitle['id']);

		# Icon
		$icon = Icon::generate($this->subtitle['icon']);

		# SVG
		$svg = SVG::generate($this->subtitle['svg'], "height:23px;");

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
	 * Get the page Javascript,
	 * encased in a script tag.
	 *
	 * @return mixed
	 */
	private function getScriptHTML () {
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
	 * 	"sm" => "",
	 * 	"id" => "",
	 * 	"html" => ""
	 * ]);
	 * </code>
	 *
	 * @param $a
	 *
	 * @return bool
	 */
	public function setGrid($a){
		return $this->grid->set($a);
	}

	public function getHTML(){
		return <<<EOF
{$this->getScriptHTML()}
{$this->getTitleHTML()}
{$this->getSubtitleHTML()}
{$this->grid->getHTML()}
EOF;

	}
}