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
	 * $html = new html([
	 * "title" => "Stripe",
	 * "subtitle" => "Subtitle",
	 * "icon" => [
	 * 	"type" => "brand",
	 * 	"name" => "stripe"
	 * ]
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct ($a = NULL) {
		if(is_array($a)){
			foreach($a as $key => $val){
				$method = "set_$key";
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
	
	function set_title($title){
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

	function set_subtitle($subtitle){
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
	
	function set_icon($icon){
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
	
	function set_svg($svg){
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
	private function get_title_html(){
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
		$class_array = str::getAttrArrray($this->title['class'], [$colour, "{$tag}-header"], $this->title['only_class']);
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
	private function get_subtitle_html(){
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
		$class_array = str::getAttrArrray($this->subtitle['class'], ["subtitle", $colour], $this->subtitle['only_class']);
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
	private function get_script () {
		return str::getScriptTag($this->script);
	}

	/**
	 * Add one or many grid cells.
	 * Cells can be infinately nested.
	 *
	 * Skip a column by entering an empty (no html key) cell.
	 *
	 * <code>
	 * $page->set_grid([
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
	public function set_grid($a){
		return $this->grid->set($a);
	}

	public function get_html(){
		return <<<EOF
{$this->get_script()}
{$this->get_title_html()}
{$this->get_subtitle_html()}
{$this->grid->get_html()}
EOF;

	}
}