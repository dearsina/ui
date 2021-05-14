<?php


namespace App\UI;

use App\Common\Exception\BadRequest;
use App\Common\href;
use App\Common\str;
use App\UI\Card\Card;

/**
 * Class Grid
 * @package App\UI
 */
class Grid {
	private $grid;
	private $unstackable;
	private $formatter;

	/**
	 * Format and return content in a HTML grid.
	 * <code>
	 * $grid = new Grid([
	 *    "unstackable" => FALSE, //If set to TRUE will make the grid unstackable on small screens
	 *    "formatter" => FALSE //If set to an anonymous function, will use that function to format the HTML per cell.
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct($a = NULL)
	{
		$this->unstackable = $a['unstackable'];
		$this->formatter = $a['formatter'];
	}

	/**
	 * Add one or many grid cells.
	 * Cells can be infinately nested.
	 *
	 * Skip a column by entering an empty (no html key) cell.
	 *
	 * <code>
	 * $grid = new Grid();
	 * $grid->set([
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
	public function set($a)
	{
		if(!$a){
			return false;
		}

		$this->grid[] = $a;
		return true;
	}

	/**
	 * Formats and returns the HTML of one row (with one or many cells).
	 * To format (id/class/style) a row, prefix the attr with "row_".
	 *
	 * @param $rows
	 *
	 * @return string
	 */
	private function getRowHTML($rows)
	{
		foreach($rows as $row){
			if(!is_array($row)){
				$row_html = $this->getColHTML(["html" => $row]);
				$row = [];
			} else if(str::isNumericArray($row)){
				$row_html = $this->getColHTML($row);
			} else if(str::isNumericArray($row['html'])){
				$row_html = $this->getColHTML($row['html']);
			} else if($row['html']){
				$row_html = $this->getColHTML([$row['html']]);
			} else {
				$row_html = $this->getColHTML([$row]);
			}

			# ID
			$id_tag = str::getAttrTag("id", $row['row_id']);

			# Class
			$class_array = str::getAttrArray($row['row_class'], "row", $row['only_row_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Style
			$style_tag = str::getAttrTag("style", $row['row_style']);

			# Data
			$data = str::getDataAttr($row['row_data']);

			$html .= "<div{$id_tag}{$class_tag}{$style_tag}{$data}>{$row_html}</div>";
		}

		return $html;
	}

	/**
	 * Formats and returns the HTML of one column cell.
	 *
	 * @param $cols
	 *
	 * @return string
	 */
	private function getColHTML($cols)
	{
		foreach($cols as $col){
			//for each item in the row
			if(!is_array($col)){
				$col_html = $col;
				$col = [];
			} else if(str::isNumericArray($col)){
				//if it goes deeper (without other metadata)
				$col_html = $this->getRowHTML($col);
			} else if(str::isNumericArray($col['html'])){
				//if it goes deeper (with metadata)
				$col_html = $this->getRowHTML($col['html']);
			} else if(is_array($col['tabs'])){
				$col_html = $this->getTabsHTML($col['tabs']);
			} else if($this->formatter){
				//If a custom formatter has been designated
				$col_html = ($this->formatter)($col);
			} else if($col['accordion']){
				//if the cell is an accordion
				$col_html = Accordion::generate($col['accordion']);
			} else {
				$col_html = self::generateTitle($col['title']);
				$col_html .= self::generateBody($col['body']);
				$col_html .= $col['html'];
			}

			# If they're not to be stacked, make it so (default is stackable)
			$col_width = $this->unstackable ? "col" : "col-sm";

			# Buttons
			if($buttons = Button::get($col)){
				$col_width = "col-sm";
			}

			# Has a fixed column width been requested
			if($col['sm']){
				$col_width .= "-{$col['sm']}";
			}

			# ID
			$id_tag = str::getAttrTag("id", $col['id'] ?: $col['col_id']);

			# Icon
			$icon = Icon::generate($col['icon']);

			# Copy
			$copy = Copy::generate($col['copy'], $col_html);

			# Class
			$class_array = str::getAttrArray($col['class'], $col_width, $col['only_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Styles
			$style_tag = str::getAttrTag("style", $col['style']);

			# Data value (used for sorting)
			$data_value = $this->getDataValueTag($col);
			$data = str::getDataAttr($col['data']);

			# Hash, URI, onClick
			if($href = href::generate($col)){
				$tag = "a";
				if($buttons){
					//If both hash and a button are found in the same cell
					$html .= "<div{$class_tag}{$id_tag}{$style_tag}{$data_value}{$data}><a{$href}>{$icon}{$col_html}{$copy}</a>{$buttons}</div>";
					//Breaks down the tag into two different tags, one remains a div with all the attributes, the other a child a tag with only the href attr
					continue;
				}

			} else {
				$tag = "div";
			}

			$html .= "<{$tag}{$href}{$id_tag}{$class_tag}{$style_tag}{$data_value}{$data}>{$icon}{$buttons}{$col_html}{$copy}</{$tag}>";
		}

		return $html;
	}

	private function getTabHeader(array $tab, ?string $active): string
	{
		if(is_array($tab['header'])){
			extract($tab['header']);
		} else if ($tab['header']){
			$title = $tab['header'];
		} else if(!$tab['icon']){
			throw new BadRequest("All tabs must have a header or an icon.");
		}

		# Id
		$id = str::getAttrTag("id", "{$tab['id']}-tab");
		//A designated title ID (in the title array) is disregarded

		# Disabled
		if($tab['disabled']){
			$disabled = "disabled";
		}

		# Class
		$class_array = str::getAttrArray($class, ["nav-link", $active, $disabled], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Icon
		$icon = $icon ?: $tab['icon'];
		if($icon){
			if(is_string($icon)){
				$icon = [
					"name" => $icon,
					"type" => "thin"
				];
			}
			$icon = Icon::generate($icon);
		}

		# Badge
		$badge = Badge::generate($badge ?: $tab['badge']);

		# Style
		$style = str::getAttrTag("style", $style);

		return <<<EOF
<a
	{$id}
	{$class}
	{$style}
	data-bs-toggle="tab"
	href="#{$tab['id']}" 
	role="tab" 
	aria-controls="{$tab['id']}" 
	aria-selected="true"
>
	{$icon}
	{$title}
	{$html}
	{$badge}
</a>
EOF;
	}

	/**
	 * Formats and returns the body of a single tab.
	 * 
	 * @param array $tab
	 *
	 * @return string|null
	 */
	private function getTabBody(array $tab): ?string
	{
		if(!$tab['body']){
			return NULL;
		}

		if(!is_array($tab['body'])){
			$body = [
				"html" => $tab['body']
			];
		} else {
			$body = $tab['body'];
		}

		$id = str::getAttrTag("id", $body['id']);
		$classArray = str::getAttrArray($body['class'], "body", $body['only_class']);
		$class = str::getAttrTag("class", $classArray);
		$style = str::getAttrTag("style", $body['style']);
		$data = str::getDataAttr( $body['data']);

		return "<div{$id}{$class}{$style}{$data}>{$this->getHTML([$body['html']])}</div>";
	}

	/**
	 * Formats and return the footer of a single tab.
	 *
	 * @param array $tab
	 *
	 * @return string|null
	 */
	private function getTabFooter(array $tab): ?string
	{
		return (new Card())->getFooterHTML($tab['footer']);
		//We're borrowing from the Card class
	}

	public function getTabsHTML(array $tabs): string
	{
		foreach($tabs as $tab){
			$tab['id'] = $tab['id'] ?: str::id("panel");
			$active = $active !== true ? "active" : NULL;
			$nav_tabs[] = "<li class=\"nav-item\" role=\"presentation\">{$this->getTabHeader($tab, $active)}</li>";

			$tab_panes[] = <<<EOF
			<div
				class="tab-pane {$active}"
				id="{$tab['id']}"
				role="tabpanel"
				aria-labelledby="{$tab['id']}-tab"
			>
				{$this->getTabBody($tab)}
				{$this->getTabFooter($tab)}
			</div>
EOF;
			# An active tab has now been set
			$active = true;
		}

		$tab_id = str::id("tab");
		$nav_tabs_html = implode("\r\n", $nav_tabs);
		$tab_panes = implode("\r\n", $tab_panes);

		return <<<EOF
<!-- Nav tabs -->
<ul class="nav nav-tabs" id="{$tab_id}" role="tablist">{$nav_tabs_html}</ul>
<!-- Tab panes -->
<div class="tab-content">{$tab_panes}</div>
<script>
var triggerTabList = [].slice.call(document.querySelectorAll('#{$tab_id} a'))
triggerTabList.forEach(function (triggerEl) {
  var tabTrigger = new bootstrap.Tab(triggerEl)

  triggerEl.addEventListener('click', function (event) {
    event.preventDefault()
    tabTrigger.show()
  })
});
</script>
EOF;

	}

	/**
	 * The data-value value is used to identify changes in
	 * modal forms, and warn the user if there has been a change
	 * that hasn't been saved.
	 *
	 * @param array $col
	 *
	 * @return string|null
	 */
	public function getDataValueTag(array $col): ?string
	{
		if($col['type'] == "checkbox" && !$col['checked']){
			//if this is a checkbox and it's not checked
			return NULL;
		}

		return str::getDataAttr(["value" => $col['value']]);
	}

	/**
	 * Returns formatted HTMl of the requested grid.
	 *
	 * @param null $a
	 *
	 * @return bool|string
	 */
	public function getHTML($a = NULL)
	{
		$grid = $a ?: $this->grid;

		if(!$grid){
			return false;
		}

		return $this->getRowHTML($grid);
	}

	/**
	 * Static method to quickly return a grid.
	 *
	 * @param array $cells An array of grid cells
	 *
	 * @return string Returns HTML
	 */
	public static function generate(array $cells)
	{
		$grid = new Grid();
		return $grid->getRowHTML($cells);
	}

	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];

		extract($a);

		# ID
		$id = str::getAttrTag("id", $id);

		# Icon
		$icon = Icon::generate($icon);

		# Badge
		if($badge = Badge::generate($badge)){
			$badge = " " . $badge;
		}

		# Button(s)
		$button = Button::generate($button);

		# Class
		$class_array = str::getAttrArray($class, "text-title", $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# If there is a link or action, that will ONLY apply to the title TEXT, not (optional) icons or badge
		if($href = href::generate($a)){
			$title = "<a{$href}>{$title}</a>";
		}

		# Alt
		$alt = str::getAttrTag("title", $alt);

		# Tag
		$tag = $tag ?: "div";

		return "<{$tag}{$id}{$class}{$style}{$href}{$alt}>{$icon}{$title}{$badge}{$button}</{$tag}>";
	}

	private static function generateBody($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["body" => $a];
		extract($a);

		$tag = $tag ?: "p";

		if($body != strip_tags($body)){
			//if the body contains HTML, it will be pushed out of the p tag, so we must switch to a div
			$tag = "div";
			$default_style = [
				"margin-top" => "0",
				"margin-bottom" => "1rem"
			];
			//And format it like a p
		}
		$id = str::getAttrTag("id", $id);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "text-body", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style_array = str::getAttrArray($style, $default_style, $only_style);
		$style = str::getAttrTag("style", $style_array);

		return "<{$tag}{$id}{$class}{$style}>{$body}{$button}</{$tag}>";
	}
}