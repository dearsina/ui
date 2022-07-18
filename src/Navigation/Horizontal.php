<?php


namespace App\UI\Navigation;


use App\Common\href;
use App\Common\Img;
use App\Common\str;
use App\UI\Badge;
use App\UI\Dropdown;
use App\UI\Grid;
use App\UI\Icon;

/**
 * Class Horizontal
 *
 * Generates a 2 level horizontal HTML menu.
 *
 * @package App\UI\Navigation
 */
class Horizontal {
	private $levels;
	private $footers;

	/**
	 * Horizontal constructor.
	 *
	 * @param array|null $levels
	 * @param array|null $footers
	 */
	public function __construct(?array $levels, ?array $footers)
	{
		$this->levels = $levels;
		$this->footers = $footers;
	}

	/**
	 * Generates the level 1 navigation bar on the very top.
	 *
	 * @return string
	 */
	private function getLevel1HTML()
	{
		$brand = $this->getBrandHtml($this->levels[1]['title'], "navbar-level1-logo");
		$items = Dropdown::generateRootUl($this->levels[1]['items']);

		return <<<EOF
<div class="nav-scroller fixed-top" id="navbar-level1">
  <nav class="nav">
  	{$brand}
	<div id="navbar-level1-items">
		<button id="navbar-level2-toggle-button" class="navbar-toggler p-0 border-0" type="button">
			<i class="fa-light fa-bars fa-fw" aria-hidden="true"></i>
		</button>
		{$items}
	</div>
  </nav>
</div>
EOF;
	}

	/**
	 * Generates the level 2 navigation bar below level 1.
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private function getLevel2HTML(): ?string
	{
		# If there are no items, omit the entire level 2 navbar
		if(!$items = Dropdown::generateRootUl($this->levels[2]['items'])){
			return NULL;
		}

		return <<<EOF
<nav class="navbar navbar-expand-lg fixed-top nav-scroller" id="navbar-level2">
	<div class="container-fluid">
		<span></span>
		<div class="navbar-collapse offcanvas-collapse" id="navbar-level2-items">
			{$items}
		</div>
	</div>
</nav>
EOF;
	}

	/**
	 * Generates the brand text or image
	 *
	 * @param $a
	 *
	 * @param $default_class
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Exception
	 * @throws \Exception
	 */
	private function getBrandHtml($a, $default_class)
	{
		if(!$a){
			return false;
		}

		if(!is_array($a)){
			$a = ["title" => $a];
		}

		extract($a);

		if($href = href::generate($a)){
			$tag = "a";
		}
		else {
			$tag = "span";
		}

		$img = Img::generate($a);

		$id = str::getAttrTag("id", $a['id']);
		$icon = Icon::generate($a['icon']);
		$title = $a['title'];
		$badge = Badge::generate($a['badge']);

		$class_array = str::getAttrArray($a['class'], $default_class, $a['only_class']);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $a['style']);

		return "<{$tag}{$id}{$class}{$style}{$href}>{$img}{$icon}{$title}{$badge}</{$tag}>";
	}

	/**
	 * Generates the HTML for ui-navigation.
	 *
	 * @return string
	 */
	public function getHTML(): string
	{
		$html .= $this->getLevel1HTML();
		$html .= $this->getLevel2HTML();
		return $html;
	}

	/**
	 * Generates the HTML for the ui-footer.
	 *
	 * @return string
	 */
	public function getFooterHTML(): string
	{
		$html .= $this->getLevel2FooterHTML();
		$html .= $this->getLevel1FooterHTML();
		return $html;
	}

	/**
	 * @return string
	 */
	private function getLevel2FooterHTML(): string
	{
		if(!$this->footers[2]){
			return false;
		}

		foreach($this->footers[2] as $id => $col){
			if($col['items']){
				$items = "<ul>";
				foreach($col['items'] as $item){
					$href = href::generate($item);
					$items .= "<li><a{$href}>{$item['title']}</a></li>";
				}
				$items .= "</ul>";
			}
			else {
				$items = false;
			}

			$col['html'] = "<h5>{$col['title']}</h5>{$col['html']}{$items}";

			$cols[] = $col;
		}

		$grid = new Grid();

		$grid->set($cols);

		return <<<EOF
<div class="footer-main">
	<div class="container">
		{$grid->getHTML()}	
	</div>
</div>
EOF;
	}

	/**
	 * Like with the top navigation bar,
	 * level 1 is smaller than level 2.
	 *
	 * @return string
	 */
	private function getLevel1FooterHTML(): string
	{
		if(!$this->footers[1]){
			return false;
		}

		$grid = new Grid([
			"unstackable" => true,
		]);

		$grid->set($this->footers[1]);

		return <<<EOF
<div class="footer-footer">
	<div class="container">
		{$grid->getHTML()}	
	</div>
</div>
EOF;
	}


}