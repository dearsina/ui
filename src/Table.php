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

		extract($options);

		$id = str::getAttrTag("id", $id ?: str::id("table"));
		$class_tag = str::getAttrArray($class, $sortable !== false ? "table-sortable" : "");
		$class = str::getAttrTag("class", $class_tag);
		$style = str::getAttrTag("style", $style);
		$script = str::getScriptTag($script);

		# Tables are unstackable by design
		$grid = array_merge(["unstackable" => true], $grid ?: []);

		$grid = new Grid($grid);
		if(!$ignore_header){
			foreach($rows[0] as $key => $row){
				# The header row inherits the class, style and the col length (sm) of the table rows
				$header_cols[] = [
					"html" => $key,
					"sm" => $row['sm'],
					"class" => $row['class'],
					"style" => $row['style'],
				];
			}
			$grid->set([
				"class" => "table-header",
				"html" => $header_cols
			]);
		}
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

	public static function onDemand(array $a){
		extract($a);

		if(!is_array($hash)){
			throw new \Exception("An onDemand setup must include a hash to request rows from.");
		}

		$a['id'] = $id ?: str::id("table");
		$id = str::getAttrTag("id", $a['id'] );
		$class = str::getAttrTag("class", $class);
		$style_class = str::getAttrArray($style, [], $only_style);
		$style = str::getAttrTag("style", $style_class);
		$script = str::getScriptTag(self::getOnDemandScript($a));

		$button = Button::generate([
			"basic" => true,
			"colour" => "black",
			"icon" => "angle-double-down",
			"title" => "Load more...",
			"class" => "load-more-button",
			"size" => "small"
		]);

		return <<<EOF
<div{$id}{$class}{$style}>
<div class="table-container"></div>
{$button}
{$script}
</div>
EOF;
	}

	/**
	 * The onDemand script.
	 *
	 * @param $a
	 *
	 * @return string
	 */
	private static function getOnDemandScript($a){
		foreach($a as $key => $val){
			if($key == 'hash'){
				continue;
			}
			$a['hash']['vars'][$key] = $val;
			unset($a[$key]);
		}
		$a['hash']['vars']['start'] = 0;
		$a_json = json_encode($a, JSON_PRETTY_PRINT);

		return /** @lang JavaScript */ <<<EOF
$.onDemand["{$a['hash']['vars']['id']}"] = $a_json;
onDemandRequest("{$a['hash']['vars']['id']}");
EOF;

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

		# The (default) assumption is that all vars are where clauses.
		$start = $vars['start'];
		$length = $vars['length'];
		unset($vars['start'], $vars['length']);
		// In case columns are called start or length
		$base_query['where'] = array_merge($base_query['where'] ?: [], $vars ?: []);

		$count_query = $base_query;
		$count_query['count'] = true;

		# Get the total results
		if(!$start){
			// If this is the first batch (we only need to do this once)
			$output->set_var('total_results',$sql->select($count_query));
			$ignore_header = is_bool($ignore_header) ? $ignore_header : false;
			//if the variable is already set, will not re-set or change it
		} else {
			//If this is not the first batch, no need to post the header again
			$ignore_header = is_bool($ignore_header) ? $ignore_header : true;
			//if the variable is already set, will not re-set or change it
		}

		$output->set_var('length', $length);
		$output->set_var('start', $start + $length);

		$rows_query = $base_query;
		$rows_query['start'] = $start;
		$rows_query['length'] = $length;

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
		$a['sortable'] = false;

		$output->set_var("rows", self::generate($rows, $a, $ignore_header));
	}
}