<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;
use App\UI\Tab\Tabs;

/**
 * Class Grid
 * @package App\UI
 */
class Grid {
	private $grid;
	private $unstackable;
	private ?object $formatter;

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
			}

			else if(str::isNumericArray($row)){
				$row_html = $this->getColHTML($row);
			}

			else if(str::isNumericArray($row['html'])){
				$row_html = $this->getColHTML($row['html']);
			}

			else if($row['html']){
				$row_html = $this->getColHTML([$row['html']]);
			}

			else {
				$row_html = $this->getColHTML([$row]);
			}

			# ID
			$id_tag = str::getAttrTag("id", $row['row_id']);

			# Class
			$class_array = str::getAttrArray($row['row_class'], "row", $row['only_row_class']);

			# Splits
			if($row['split']){
				$class_array[] = "col-split";
			}

			# Class tag
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
			}

			else if(str::isNumericArray($col)){
				//if it goes deeper (without other metadata)
				$col_html = $this->getRowHTML($col);
			}

			else if(str::isNumericArray($col['html'])){
				//if it goes deeper (with metadata)
				$col_html = $this->getRowHTML($col['html']);
			}

			else if(is_array($col['tabs']) && array_filter($col['tabs'])){
				//If tabs exist (throw in the optional formatter also)
				$col_html = Tabs::generate($col['tabs'], $this->formatter);
			}

			else if($this->formatter){
				//If a custom formatter has been designated
				$col_html = ($this->formatter)($col);
			}

			else if($col['accordion']){
				//if the cell is an accordion
				$col_html = Accordion::generate($col['accordion']);
			}

			else {
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

			# Alt (title)
			$title = str::getAttrTag("title", $col['alt']);

			# Copy
			$copy = Copy::generate($col['copy'], $col_html);

			# Badges
			$col_html .= Badge::generate($col['badge']);

			# Class
			$class_array = str::getAttrArray($col['class'], $col_width, $col['only_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Styles
			$style_tag = str::getAttrTag("style", $col['style']);

			# Data value (used for sorting)
			$data_value = $this->getDataValueTag($col);
			$data = str::getDataAttr($col['data'], true);

			# Hash, URI, onClick
			if($href = href::generate($col)){
				$tag = "a";
				if($buttons){
					//If both hash and a button are found in the same cell
					$html .= "<div{$class_tag}{$id_tag}{$style_tag}{$data_value}{$data}{$title}><a{$href}>{$icon}{$col_html}{$copy}</a>{$buttons}</div>";
					//Breaks down the tag into two different tags, one remains a div with all the attributes, the other a child a tag with only the href attr
					continue;
				}

			} else {
				$tag = "div";
			}

			$html .= "<{$tag}{$href}{$id_tag}{$class_tag}{$style_tag}{$data_value}{$data}{$title}>{$icon}{$buttons}{$col_html}{$copy}</{$tag}>";
		}

		return $html;
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
		if($col['type'] == "checkbox" && array_key_exists("checked", $col) && !$col['checked']){
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

	public static function generateRows(?array $rows): ?string
	{
		if(!$rows){
			return NULL;
		}

		if(!key_exists("rows", $rows)){
			$rows = [
				"rows" => $rows
			];
		}

		if(!is_array($rows['rows'])){
			return NULL;
		}

		# Allow for the row barrier to be moved by the user
		$row_class[] = "col-split";

		# If the breakpoint key is set to false, the columns won't fold on small screens
		if($rows['breakpoint'] === false){
			$row_class[] = "row-cols-2";
		}

		foreach($rows['rows'] as $key => $val){
			if(is_array($val)){
				/**
				 * If the value is itself an array,
				 * which can be done if there is a
				 * chance there is more than one
				 * row with the same key.
				 */
				foreach($val as $key => $val){
					$left = [
						"class" => "small",
						"sm" => $rows['sm'],
						"html" => $key,
					];
					$grid[] = [
						"row_class" => $row_class,
						"html" => [$left, $val]
					];
				}
				continue;
			}
			$left = [
				"class" => "small",
				"sm" => $rows['sm'],
				"html" => $key,
			];
			$grid[] = [
				"row_class" => $row_class,
				"html" => [$left, $val]
			];
		}

		return Grid::generate($grid);
	}

	/**
	 * Generate a cell title.
	 *
	 * @param $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];

		extract($a);

		# Title
		$title = $title ?: $html;

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

	/**
	 * @param string|array|null $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateBody($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["body" => $a];
		extract($a);

		$body = $body ?: $html;

		$tag = $tag ?: "p";
		if(is_string($body) && $body != strip_tags($body)){
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
		$alt = str::getAttrTag("title", str::truncateIf(strip_tags($body), 200));

		return "<{$tag}{$id}{$class}{$style}{$alt}>{$body}{$button}</{$tag}>";
	}
}