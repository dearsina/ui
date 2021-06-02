<?php


namespace App\UI;


use App\Common\Output;
use App\Common\SQL\Factory;
use App\Common\str;
use App\UI\Form\Field;
use App\UI\Form\Form;

/**
 * Class Table
 * @package App\UI
 */
class Table {
	/**
	 * Given an output from a SQL request, formats as a table,
	 * with sortable column headers.
	 *
	 * Tables with an order column:
	 * <code>
	 * Table::generate($rows, [
	 *    "order" => true,
	 *    "sortable" => false, // Disables sorting of any column
	 *    "rel_table" => $rel_table,
	 *    //The limiting key-val are only useful for tables where the rows can be reordered
	 *    "limiting_key" => "subscription_id",
	 *    "limiting_val" => $workflow['subscription_id'],
	 * ]);
	 * </code>
	 *
	 * @param array      $rows    Array from SQL results
	 * @param array|null $options ID, class, style, script
	 *
	 * @param bool|null  $ignore_header
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public static function generate($rows, ?array $options = [], ?bool $ignore_header = NULL)
	{
		if(empty($rows)){
			return false;
		}

		$options['id'] = $options['id'] ?: str::id("table");

		extract($options);

		$id = str::getAttrTag("id", $id);
		$class_array = str::getAttrArray($class, $sortable !== false ? "table-sortable" : "");
		// You can make the whole table not sortable by adding "sortable => false" in the options array

		# Can the rows be ordered?
		if($order){
			foreach($rows as $key => $row){
				$rows[$key] = self::getSortableRow($row);
			}

			# Identifies which table is being reordered
			$data["rel_table"] = $rel_table;

			# Use the limiting key/val pair to avoid reordering the entire table
			$data["limiting_key"] = $limiting_key;
			$data["limiting_val"] = $limiting_val;

			$class_array[] = "table-orderable";
		}

		# Script
		$script = str::getScriptTag($script);

		# Class
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Tables are unstackable by design
		$grid = array_merge(["unstackable" => true], $grid ?: []);

		$grid = new Grid($grid);

		# Header row (if it's not be ignored)
		if(!$ignore_header){
			$row = $rows[0]['html'] ?: $rows[0];
			$grid->set([
				"row_class" => str::getAttrArray($row['header_class'], "table-header", $row['only_header_class']),
				"row_style" => $row['header_style'],
				"html" => self::generateHeaderRow($row, $options),
			]);
		}

		# Table rows
		foreach($rows as $key => $row){
			if(!is_string($row['html'])){
				$row['html'] = array_values($row['html'] ?: $row);
			}
			$row['row_class'] = str::getAttrArray($row['row_class'], "table-body", $row['only_row_class']);
			$grid->set($row);
		}

		# Data
		$data = str::getDataAttr($data);

		return <<<EOF
<div{$id}{$class}{$style}{$data}>
	{$grid->getHTML()}
</div>{$script}
EOF;
	}

	/**
	 * Update a single row in a table.
	 *
	 * Given a row ID, an array of row data, and an (optional) audience,
	 * will update (replace) a single row in a table. If an audience is
	 * included, will update in real time in the window of every audience
	 * member. Requires the row to have a meta row-id field setup like this:
	 *
	 * <code>
	 * $row = [
	 * 	"html" => $row,
	 * 	"row_id" => $row_id,
	 * ];
	 * </code>
	 *
	 * @param string     $row_id
	 * @param array      $row
	 * @param array|null $audience
	 */
	public static function updateRow(string $row_id, array $row, ?array $audience = NULL): void
	{
		# Rows are basically just unstackable grids
		$grid = new Grid(["unstackable" => true]);

		$row['html'] = array_values($row['html'] ?: $row);
		//In case the row has meta fields

		# Set the class
		$row['row_class'] = str::getAttrArray($row['row_class'], "table-body", $row['only_row_class']);

		# Set the single row grid
		$grid->set($row);

		Output::getInstance()->replace("#{$row_id}", $grid->getHTML(), $audience);
	}

	/**
	 * Generates the (optional) header row.
	 *
	 * @param array|NULL $row
	 * @param array      $options
	 *
	 * @return array
	 */
	private static function generateHeaderRow(?array $row, array $options)
	{
		if(!is_array($row)){
			return [];
		}

		extract($options);

		foreach($row as $key => $col){
			$col = is_array($col) ? $col : ["html" => $col];

			# The default class for a header row
			$default = ["text-flat"];

			if($col['sortable'] !== false){
				//If the column is not explicitly set to not sortable
				$data['col'] = $col['col_name'] ?: $key;
				//If a column alias has been set, use it, otherwise, use the key
			}
			else {
				$data = [];
				$default[] = "sorted-ignore";
				// You can make individual columns not sortable by adding the "sortable => false" to a cell
			}

			$header_class = str::getAttrArray($col['header_class'], $default);

			# The header row inherits the class, style and the col length (sm) of the table rows
			$header_cols[] = [
				"html" => $key,
				"sm" => $col['sm'],
				"class" => $header_class,
				"style" => $col['header_style'],
				"data" => $data,
			];
		}
		return $header_cols;
	}

	/**
	 * @param $row
	 *
	 * @return array
	 * @throws \Exception
	 */
	private static function getSortableRow($row)
	{
		# We need to unset the order number, cause we don't actually use it anywhere
		unset($row['order']);


		if(!key_exists("id", $row)){
			throw new \Exception("To prepare a sortable table, an id key must be included per row.");
		}

		$id = $row['id'];
		unset($row['id']);

		$order = [
			"<!--SORTABLE-->" => [
				"class" => $id ? "sortable-handlebars" : "",
				"sm" => 1,
				"header_style" => [
					"max-width" => "3.5rem",
				],
			],
		];

		$row = [
			"row_class" => "draggable",
			"row_data" => [
				"id" => $id,
			],
			"html" => array_merge($order, $row),
		];

		return $row;
	}

	/**
	 * @param array $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function onDemand(array $a)
	{
		extract($a);

		if(!is_array($hash)){
			throw new \Exception("An onDemand setup must include a hash to request rows from.");
		}

		$a['id'] = $id ?: str::id("table");
		$id = str::getAttrTag("id", $a['id']);
		$class_array = str::getAttrArray($class, "table-ondemand", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style_class = str::getAttrArray($style, [], $only_style);
		$style = str::getAttrTag("style", $style_class);
		$data_attr = self::getOnDemandAttr($a);

		$button = Button::generate([
			"basic" => true,
			"colour" => "black",
			"icon" => "angle-double-down",
			"title" => "Load more...",
			"class" => "load-more-button",
			"size" => "small",
		]);

		return <<<EOF
<div{$id}{$class}{$style}{$data_attr}>
<div class="table-container"></div>
{$button}
{$script}
</div>
EOF;
	}

	/**
	 * Gets the data-attrs for the onDemand element
	 *
	 * @param $a
	 *
	 * @return string
	 */
	private static function getOnDemandAttr($a)
	{
		foreach($a as $key => $val){
			if($key == 'hash'){
				continue;
			}
			$a['hash']['vars'][$key] = $val;
			unset($a[$key]);
		}
		$a['hash']['vars']['start'] = 0;

		/**
		 * If you want to fire appear event for elements
		 * which are close to viewport but are not visible
		 * yet, you may add data attributes appear-top-offset
		 * and appear-left-offset to DOM nodes.
		 */
		$a['appear-top-offset'] = 300;

		return str::getDataAttr($a, true);
		//Keep empty because values could be "0"
	}

	/**
	 * What to place on the method that is requested by a onDemand
	 * "load-more-button" button.
	 *
	 * <code>
	 * $base_query = [
	 *    "table" => "error_log",
	 *    "order_by" => [
	 *        "created" => "desc"
	 *    ]
	 * ];
	 *
	 * $row_handler = function(array $error){
	 *    $row["Date"] = [
	 *        "html" => str::ago($error['created']),
	 *        "sm" => 2
	 *    ];
	 *    $row["Type"] = [
	 *        "accordion" => [
	 *            "header" => $error['title'],
	 *            "body" => str::pre($error['message'])
	 *        ],
	 *        "sm" => 4
	 *    ];
	 *    return $row;
	 * };
	 *
	 * Table::managePageRequest($a, $base_query, $row_handler);
	 * </code>
	 *
	 * @param array       $a
	 * @param array|null  $base_query
	 * @param object|null $row_handler
	 *
	 * @return bool
	 */
	public static function managePageRequest(array $a, ?array $base_query, ?object $row_handler): bool
	{
		extract($a);

		$output = Output::getInstance();
		$sql = Factory::getInstance();

		# Default base query is just querying the rel_table
		if(!is_array($base_query)){
			$base_query['table'] = $rel_table;
		}

		# UrlDEcode the variables
		$vars = str::urldecode($vars);

		# The (default) assumption is that all vars are where clauses, except:
		foreach(["start", "length", "order_by_col", "order_by_dir"] as $key){
			if(!$vars[$key]){
				continue;
			}

			$$key = $vars[$key];
			//for reference

			# Report the metadata back for reference
			$output->setVar($key, $vars[$key]);

			unset($vars[$key]);
			// We remove them because all other vars are being fed as where cols
		}

		# The start value grows for every request
		$output->setVar('start', $start + $length);

		foreach($vars as $key => $val){
			# If the value is a numerical array, assume an IN is required
			if(is_string($key) && str::isNumericArray($val)){
				$base_query['where'][] = [$key, "IN", $val];
			}
			else {
				$base_query['where'][$key] = $val;
			}
		}

		$count_query = $base_query;
		$count_query['distinct'] = true;
		$count_query['count'] = "{$rel_table}_id";

		# Get the total results
		if(!$start){
			// If this is the first batch (we only need to do this once)

			# Run the count query
			$total_results = $sql->select($count_query);

			$output->setVar('query', $_SESSION['query']);
			$output->setVar('total_results', $total_results ?: 0);

			if(!$total_results){//echo $_SESSION['query'];exit;
				//If no rows can be found
				$output->setVar('start', 1);
				$output->setVar("rows", "<i class=\"text-silent\">No rows found</i>");
				return true;
			}


			$ignore_header = is_bool($ignore_header) ? $ignore_header : false;
			//if the variable is already set, will not re-set or change it
		}
		else {
			//If this is not the first batch, no need to post the header again
			$ignore_header = is_bool($ignore_header) ? $ignore_header : true;
			//if the variable is already set, will not re-set or change it
		}

		$rows_query = $base_query;
		$rows_query['start'] = $start;
		$rows_query['length'] = $length;

		if($order_by_col && $order_by_dir){
			//only if a col and dir have been given, otherwise use default
			$complex_order_by = explode(".", $order_by_col);

			if(count($complex_order_by) == 2){
				$complex_order_by[] = $order_by_dir;
				$rows_query['order_by'] = [$complex_order_by];
			}
			else {
				$rows_query['order_by'] = [$order_by_col => $order_by_dir];
			}
		}

		$rows = $sql->select($rows_query);

		$output->setVar('query_parameters', $rows_query);
		$output->setVar('query', $_SESSION['query']);

		if(!$rows){
			//if no results are found
			return false;
		}

		# Hack to fix the order (not sure why SQL isn't doing this)
		if($rows_query['order_by']){
			str::multidimensionalOrderBy($rows, $rows_query['order_by']);
		}

		if(is_object($row_handler)){
			//if a custom row handler has been included
			foreach($rows as $id => $row){
				$rows[$id] = ($row_handler)($row);
				//run the row handler thru each row
			}
		}
		//		echo json_encode($rows);exit;

		# Due to the data being loaded piecemeal, it is not sortable
		//		$a['sortable'] = false;

		$output->setVar("rows", self::generate($rows, $a, $ignore_header));

		return true;
	}


	/**
	 * Given a rel_table, returns a row with a "New..." link,
	 * for use when $rows is empty and you don't want
	 * the table to be bare.
	 *
	 * @param string     $rel_table
	 * @param array|null $vars
	 *
	 * @return array[]
	 */
	public static function emptyTablePlaceholder(string $rel_table, ?array $vars = NULL): array
	{
		return [
			str::title($rel_table) => [
				"icon" => Icon::get("new"),
				"html" => str::title("New {$rel_table}..."),
				"hash" => [
					"rel_table" => $rel_table,
					"action" => "new",
					"vars" => $vars,
				],
			],
		];
	}

	/**
	 * Given a list of filters, will turn them into a checkbox form,
	 * connected to the on-demand table. When a checkbox is clicked,
	 * the table is refreshed with the filter.
	 *
	 * Expecting the filters array to be as follows:
	 * <code>
	 * "column" => [
	 *    "title" => "Column title",
	 *    "options" => [
	 *        "option-key" => "option-value"
	 *    ]
	 * ]
	 * </code>
	 *
	 * @param array  $filters
	 * @param string $ondemand_table_id
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function getFilterCard(array $filters, string $ondemand_table_id): string
	{
		$fields[] = [
			"name" => "q",
			"label" => "Search",
			"placeholder" => "Enter search string here",
		];

		foreach($filters as $column => $data){
			$options = [];
			foreach($data['options'] as $key => $option){
				$option .= "<span class=\"filter-icon only\" title=\"Select only this\">" . Icon::generate("indent") . "</span>";
				$option .= "<span class=\"filter-icon except\" title=\"Select all except this\">" . Icon::generate("outdent") . "</span>";

				$options[$key] = [
					"label" => [
						"title" => false,
						"desc" => $option,
					],
				];
			}

			$field = [
				"type" => "checkbox",
				"class" => "column-toggle",
				"label" => [
					"title" => $data['title'],
				],
				"parent_style" => [
					"margin-bottom" => "-3rem",
				],
			];
			$label = Field::getHTML($field);


			$fields[] = [
				"parent_style" => [
					"height" => "25px",
				],
				"type" => "checkbox",
				"label" => $label,
				"options" => $options,
				"name" => $column,
				"checked" => in_array($id, $vars[$column] ?: []),
			];
		}

		$form = new Form([
			"class" => "form-ondemand-filter",
			"data" => [
				"table_id" => $ondemand_table_id,
			],
			"style" => [
				"margin-bottom" => "-1rem",
			],
			"fields" => [$fields],
		]);

		$card = new \App\UI\Card\Card([
			"header" => [
				"icon" => Icon::get("filter"),
				"title" => "Filters",
				"button" => [
					"title" => "Clear",
					"class" => "clear-ondemand-filters",
					"ladda" => false,
					"basic" => true,
					"size" => "s",
				],
			],
			"body" => $form->getHTML(),
		]);

		return $card->getHTML();
	}
}