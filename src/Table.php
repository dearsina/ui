<?php


namespace App\UI;


use App\Common\Output;
use App\Common\SQL\Factory;
use App\Common\str;

class Table {
	/**
	 * Given an output from a SQL request, formats as a table,
	 * with sortable column headers.
	 *
	 * @param            $rows Array from SQL results
	 * @param array|null $options ID, class, style, script
	 *
	 * @return bool|string
	 */
	public static function generate($rows, ?array $options = [], ?bool $ignore_header = NULL){
		if(empty($rows)){
			return false;
		}

		$options['id'] =  $options['id'] ?: str::id("table");

		extract($options);

		$id = str::getAttrTag("id",$id);
		$class_tag = str::getAttrArray($class, $sortable !== false ? "table-sortable" : "");
		// You can make the whole table not sortable by adding "sortable => false" in the options array
		$class = str::getAttrTag("class", $class_tag);
		$style = str::getAttrTag("style", $style);
		$script = str::getScriptTag($script);

		# Tables are unstackable by design
		$grid = array_merge(["unstackable" => true], $grid ?: []);

		$grid = new Grid($grid);

		# Header row (if it's not be ignored)
		if(!$ignore_header){
			$grid->set([
				"class" => "table-header",
				"html" => self::generateHeaderRow($rows, $options)
			]);
		}

		# Table rows
		foreach($rows as $row){
			$grid->set([
				"class" => "table-body",
				"html" => array_values($row)
			]);
		}

		return <<<EOF
<div{$id}{$class}{$style}>
	{$grid->getHTML()}
</div>{$script}
EOF;
	}

	/**
	 * Generates the (optional) header row.
	 *
	 * @param $rows
	 * @param $options
	 *
	 * @return mixed
	 */
	private static function generateHeaderRow($rows, $options){
		extract($options);

		foreach($rows[0] as $key => $row){

			$row = is_array($row) ? $row : ["html" => $row];

			# The default class for a header row
			$default= ["text-flat"];

			if($row['sortable'] !== false){
				//If the column is not explicitly set to not sortable
				$data['col'] =  $row['col_name'] ?: $key;
				//If a column name has been set, use it, otherwise, use the key
			} else {
				$data = [];
				$default[] = "sorted-ignore";
				// You can make individual columns not sortable by adding the "sortable => false" to a cell
			}

			$header_class = str::getAttrArray($row['header_class'], $default);

			# The header row inherits the class, style and the col length (sm) of the table rows
			$header_cols[] = [
				"html" => $key,
				"sm" => $row['sm'],
				"class" => $header_class,
				"style" => $row['header_style'],
				"data" => $data
			];
		}
		return $header_cols;
	}

	public static function onDemand(array $a){
		extract($a);

		if(!is_array($hash)){
			throw new \Exception("An onDemand setup must include a hash to request rows from.");
		}

		$a['id'] = $id ?: str::id("table");
		$id = str::getAttrTag("id", $a['id'] );
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
			"size" => "small"
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
	private static function getOnDemandAttr($a){
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

		return str::getDataAttr($a);
	}

	/**
	 * What to place on the method that is requested by a onDemand
	 * "load-more-button" button.
	 *
	 * <code>
	 * $base_query = [
	 * 	"table" => "error_log",
	 * 	"order_by" => [
	 * 		"created" => "desc"
	 * 	]
	 * ];
	 *
	 * $row_handler = function(array $error){
	 * 	$row["Date"] = [
	 * 		"html" => str::ago($error['created']),
	 * 		"sm" => 2
	 * 	];
	 * 	$row["Type"] = [
	 * 		"accordion" => [
	 * 			"header" => $error['title'],
	 * 			"body" => str::pre($error['message'])
	 * 		],
	 * 		"sm" => 4
	 * 	];
	 * 	return $row;
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
	public static function managePageRequest(array $a, ?array $base_query, ?object $row_handler){
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
			$output->set_var($key, $vars[$key]);

			unset($vars[$key]);
			// We remove them because all other vars are being fed as where cols
		}

		# The start value grows for every request
		$output->set_var('start', $start + $length);

		$base_query['where'] = array_merge($base_query['where'] ?: [], $vars ?: []);

		$count_query = $base_query;
		$count_query['count'] = true;

		# Get the total results
		if(!$start){
			// If this is the first batch (we only need to do this once)

			if(!$total_results = $sql->select($count_query)){
				//If no rows can be found
				$output->set_var('total_results',0);
				$output->set_var('start',1);
				$output->set_var("rows", "<i class=\"text-silent\">No rows found</i>");
				return true;
			}

			$output->set_var('total_results',$total_results);
			$ignore_header = is_bool($ignore_header) ? $ignore_header : false;
			//if the variable is already set, will not re-set or change it
		} else {
			//If this is not the first batch, no need to post the header again
			$ignore_header = is_bool($ignore_header) ? $ignore_header : true;
			//if the variable is already set, will not re-set or change it
		}

		$rows_query = $base_query;
		$rows_query['start'] = $start;
		$rows_query['length'] = $length;

		if($order_by_col && $order_by_dir){
			//only if a col and dir have been given, otherwise use default
			$rows_query['order_by'] = [$order_by_col => $order_by_dir];
		}

		$rows = $sql->select($rows_query);

		if(!$rows){
			//if no results are found
			return false;
		}

		if(is_object($row_handler)){
			//if a custom row handler has been included
			foreach($rows as $id => $row){
				$rows[$id] = ($row_handler)($row);
				//run the row handler thru each row
			}
		}

		# Due to the data being loaded piecemeal, it is not sortable
//		$a['sortable'] = false;

		$output->set_var("rows", self::generate($rows, $a, $ignore_header));
	}
}