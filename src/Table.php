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
	 * Number of rows sent in the first AG Grid batch.
	 *
	 * The first batch is intentionally small so the browser can render useful data
	 * quickly while the remaining rows continue loading.
	 */
	public const AG_GRID_INITIAL_BATCH_SIZE = 100;

	/**
	 * Default row count for generic AG Grid batches after the first batch.
	 *
	 * Population-specific streaming may choose a different later-batch size when it
	 * can do so safely, but this value remains the default for the shared table loader.
	 */
	public const AG_GRID_BATCH_SIZE = 500;

	/**
	 * Upper bound for caller-supplied AG Grid batch sizes.
	 *
	 * Request variables are allowed to tune batch sizes, but this cap prevents a caller
	 * from accidentally recreating the original very-large-payload behaviour.
	 */
	public const AG_GRID_MAX_BATCH_SIZE = 5000;

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
			$data["action"] = $action;
			$data['vars'] = $vars ?: [];

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
			$row = \reset($rows);
			$row = $row['html'] ?: $row;
			if(!is_array($row)){
				$row = $rows;
			}
			$grid->set([
				"row_class" => str::getAttrArray($row['header_class'], "table-header", $row['only_header_class']),
				"row_style" => $row['header_style'],
				"html" => self::generateHeaderRow($row, $options),
			]);
		}

		# Table rows
		foreach(array_filter($rows) as $row){
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
		Output::getInstance()->append("#{$table_id} > .table-sortable", Table::generate([$row], Table::getAsyncOptions($row), true, true), $audience);
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
				"rel_table" => true,
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
				"button" => $col['header_button'],
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

		if(!array_key_exists("id", $html)){
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
			# Ignore the hash key root key, as it's used to generate the other data-attrs
			if($key == 'hash'){
				continue;
			}

			# For all other keys, load them as vars
			$a['hash']['vars'][$key] = $val;

			unset($a[$key]);
		}

		# Set the start to be zero
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
		if($count_query['offset'] || $offset){
			$count_query_offset = $count_query['offset'] ?: $offset;
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
		self::setOrderBy($rows_query, $a);
		self::setOffsetCte($rows_query, $a);

		$rows_query['start'] = $start;
		$rows_query['length'] = $length;

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

	public static function setOffsetCte(array &$rows_query, array $a): void
	{
		extract($a);

		if(!$offset){
			//only if an offset has been given, otherwise use the default
			return;
		}

		$rows_query['cte'] = [
			"offset" => [
				"db" => $rows_query['db'],
				"table" => $rows_query['table'],
				"offset" => $offset,
				"include_removed" => $rows_query['include_removed'],
				// Carried over from the base query
			],
		];

		$rows_query['db'] = NULL;
		$rows_query['table'] = "offset";
	}

	private static function setOrderBy(array &$rows_query, array $a): void
	{
		extract($a);

		if(!$order_by_col){
			//only if a col has been given, otherwise use the default
			return;
		}

		# If no direction is given, assume ASC
		if(!$order_by_dir){
			$order_by_dir = "ASC";
		}

		# Handle complex order bys (joining tables)
		$complex_order_by = explode(".", $order_by_col);

		if(count($complex_order_by) == 2){
			$complex_order_by[] = $order_by_dir;
			$rows_query['order_by'] = [$complex_order_by];
			return;
		}

		# Set the order by
		$rows_query['order_by'] = [
			$order_by_col => $order_by_dir
		];
	}

	/**
	 * Handles an AG Grid JSON request by reading, formatting, and emitting rows in batches.
	 *
	 * This method is the generic AG Grid table loader. It keeps the caller's base SQL query
	 * intact, applies LIMIT windows to that query, formats rows through the optional row
	 * handler, and sends each batch to the browser via loadAgGrid(). The first batch is
	 * intentionally small so the UI can render early; subsequent batches use the normal
	 * AG Grid batch size.
	 *
	 * When worker recipient information is available, batches are pushed immediately to
	 * the active browser connection rather than being buffered until worker shutdown. The
	 * batch metadata includes request_id, initial/append flags, completion state, loaded
	 * row count, and, when available, a stable total count.
	 *
	 * This loader still pages the final result query. It does not move batching into any
	 * upstream source-table or CTE construction; callers that need deeper streaming should
	 * implement that before delegating here or bypass this method with a domain-specific
	 * streaming method.
	 *
	 * @param array       $a Request payload containing vars.id and optional AG Grid batch vars.
	 * @param array|null  $base_query Shared SQL select-array used as the source for every batch.
	 * @param object|null $row_handler Optional callable object used to convert raw SQL rows to AG Grid rows.
	 * @param array|null  $laps Optional timing/debug lines attached to the final batch in dev mode.
	 * @param object|null $continue_handler Optional callable checked before expensive work and each send.
	 *
	 * @return bool TRUE after all available rows have been emitted.
	 */
	public static function manageJsonRequest(array $a, ?array $base_query, ?object $row_handler, ?array $laps = NULL, ?object $continue_handler = NULL): bool
	{
		extract($a);

		if(is_object($continue_handler) && !($continue_handler)()){
			return false;
		}

		$sql = Factory::getInstance();
		$vars = $a['vars'] ?? [];

		# Include the ID in the response
		$output_vars['id'] = $vars['id'];
		// This is the grid ID

		Output::getInstance()->setVar("query", $sql->select($base_query, true));

		# Workers normally buffer output until the request completes.
		# Supplying recipients makes each batch speak to the browser immediately.
		$recipients = self::getCurrentOutputRecipients();
		$request_id = $vars['ag_grid_request_id'] ?? NULL;
		$initial_batch_size = self::getAgGridBatchSize($vars['ag_grid_initial_batch_size'] ?? NULL, self::AG_GRID_INITIAL_BATCH_SIZE);
		$batch_size = self::getAgGridBatchSize($vars['ag_grid_batch_size'] ?? NULL, self::AG_GRID_BATCH_SIZE);
		# Count the same result set once so the UI can show a stable denominator.
		# If counting fails for a non-client table, the loader still works without a total.
		$total = self::getAgGridTotal($sql, $base_query);
		$offset = 0;
		$loaded_count = 0;
		$batch_number = 0;
		$current_batch_size = $initial_batch_size;

		do {
			if(is_object($continue_handler) && !($continue_handler)()){
				return false;
			}

			# Clone the caller's query and page it. This keeps existing filtering/order clauses intact
			# while avoiding one huge selected/transformed/compressed payload.
			$rows_query = $base_query;
			$rows_query['limit'] = [$offset, $current_batch_size];
			unset($rows_query['start'], $rows_query['length'], $rows_query['offset']);

			$rows = $sql->select($rows_query);
			$is_initial_batch = $batch_number == 0;

			if(!$rows){
				$output_vars['rows'] = $is_initial_batch ? NULL : [];
				$batch = [
					"request_id" => $request_id,
					"initial" => $is_initial_batch,
					"append" => !$is_initial_batch,
					"complete" => true,
					"loaded" => $loaded_count,
				];

				# An empty final batch is still useful: it tells the browser the stream is complete.
				# When a count was unavailable, loaded_count is the best final denominator.
				if($total !== NULL){
					$batch['total'] = $total;
				}
				else {
					$batch['total'] = $loaded_count;
				}

				$output_vars['batch'] = $batch;

				if(str::isDev() && $laps){
					$output_vars['laps'] = $laps;
				}

				if(is_object($continue_handler) && !($continue_handler)()){
					return false;
				}

				self::compressAndSetOutputVars($output_vars, $recipients);
				return true;
			}

			# Existing callers may return either one associative row or a numeric list of rows.
			# Normalise before running the row handler so the batch metadata uses consistent counts.
			if(!str::isNumericArray($rows)){
				$rows = [$rows];
			}

			$batch_rows = [];
			foreach($rows as $id => $row){
				$batch_rows[$id] = is_object($row_handler) ? ($row_handler)($row) : $row;
			}

			$loaded_count += count($batch_rows);
			# If the total is known, avoid sending an extra empty completion batch when the result count
			# is exactly divisible by the batch size.
			$is_complete = count($rows) < $current_batch_size || ($total !== NULL && $loaded_count >= $total);

			$output_vars['rows'] = $batch_rows;
			$batch = [
				"request_id" => $request_id,
				"initial" => $is_initial_batch,
				"append" => !$is_initial_batch,
				"complete" => $is_complete,
				"loaded" => $loaded_count,
			];

			if($total !== NULL){
				$batch['total'] = $total;
			}

			$output_vars['batch'] = $batch;

			if($is_complete && str::isDev() && $laps){
				$output_vars['laps'] = $laps;
			}

			if(is_object($continue_handler) && !($continue_handler)()){
				return false;
			}

			self::compressAndSetOutputVars($output_vars, $recipients);

			$offset += $current_batch_size;
			$current_batch_size = $batch_size;
			$batch_number++;
		} while(!$is_complete);

		return true;
	}

	/**
	 * Resolves a safe AG Grid batch size from request input.
	 *
	 * Request variables can override default batch sizes, but they are not trusted blindly.
	 * Non-positive values fall back to the caller-provided default and large values are
	 * capped at AG_GRID_MAX_BATCH_SIZE.
	 *
	 * @param mixed $requested_size Raw requested batch size, usually from request vars.
	 * @param int   $default_size Batch size to use when no valid override is supplied.
	 *
	 * @return int Sanitised batch size.
	 */
	public static function getAgGridBatchSize($requested_size, int $default_size): int
	{
		$batch_size = (int)($requested_size ?: $default_size);

		# Fall back on invalid/zero values rather than trusting request vars.
		if($batch_size < 1){
			return $default_size;
		}

		# Cap the batch size so a caller cannot accidentally reintroduce very large browser payloads.
		return min($batch_size, self::AG_GRID_MAX_BATCH_SIZE);
	}

	/**
	 * Returns the best available recipient selector for immediate worker output.
	 *
	 * CLI/worker requests normally accumulate Output until the request exits. Batching only
	 * improves perceived load time if each batch can be sent while the worker is still
	 * running, so this method reconstructs the recipient array from globals carried into
	 * the worker by Process::request().
	 *
	 * The current connection is preferred to avoid sending batches to another open tab for
	 * the same user. Session and user fallbacks preserve older async-output behaviour when
	 * no connection id is present.
	 *
	 * @return array|null Recipient selector accepted by Output/PA, or NULL to buffer normally.
	 */
	public static function getCurrentOutputRecipients(): ?array
	{
		global $connection_id;
		global $session_id;
		global $user_id;

		# Prefer the exact connection when available. This prevents overlapping tabs/sessions for
		# the same user from receiving batches intended for the visible grid request.
		if($connection_id){
			$recipients['connection_id'] = $connection_id;
		}

		# Session/user fallbacks preserve the older worker-output behaviour when no connection id exists.
		if($session_id){
			$recipients['session_id'] = $session_id;
		}
		else if($user_id){
			$recipients['user_id'] = $user_id;
		}

		return $recipients ?? NULL;
	}

	/**
	 * Attempts to count the full AG Grid result set represented by a base query.
	 *
	 * The count is used only for progress display. If a count cannot be computed safely,
	 * batching still proceeds and the browser falls back to a "loaded so far" narrative.
	 *
	 * The current generic AG Grid usages are client-row based, so this method counts
	 * distinct client_id after removing row-page-only clauses such as columns, order_by,
	 * and limit. Future non-client_id queries may fail this count; the exception is
	 * intentionally swallowed to keep row loading functional.
	 *
	 * @param mixed      $sql SQL factory/connection object exposing select().
	 * @param array|null $base_query Base SQL select-array for the grid result.
	 *
	 * @return int|null Stable row total when available, otherwise NULL.
	 */
	private static function getAgGridTotal($sql, ?array $base_query): ?int
	{
		if(!$base_query){
			return NULL;
		}

		# Build a count query from the same base query, removing clauses that only make sense for row pages.
		# Current AG Grid population-style queries are client based, so distinct client_id is the stable row count.
		$count_query = $base_query;
		unset($count_query['columns'], $count_query['order_by'], $count_query['limit'], $count_query['start'], $count_query['length'], $count_query['offset']);
		$count_query['distinct'] = true;
		$count_query['count'] = 'client_id';

		try {
			return (int)$sql->select($count_query);
		}
		catch(\Throwable $e) {
			# Keep batching functional for any future non-client_id AG Grid query.
			# The UI will fall back to a loaded-so-far narrative when no total is supplied.
			return NULL;
		}
	}

	/**
	 * Compresses an AG Grid payload and sends it to the browser loader.
	 *
	 * The browser-side loadAgGrid() function expects a base64-encoded gzipped JSON payload.
	 * This method also adds the elapsed seconds field used by the browser console timing
	 * output. When recipients are supplied, Output sends the function call immediately to
	 * those recipients; otherwise it is stored in the normal request output buffer.
	 *
	 * @param array      $output_vars Payload containing id, rows, and optional batch/laps metadata.
	 * @param array|null $recipients Optional recipient selector for immediate async output.
	 *
	 * @return void
	 */
	public static function compressAndSetOutputVars(array $output_vars, ?array $recipients = NULL): void
	{
		$output_vars['seconds'] = round(str::stopTimer(), 2);

		# Compress the output vars
		$output_vars_json = json_encode($output_vars);
		$output_vars_compressed = gzencode($output_vars_json);
		$output_vars_base64 = base64_encode($output_vars_compressed);

		# Load the response
		Output::getInstance()->function("loadAgGrid", $output_vars_base64, $recipients);
	}


	/**
	 * Given a rel_table, returns a row with a "New..." link,
	 * for use when $rows is empty and you don't want
	 * the table to be bare.
	 *
	 * @param string     $rel_table
	 * @param array|null $overrides
	 *
	 * @return array[]
	 * @throws \Exception
	 */
	public static function emptyTablePlaceholder(string $rel_table, ?array $overrides = NULL): array
	{
		$hash = $overrides['hash'] ?: [
			"rel_table" => $overrides['rel_table'] ?: $rel_table,
			"action" => $overrides['action'] ?: "new",
			"vars" => $overrides['vars'],
		];

		return [
			$overrides['title'] ?: str::title($rel_table) => [
				"icon" => $overrides['icon'] ?: Icon::get("new"),
				"html" => $overrides['title'] ? "New {$overrides['title']}..." : str::title("New {$rel_table}..."),
				"hash" => $hash,
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
                // TODO: Review logic here
//				"checked" => in_array($id, $vars[$column] ?: []),
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
