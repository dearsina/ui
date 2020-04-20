<?php


namespace App\UI;

use App\Common\str;

class Grid {
	private $grid;
	private $unstackable;
	private $formatter;

	/**
	 * Format and return content in a HTML grid.
	 * <code>
	 * $grid = new Grid([
	 * 	"unstackable" => FALSE, //If set to TRUE will make the grid unstackable on small screens
	 * 	"formatter" => FALSE //If set to an anonymous function, will use that function to format the HTML per cell.
	 * ]);
	 * </code>
	 * @param null $a
	 */
	public function __construct ($a = NULL) {
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
	private function getRowHTML($rows){
		foreach($rows as $row){
			if(!is_array($row)) {
				$row_html = $this->getColHTML(["html" => $row]);
				$row = [];
			} else if(str::isNumericArray($row)){
				$row_html = $this->getColHTML($row);
			} else if (str::isNumericArray($row['html'])){
				$row_html = $this->getColHTML($row['html']);
			} else if($row['html']){
				$row_html = $this->getColHTML([$row['html']]);
			} else {
				$row_html = $this->getColHTML([$row]);
			}

			# ID
			$id_tag = str::getAttrTag("id", $row['id']);

			# Class
			$class_array = str::getAttrArrray($row['class'], "row", $row['only_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Style
			$style_tag = str::getAttrTag("style", $row['style']);

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
	private function getColHTML($cols){
		foreach($cols as $col){
			//for each item in the row
			if(!is_array($col)){
				$col_html = $col;
				$col  = [];
			} else if(str::isNumericArray($col)){
				//if it goes deeper (without other metadata)
				$col_html = $this->getRowHTML($col);
			} else if(str::isNumericArray($col['html'])) {
				//if it goes deeper (with metadata)
				$col_html = $this->getRowHTML($col['html']);
			} else if ($this->formatter){
				//If a custom formatter has been designated
				$col_html = ($this->formatter)($col);
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
			$id_tag = str::getAttrTag("id", $col['id']);

			# Class
			$class_array = str::getAttrArrray($col['class'], $col_width, $col['only_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Styles
			$style_tag = str::getAttrTag("style", $col['style']);

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
	public function getHTML($a = NULL){
		$grid = $a ?: $this->grid;

		if(!$grid){
			return false;
		}

		$html = $this->getRowHTML($grid);
		return $html;
	}
}