<?php


namespace App\UI\Navigation;


use App\Common\href;
use App\Common\Log;
use App\Common\Output;
use App\Common\str;
use App\UI\Badge;
use App\UI\Icon;

/**
 * Class NavigationConstructor
 *
 * This class only manages the building of the navigation HTML.
 * It does not manage what goes in to the navigation.
 *
 * Only use this class as a building block.
 *
 * @package App\UI\Navigation
 */
class NavigationConstructor {
	/**
	 * @var NavigationConstructor
	 */
	private $navigation;

	/**
	 * Contains level 1 and 2 titles and items
	 * @array|string
	 */
	protected $level;

	/**
	 * Classes
	 * @var output
	 */
	private $output;

	/**
	 * @var log
	 */
	public $log;

	/**
	 * @var NavigationConstructor
	 */
	private static $instance = null;

	/**
	 * The navigation tree is kept in a
	 * global variable so that it can be amended
	 * by any process before being produced as HTML
	 * by the $output class.
	 */
	private function __construct() {
		//The constructor is private so that the class can be run in static mode.
		$this->navigation = false;

		# Global navigation tree
		global $level;
		$this->level =& $level;

		$this->output = Output::getInstance();
		$this->log = Log::getInstance();
	}

	private function __clone() {
		//Stops the cloning of this object.
	}

	private function __wakeup() {
		//Stops the unserialising of this object.
	}

	/**
	 * @return NavigationConstructor
	 */
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new NavigationConstructor();

		}
		return self::$instance;
	}

	/**
	 * Generates the level 1 image title on the top left side of the screen.
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private function generate_level1_title_html($a){
		if(!$a){
			return false;
		}

		if(is_array($a)){
			extract($a);
			$href = href::generate($a);
			$src = $src ? "<img src=\"{$src}\">" : NULL;
		} else {
			//if $a is not an array, but a string, it's assumed that it's the text title
			$title = $a;
			$href = str::getAttrTag("href", "/");
		}

		$icon =  Icon::generate($icon);
		$badge = Badge::generate($badge);
		$class = str::getAttrTag("class", $class);
		$style = str::getAttrTag("style", $style);

		return <<<EOF
<a id="navbar-level1-logo"{$class}{$href}{$style}>{$icon}{$src}{$title}{$badge}</a>
EOF;
	}

	/**
	 * Generates the level 2 title on the left side of the screen.
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private function generate_level2_title_html($a){
		if(!$a){
			return false;
		}

		if(is_array($a)){
			extract($a);
			$href = href::generate($a);
		} else {
			$title = $a;
			$href = str::getAttrTag("href", "#");
		}

		$class = $class ? " {$class}" : "";
		$style = str::getAttrTag("style", $style);

		return <<<EOF
<a class="navbar-title{$class}"{$href}{$style}>{$title}</a>
EOF;
	}

	/**
	 * Generates a multilevel menu based on parent-children items.
	 *
	 * @param      $items
	 * @param null $ul
	 *
	 * @return bool|string
	 */
	private function generate_multilevel_items_html($items, $ul = NULL){
		if(!is_array($items)){
			return false;
		}

		foreach($items as $item){
			if($item['children'] || $ul) {
				//if the item has children, or is a top level item
				$item['class'] = ["parent", $item['class']];
			}

			if(!$href = href::generate($item)){
				$href = str::getAttrTag("href", "#");
			}
			$icon = Icon::generate($item['icon']);
			$badge = Badge::generate($item['badge']);

			if($item['disabled']){
				$item['class'] = [$item['class'], "disabled"];
				$href = str::getAttrTag("href", "#");
			}
			$class = str::getAttrTag("class", $item['class']);
			$style = str::getAttrTag("style", $item['style']);


			$html .= <<<EOF
<li{$class}{$style}>
	<a {$href}>{$icon}{$item['title']}{$badge}</a>
	{$this->generate_multilevel_items_html($item['children'])}
</li>
EOF;
		}

		# ul classes (applicable primarily to the top level)
		$class = str::getAttrTag("class", $ul['class']);
		$style = str::getAttrTag("style", $ul['style']);

		return "<ul{$class}{$style}>{$html}</ul>";

	}

	/**
	 * Add an an item to the level1 menu.
	 *
	 * @param $item
	 *
	 * @return bool
	 */
	protected function add_level1_item($item){
		$this->level[1]['items'][] = $item;
		return true;
	}

	/**
	 * Add an an item to the level2 menu.
	 *
	 * @param $item
	 *
	 * @return bool
	 */
	protected function add_level2_item($item){
		$this->level[2]['items'][] = $item;
		return true;
	}

	/**
	 * Generates the level 1 navigation bar on the very top.
	 *
	 * @return string
	 */
	public function generate_level1_html(){
		$html .= "<div id=\"navbar-level1\">";
		$html .= $this->generate_level1_title_html($this->level[1]['title']);
		$html .= "<div id=\"navbar-level1-buttons\">";
		$html .= $this->generate_multilevel_items_html($this->level[1]['items'], ["class" => "nav-right"]);
		$html .= "</div>";
		$html .= "</div>";
		return $html;
	}

	/**
	 * Generates the level 2 navigation bar below level 1.
	 * @return string
	 */
	public function generate_level2_html(){
		$html .= "<div id=\"navbar-level2\" class=\"navbar navbar-expand-md navbar-dark\">";
		$html .= $this->generate_level2_title_html($this->level[2]['title']);
		$html .= "<div class=\"navbar-sidebar-toggle\" onClick=\"$('#ui-sidebar-right').toggleClass('show');$(this).toggleClass('show');\"><i class=\"fal fa-bars\"></i></div>";
		$html .= "<div id=\"ui-sidebar-right\">";
		$html .= $this->generate_multilevel_items_html($this->level[2]['items'], ["class" => "nav-left"]);
		$html .= "</div>";
		$html .= "</div>";
		return $html;
	}

	/**
	 * Generates the HTML for ui-navigation.
	 *
	 * @return bool|string
	 */
	public function get_html () {
		$html .= $this->generate_level1_html();
		$html .= $this->generate_level2_html();
		return $html;
	}
}