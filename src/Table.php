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
	public static function generate($rows, ?array $options = [], ?bool $ignore_header = NULL, ?bool $rows_only = NULL): ?string
	{
		if(empty($rows)){
			return NULL;
		}

		$options['id'] = $options['id'] ?: str::id("table");

		extract($options);

		$id = str::getAttrTag("id", $id);
		$class_array = str::getAttrArray($class, $sortable !== false ? "table-sortable" : "");
		// You can make the whole table not sortable by adding "sortable => false" in the options array

		# Can the rows be ordered?
		if($order){
			if(!$rel_table){
				throw new \Exception("If a table is orderable, it must also have a corresponding <code>rel_table</code> table.");
			}
			foreach($rows as $key => $row){
				# Non-sortable rows
				if($row['sortable'] === false){
					$rows[$key] = $row;
					continue;
				}

				# Sortable rows
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

		# Header row (if it's not to be ignored)
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

		if($rows_only){
			//if we're only interested in the row data
			return $grid->getHTML();
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
	 * member. Requires the row being updated to have been created with
	 * a meta row-id field setup like this:
	 *
	 * <code>
	 * $row = [
	 *    "html" => $row,
	 *    "row_id" => $row_id,
	 * ];
	 * </code>
	 *
	 * @param string     $row_id
	 * @param array      $row
	 * @param array|null $audience
	 */
	public static function updateRow(string $row_id, array $row, ?array $audience = NULL): void
	{
		Output::getInstance()->replace("#{$row_id}", Table::generate([$row], Table::getAsyncOptions($row), true, true), $audience);
	}

	/**
	 * DEPRECIATED, use appendRow and prependRow instead.
	 *
	 * Add a row to a table.
	 *
	 * @param string     $table_id
	 * @param array      $row
	 * @param array|null $audience
	 *
	 * @throws \Exception
	 */
	public static function addRow(string $table_id, array $row, ?array $audience = NULL): void
	{
		Output::getInstance()->append("#{$table_id}", Table::generate([$row], Table::getAsyncOptions($row), true, true), $audience);
	}

	/**
	 * Appends a row to a table, at the very bottom.
	 *
	 * @param string     $table_id
	 * @param array      $row
	 * @param array|null $audience
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function appendRow(string $table_id, array $row, ?array $audience = NULL): void
	{
		Output::getInstance()->append("#{$table_id} > .table-container", Table::generate([$row], Table::getAsyncOptions($row), true, true), $audience);
	}

	/**
	 * Prepends a row to a table, below the header.
	 *
	 * @param string     $table_id
	 * @param array      $row
	 * @param array|null $audience
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function prependRow(string $table_id, array $row, ?array $audience = NULL): void
	{
		Output::getInstance()->after("#{$table_id} > .table-container > .table-header", Table::generate([$row], Table::getAsyncOptions($row), true, true), $audience);
	}

	/**
	 * Generate the options for a row insert/update.
	 *
	 * @param array $row
	 *
	 * @return bool[]|null
	 */
	public static function getAsyncOptions(array $row): ?array
	{
		# Handle order-able table updates
		if($row['id'] || $row['html']['id']){
			// If there is an "id" column, assume this is an order-able table that we're updating
			return [
				"order" => true,
				"rel_table" => true
				// As we don't know the rel_table, and we only need it to "be", set it to true
			];
		}

		return NULL;
	}

	/**
	 * Generates the (optional) header row.
	 * Special header keys can be added to the first row of a table to be used
	 * in a header.
	 *
	 * @param array|NULL $row
	 * @param array      $options
	 *
	 * @return array
	 */
	private static function generateHeaderRow(?array $row, array $options): ?array
	{
		if(!is_array($row)){
			return NULL;
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
				"data" => $data,
				"style" => $col['header_style'],
				"icon" => $col['header_icon'],
				"buttons" => $col['header_buttons'],
				"button" => $col['header_button']
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
	private static function getSortableRow($row): array
	{
		# Rows with meta rows
		if($row['html']){
			//if the row already has its own meta row
			$html = $row['html'];
			unset($row['html']);
			$meta = $row;
		}

		# Rows without meta rows
		else {
			$html = $row;
		}

		if(!key_exists("id", $html)){
			throw new \Exception("To prepare a sortable table, an <code>id</code> key must be included per row.
			This is different from a <code>row_id</code> key.");
		}

		$id = $html['id'];
		unset($html['id']);

		$meta['row_class'] = "draggable";
		$meta['row_data']['id'] = $id;

		# We need to unset the order number, cause we don't actually use it anywhere
		unset($html['order']);

		# Add the custom sortable "row" (as the first row)
		$html = array_merge(["<!--SORTABLE-->" => [
				"class" => $id ? "sortable-handlebars" : "",
				"sm" => 1,
				"header_style" => [
					"max-width" => "2.5rem",
				],
			],
		], $html);

		$meta['html'] = $html;

		return $meta;
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

		# Include the ID in the response
		$output_vars['id'] = $vars['id'];

		# The (default) assumption is that all vars are where clauses, except:
		foreach(["start", "length", "order_by_col", "order_by_dir"] as $key){
			if(!$vars[$key]){
				continue;
			}

			$$key = $vars[$key];
			//for reference

			# Report the metadata back for reference
			$output_vars[$key] = $vars[$key];

			unset($vars[$key]);
			// We remove them because all other vars are being fed as where cols
		}

		# The start value grows for every request
		$output_vars["start"] = $start + $length;

		foreach($vars as $key => $val){
			# If the value is a numerical array, assume an IN is required
			if(is_string($key) && str::isNumericArray($val)){
				$base_query['where'][] = [$key, "IN", $val];
			}
			else {
				if(is_int($key)){
					$base_query['where'][] = $val;
				}
				else {
					$base_query['where'][$key] = $val;
				}
			}
		}

		$count_query = $base_query;

		# If there is an offset (header row for example), remove it for the count, but keep the number
		if($count_query['offset']){
			$count_query_offset = $count_query['offset'];
			unset($count_query['offset']);
		}

		$count_query['distinct'] = true;
		$count_query['count'] = "{$rel_table}_id";

		# Get the total results
		if(!$start){
			// If this is the first batch (we only need to do this once)

			# Run the count query
			$total_results = $sql->select($count_query);

			$output_vars['query'] = $_SESSION['query'];

			# If rows are returned, ensure the total results take into account any offset
			if($total_results){
				$output_vars['total_results'] = $total_results - $count_query_offset;
			}
			else {
				$output_vars['total_results'] = 0;
			}

			if(!$total_results){
				//If no rows can be found
				$output_vars['start'] = 1;
				$output_vars['rows'] = "<i>No rows found</i>";
				$output->function("onDemandResponse", $output_vars);
				return true;
			}

			$ignore_header = is_bool($ignore_header) ? $ignore_header : false;
			//if the variable is already set, will not re-set or change it

			# If an offset is given, we need to adjust the start value
			$start = $base_query['offset'];
			unset($base_query['offset']);
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

		$output_vars['query_parameters'] = $rows_query;
		$output_vars['query'] = $_SESSION['query'];

		if(!$rows){
			//if no results are found
			return false;
		}

		$output_vars['row_count'] = count($rows);

		if(is_object($row_handler)){
			//if a custom row handler has been included
			foreach($rows as $id => $row){
				$rows[$id] = ($row_handler)($row);
				//run the row handler through each row
			}
		}

		$output_vars['rows'] = self::generate($rows, $a, $ignore_header, true);

		# Load the response
		$output->function("onDemandResponse", $output_vars);

		return true;
	}

	public static function manageJsonRequest(array $a, ?array $base_query, ?object $row_handler, ?array $laps = NULL): bool
	{
		extract($a);

		$sql = Factory::getInstance();

		# Include the ID in the response
		$output_vars['id'] = $vars['id'];
		// This is the grid ID

		Output::getInstance()->setVar("query",  $sql->select($base_query, true));

		# Run the query
		if(!$rows = $sql->select($base_query)){
			//if no results are found
			self::compressAndSetOutputVars($output_vars);
			return true;
		}

		if(is_object($row_handler)){
			//if a custom row handler has been included
			foreach($rows as $id => $row){
				$output_vars['rows'][$id] = ($row_handler)($row);
				//run the row handler through each row
			}
		}

		if(str::isDev() && $laps){
			Output::getInstance()->setVar("laps", $laps);
		}

		self::compressAndSetOutputVars($output_vars);

		return true;
	}

	public static function compressAndSetOutputVars(array $output_vars): void
	{
		$output_vars['seconds'] = round(str::stopTimer(), 2);

		# Compress the output vars
		$output_vars_json = json_encode($output_vars);
		$output_vars_compressed = gzencode($output_vars_json);
		$output_vars_base64 = base64_encode($output_vars_compressed);

		# Load the response
		Output::getInstance()->function("loadAgGrid", $output_vars_base64);
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
	 * Use this method to generate a string that can be used
	 * to display a message when a table is empty.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function emptyTableString(string $string): string
	{
		return <<<EOF
<p style="font-size: 85%;font-style: italic;font-weight: 200;margin-top: 0.5rem;">
	{$string}
</p>
EOF;

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
				"name" => "{$column}[]",
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