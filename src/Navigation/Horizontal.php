<?php


namespace App\UI\Navigation;


use App\Common\href;
use App\Common\str;
use App\UI\Badge;
use App\UI\Icon;

/**
 * Class Horizontal
 *
 * Generates a 2 level horizontal HTML menu.
 *
 * @package App\UI\Navigation
 */
class Horizontal {
	public function __construct ($levels) {
		$this->levels = $levels;
	}

	/**
	 * Generates the level 1 navigation bar on the very top.
	 *
	 * @return string
	 */
	public function getLevel1HTML(){
		$html .= "<div id=\"navbar-level1\">";
		$html .= $this->getTitleHTML($this->levels[1]['title'], "navbar-level1-logo");
		$html .= "<div id=\"navbar-level1-buttons\">";
		$html .= $this->getMultiLevelItemsHTML($this->levels[1]['items'], ["class" => "nav-right"]);
		$html .= "</div>";
		$html .= "</div>";
		return $html;
	}

	/**
	 * Generates the level 2 navigation bar below level 1.
	 * @return string
	 */
	public function getLevel2HTML(){
		$html .= "<div id=\"navbar-level2\" class=\"navbar navbar-expand-md navbar-dark\">";
		$html .= $this->getTitleHTML($this->levels[2]['title'], "navbar-title");
		$html .= "<div class=\"navbar-sidebar-toggle\" onClick=\"$('#ui-sidebar-right').toggleClass('show');$(this).toggleClass('show');\"><i class=\"fal fa-bars\"></i></div>";
		$html .= "<div id=\"ui-sidebar-right\">";
		$html .= $this->getMultiLevelItemsHTML($this->levels[2]['items'], ["class" => "nav-left"]);
		$html .= "</div>";
		$html .= "</div>";
		return $html;
	}

	/**
	 * Generates titles
	 *
	 * @param $a
	 *
	 * @return bool|string
	 */
	private function getTitleHTML($a, $default_class){
		if(!$a){
			return false;
		}

		if(!is_array($a)){
			$a = ["title" => $a];
		}

		if($href = href::generate($a)){
			$tag = "a";
		} else {
			$tag = "span";
		}
		$img = $src ? "<img src=\"{$src}\">" : false;
		$id = str::getAttrTag("id", $a['id'], str::id($default_class));
		$icon = Icon::generate($a['icon']);
		$title = $a['title'];
		$badge = Badge::generate($a['badge']);
		$class_array = str::getAttrArray($a['class'], $default_class, $a['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $a['style']);

		return "<{$tag}{$id}{$class}{$style}{$href}>{$img}{$icon}{$title}{$badge}</{$tag}>";
	}

	/**
	 * Generates a multilevel menu based on parent-children items.
	 *
	 * @param      $items
	 * @param null $ul
	 *
	 * @return bool|string
	 */
	private function getMultiLevelItemsHTML($items, $ul = NULL){
		if(!is_array($items)){
			return false;
		}

		foreach($items as $item){
			if(empty($item)){
				continue;
			}

			if($item['children'] || $ul) {
				//if the item has children, or is a top level item
				$item['class'] = ["parent", $item['class']];
			}

			if(!$href = href::generate($item)){
//				$href = str::getAttrTag("href", "#");
			}
			$icon = Icon::generate($item['icon']);
			$badge = Badge::generate($item['badge']);

			if($item['disabled']){
				$item['class'] = [$item['class'], "disabled"];
//				$href = str::getAttrTag("href", "#");
			}
			$class = str::getAttrTag("class", $item['class']);
			$style = str::getAttrTag("style", $item['style']);

			# Hovertext
			$title = str::getAttrTag("title", $item['alt']);


			$html .= <<<EOF
<li{$class}{$style}{$title}>
	<a {$href}>{$icon}{$item['title']}{$badge}</a>
	{$this->getMultiLevelItemsHTML($item['children'])}
</li>
EOF;
		}

		# ul classes (applicable primarily to the top level)
		$class = str::getAttrTag("class", $ul['class']);
		$style = str::getAttrTag("style", $ul['style']);

		return "<ul{$class}{$style}>{$html}</ul>";

	}

	/**
	 * Generates the HTML for ui-navigation.
	 *
	 * @return string
	 */
	public function getHTML () {
		$html .= $this->getLevel1HTML();
		$html .= $this->getLevel2HTML();
		return $html;
	}
}