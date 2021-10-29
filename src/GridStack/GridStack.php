<?php

namespace App\UI\GridStack;

use App\Client\ClientHandler;

/**
 * Methods to help with the placement of elements built
 * with the help of the gridstack.js class.
 *
 */
class GridStack {
	/**
	 * Given a set of elements, builds a grid that can be
	 * fed directly into a Grid() class.
	 *
	 * The elements have been built (x, y, width, height)
	 * using the gridstack.js class.
	 *
	 * Almost perfect.
	 *
	 * <code>
	 * $grid = GridStack::buildGrid($workflow_form_fields);
	 * </code>
	 *
	 * @param array              $elements
	 *
	 * @param ClientHandler|null $client
	 *
	 * @return array
	 */
	public static function buildGrid(array $elements): array
	{
		$cells = [];

		while($elements) {
			# The first field in this row
			$element = array_shift($elements);

			if($element['y'] + $element['height'] > $y){
				$y = $element['y'] + $element['height'];
			}
			$row_fields[] = $element;

			while(reset($elements)['y'] < $y) {
				if(empty($elements)){
					break;
				}
				$element = array_shift($elements);
				if($element['y'] + $element['height'] > $y){
					$y = $element['y'] + $element['height'];
				}
				$row_fields[] = $element;
			}

			$cells[] = self::buildGridRows($row_fields);
		}

		return $cells;
	}

	private static function buildGridRows(array &$row_elements): array
	{
		$cols = [];

		$x = 0;

		while($row_elements) {
			# Reset the col fields array
			$col_elements = [];

			# The first field in this row
			$field = array_shift($row_elements);

			# The last x (for calculating potential gaps later)
			$last_x = $x;

			# col_x is the left most starting point of this column
			$col_x = $field['x'];

			# X is the max width of this column
			if($field['x'] + $field['width'] > $x){
				$x = $field['x'] + $field['width'];
			}

			# Feed the first field into the col_fields array
			$col_elements[] = $field;

			self::buildGridRowFields($row_elements, $col_elements, $x, $col_x, $last_x);

			# Account for gaps between columns
			if($gap = $col_x - $last_x){
				// If there is a gap between the left-most point of this column and the right-most point of the last column
				$cols[] = [
					"sm" => $gap,
					"type" => "html",
					"html" => "",
				];
			}

			# The max width is the difference between the very end and the starting point of this column
			$max_width = $x - $col_x ?: 12;
			//Edge case failsafe, if the max width is 0, set it to 12.

			# Get all the cells for this column
			$cells = self::buildGridColumnFields($col_elements, $max_width, $col_x);

			$cols[] = [
				"sm" => $max_width,
				"html" => $cells,
			];
		}

		return $cols;
	}

	private static function buildGridRowFields(array &$row_elements, array &$col_elements, int &$x, ?int &$col_x, int $last_x): void
	{
		# Go through all fields for this row
		while(reset($row_elements)['x'] < $x && reset($row_elements)['x'] >= $last_x) {
			//For each row that falls within this column but not so far back to fall into the previous column

			# If there are no further rows, skip
			if(empty($row_elements)){
				return;
			}

			# Grab the first field
			$field = array_shift($row_elements);

			# If this next field is further to the left than the last, update the left-most point
			if($field['x'] < $col_x){
				$col_x = $field['x'];
			}

			# If this next field is wider than the previous, expand the right-most point
			if($field['x'] + $field['width'] > $x){
				$x = $field['x'] + $field['width'];
			}

			# Feed this field into the col_fields array
			$col_elements[] = $field;
		}
	}

	/**
	 * Goes through all the column elements for this column, and returns the built cells.
	 *
	 * @param array    $col_fields
	 * @param int      $max_width
	 * @param int|null $col_x
	 *
	 * @return array
	 */
	private static function buildGridColumnFields(array $col_fields, int $max_width, ?int $col_x): array
	{
		# For each column field for this column
		foreach($col_fields as $f){

			# Adjust the width to take into account the parent width
			$f['width'] = round($f['width'] * (12 / $max_width));

			# If there is a gap, insert an empty cell to pad the space
			if($gap = $f['x'] - $col_x){
				# Make the width proportionate to the parent column
				$gap = round($gap * (12 / $max_width));

				# Account for the gap in the width
				$f['width'] += $gap;

				$cells[] = [[
					"sm" => $gap,
					"type" => "html",
					"html" => "",
				], [
					$f['html']
				]];
			}

			else {
				$cells[] = $f['html'];
			}
		}

		return $cells;
	}
}