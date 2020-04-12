<?php


namespace App\UI;

use App\Common\str;

class Grid {
	private $grid;
	private $unstackable;

	/**
	 * Format and return content in a HTML grid.
	 *
	 * @param null $a
	 */
	public function __construct ($a = NULL) {
		$this->unstackable = $a['unstackable'];
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
	public function set($a){
		if(!$a){
			return false;
		}

		$this->grid[] = $a;
		return true;
	}

	/**
	 * Formats and returns the HTML of one row (with one or many cells).
	 *
	 * @param $rows
	 *
	 * @return string
	 */
	private function get_row_html($rows){
		foreach($rows as $row){
			if(!is_array($row)) {
				$row_html = $this->get_col_html(["html" => $row]);
				$row = [];
			} else if(str::is_numeric_array($row)){
				$row_html = $this->get_col_html($row);
			} else if (str::is_numeric_array($row['html'])){
				$row_html = $this->get_col_html($row['html']);
			} else {
				$row_html = $this->get_col_html([$row['html']]);
			}

			# ID
			$id_tag = str::get_attr_tag("id", $row['id']);

			# Class
			$class_array = str::get_attr_array($row['class'], "row", $row['only_class']);
			$class_tag = str::get_attr_tag("class", $class_array);

			# Style
			$style_tag = str::get_attr_tag("style", $row['style']);

			$html .= "<div{$id_tag}{$class_tag}{$style_tag}>{$row_html}</div>";
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
	private function get_col_html($cols){
		foreach($cols as $col){
			//for each item in the row
			if(!is_array($col)){
				$col_html = $col;
				$col  = [];
			} else if(str::is_numeric_array($col)){
				//if it goes deeper (without other metadata)
				$col_html = $this->get_row_html($col);
			} else if(str::is_numeric_array($col['html'])){
				//if it goes deeper (with metadata)
				$col_html = $this->get_row_html($col['html']);
			} else {
				$col_html = $col['html'];
			}

			# If they're not to be stacked, make it so (default is stackable)
			$col_width = $this->unstackable ? "col" : "col-sm";

			# Has a fixed column width been requested
			if($col['sm']){
				$col_width .= "-{$col['sm']}";
			}

			# ID
			$id_tag = str::get_attr_tag("id", $col['id']);

			# Class
			$class_array = str::get_attr_array($col['class'], $col_width, $col['only_class']);
			$class_tag = str::get_attr_tag("class", $class_array);

			# Styles
			$style_tag = str::get_attr_tag("style", $col['style']);

			$html .= "<div{$id_tag}{$class_tag}{$style_tag}>{$col_html}</div>";
		}

		return $html;
	}

	/**
	 * Returns formatted HTMl of the requested grid.
	 *
	 * @param null $a
	 *
	 * @return bool|string
	 */
	public function get_html($a = NULL){
		$grid = $a ?: $this->grid;

		if(!$grid){
			return false;
		}

		$html = $this->get_row_html($grid);
		return $html;
	}
}