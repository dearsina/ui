<?php


namespace App\UI\Examples;


use App\Common\Example\Common;
use App\Common\str;
use App\UI\Grid;

class MySQL extends Common implements \App\Common\Example\ExampleInterface {

	/**
	 * @inheritDoc
	 */
	public function getHTML ($a = NULL)
	{
		$grid = new Grid();

		$examples[] = [
			"header" => "Complex Or",
			"query" => [
				"table" => "form_field",
				"or" => [
					"subscription_id" => "123",
					[
						"subscription_id" => NULL,
						$workflow['person'] ? "person" : "organisation" => true
					],
//					[
//						"subscription_id" => NULL,
//						"immutable" => NULL,
//					]
				]
			],
			"footer" => ""
		];

		$examples[] = [
			"header" => "Recursive and-or",
			"query" => [
				"table" => "form_field",
				"or" => [

						"subscription_id" => NULL,
						[
							["person", "IN", [1,2,3]],
							["person", "=", "abc"],
//							"person" => NULL,
							"person" => ["form_field", "organisation"],
							["organisation", "IS NOT", NULL],
						]

				]
			],
			"footer" => ""
		];

//		$examples[] = [
//			"header" => "Or will NOT an OR NULL unless you specify it",
//			"query" => [
//				"table" => "form_field",
//				"or" => [
//					"doc_type_id" => "123",
//					["doc_type_id", "IS", NULL]
//				]
//			],
//			"footer" => ""
//		];

//		$examples[] = [
//			"header" => "Long join",
//			"query" => [
//				"table" => "workflow_doc_type",
//				"left_join" => [[
//					"table" => "doc_type",
//					"id" => "doc_type_id"
//				],[
//					"table" => "form_field",
//					"on" => [
//						"doc_type_id" => ["doc_type", "doc_type_id"],
//					],
//				],[
//					"db" => "app",
//					"table" => "field_type",
//					"on" => [
//						"field_type_id" => ["form_field", "field_type_id"],
//					],
//				]]
//			],
//			"footer" => ""
//		];
//
//		$examples[] = [
//			"header" => "JSON join",
//			"query" => [
//				"db" => "user_data",
//				"table" => "workflow",
//				"join" => [[
//					"columns" => false,
//					"db" => "user_data",
//					"table" => "doc_type",
//					"on" => [
//						"doc_type_id" => ["user_data", "workflow", "doc_type_ids"]
//					]
//				]]
//			],
//			"footer" => ""
//		];
//
//		$examples[] = [
//			"header" => "Join with where and limit",
//			"query" => [
//				"columns" => [
//					"c",
//					"r",
//					"u",
//					"d",
//					"created_by"
//				],
//				"table" => "role_permission",
//				"join" => [[
//					"columns" => false,
//					"table" => "role",
//					"on" => "role_id",
//				],[
//					"columns" => false,
//					"table" => "user_role",
//					"on" => [
//						"rel_table" => ["role", "role"]
//					],
//					"where" => [
//						"rel_table" => "user"
//					]
//				],[
//					"columns" => "first_name",
//					"table" => "user",
//					"on" => [
//						"user_id" => ["role_permission", "created_by"]
//					]
//				]],
////				"where" => $where,
//				"limit" => 1
//			],
//			"footer" => ""
//		];
//
//
//		$examples[] = [
//			"header" => "Join with limit",
//			"query" => [
//				"distinct" => true,
//				"columns" => "currency_code",
//				"table" => "country",
//				"join" => [[
//					"columns" => false,
//					"table" => "geolocation",
//					"on" => "country_code"
//				],[
//					"columns" => false,
//					"table" => "connection",
//					"on" => [
//						"ip" => ["geolocation", "ip"]
//					],
//					"where" => [
//						["closed", "IS", NULL],
//						"user_id" => "123"
//					]
//				]],
//				"limit" => 1
//			],
//			"footer" => ""
//		];
//
//
//		$examples[] = [
//			"header" => "Join with count",
//			"query" => [
//				"count" => true,
//				"table" => "subscription",
//				"join" => [[
//					"table" => "subscription_plan",
//					"where" => [
//						"discount_id" => "123"
//					]
//				]]
//			],
//			"footer" => ""
//		];
//
//
//		$examples[] = [
//			"header" => "Distinct join with count",
//			"query" => [
//				"distinct" => true,
//				"columns" => [
//					"subscription_count" => ["COUNT", "subscription_id"],
//					"status"
//				],
//				"table" => "subscription",
//				"join" => [[
//					"columns" => false,
//					"table" => "subscription_plan_service",
//					"on" => "subscription_id",
//					"where" => [
//						"service_id" => "123"
//					]
//				]],
//			],
//			"footer" => ""
//		];
//
//
//		$examples[] = [
//			"header" => "Distinct join count",
//			"query" => [
//				"distinct" => true,
//				"count" => true,
//				"table" => "plan",
//				"join" => [[
//					"columns" => false,
//					"table" => "plan_service",
//					"where" => [
//						"service_id" => "234",
//					],
//				]],
//				"where" => [
//					["retired", "IS NOT", NULL],
//				],
//			],
//			"footer" => ""
//		];
//
//		$examples[] = [
//			"header" => "Insert",
//			"method" => "insert",
//			"query" => [
//				"table" => "user",
//				"set" => [[
//					"this column doesn't exist" => "column will be ignored",
//					"first_name" => "First",
//					"last_name" => "Last",
//					"2fa_enabled" => "true",
//					"verified" => "01-12-1984"
//				],[
//					"last_name" => "Last",
//					"email" => "email@address.com"
//				]]
//			],
//			"footer" => ""
//		];
//
//		$examples[] = [
//			"header" => "GROUP CONCAT",
//			"query" => [
//				"columns" => [
//					"currency_code",
//					"countries" => ["group_concat", [
//						"distinct" => true,
//						"columns" => "name",
//						"order_by" => [
//							"country" => "ASC"
//						],
//						"separator" => ", "
//						]
//					]
//				],
//				"table" => "country",
//				"where" => [
//					["currency_code", "<>", ""]
//				],
//				"order_by" => [
//					"currency_code" => "ASC"
//				]
//			],
//		];
//
//		$examples[] = [
//			"header" => "GROUP CONCAT",
//			"query" => [
//				"columns" => [
//					"plan_count" => ["COUNT", "plan_id"],
//					"plan_names" => ["GROUP_CONCAT", [
//						"distinct" => true,
//						"columns" => [
//							"title"
//						],
//						"separator" => "|"
//					]]
//				],
//				"table" => "plan",
//				"join" => [[
////					"columns" => false,
//					"table" => "plan_service",
//					"where" => [
//						"service_id" => "123"
//					]
//				]],
//				"limit" => 1
//			],
//		];
//
//		$examples[] = [
//			"header" => "Active cron jobs only",
//			"query" => [
//				"table" => "cron_job",
//				"where" => [
//					"paused" => NULL
//				],
//				"order_by" => [
//					"order" => "ASC"
//				]
//			],
//		];
//
//		$examples[] = [
//			"header" => "Simple remove",
//			"method" => "remove",
//			"query" => [
//				"table" => "interpol_red_notice",
//				"db" => "public_list",
//				"id" => "123",
//			],
//		];
//
//		$examples[] = [
//			"header" => "Simple restore",
//			"method" => "restore",
//			"query" => [
//				"table" => "interpol_red_notice",
//				"db" => "public_list",
//				"id" => "123",
//			],
//		];
//
//		$examples[] = [
//			"header" => "Update with ID + DB",
//			"method" => "update",
//			"query" => [
//				"table" => "interpol_red_notice",
//				"db" => "public_list",
//				"id" => "123",
//				"set" => [
//					"first_name" => "George"
//				]
//			],
//			"footer" => "Will give you the whole table"
//		];
//
		$examples[] = [
			"header" => "Complex set, and complex where",
			"method" => "update",
			"query" => [
				"table" => "cron_job",
				"set" => [
					"order" => [NULL, "cron_job", "order", "+ 1"],
					"title" => "TEXT << WILL STILL BE TRUNCATED BECAUSE TITLE IS NOT A LONG ENOUGH COLUMN",
					"desc" => "TEXT << WON'T BE TRUNCATED BECAUSE COL NAME IS IN HTML ARRAY"
				],
				"html" => ["title","desc"],
				"where" => [
					["order", "between", 1, 10]
				]
			],
		];
//
//		$examples[] = [
//			"header" => "Update with join",
//			"method" => "update",
//			"query" => [
//				"table" => "interpol_red_notice",
//				"db" => "public_list",
//				"join" => [[
//					"table" => "user_role",
//					"on" => "user_id",
//					"where" => [
//						"rel_table" => "alias"
//					],
////					"include_removed" => true
//				]],
////				"id" => "123",
//				"set" => [
//					"first_name" => ["user_role", "user_id"]
//				]
//			],
//			"footer" => "Will give you the whole table"
//		];
//
//
//
//		$examples[] = [
//			"header" => "Just the table name",
//			"query" => [
//				"table" => "user"
//			],
//			"footer" => "Will give you the whole table"
//		];
//
//		$examples[] = [
//			"header" => "Order By and Limits",
//			"query" => [
//				"columns" => [
//					"first_name",
//					"last_name_alias" => "last_name",
//				],
//				"table" => "user",
//				"order_by" => [
//					"first_name" => "ASC",
//					"last_name_alias" => "DESC",
//					"user_id" => "ASC"
//				],
//				"limit" => 3
//			],
//		];
//
//		$examples[] = [
//			"header" => "Start and length",
//			"query" => [
//				"columns" => "first_name",
//				"table" => "user",
//				"limit" => [10, 5]
//			],
//			"footer" => "Start and length are fed as an array to the length key."
//		];
//
//		$examples[] = [
//			"header" => "Table + ID",
//			"query" => [
//				"table" => "user",
//				"id" => "id_value"
//			],
//			"footer" => "Will give you a single row (if the ID exists)"
//		];
//
//		$examples[] = [
//			"header" => "Columns",
//			"query" => [
//				"columns" => [
//					"user_id",
//					"name" => "first_name",
//					"wrong_col_name" => "wrong_name",
//					"ignored_col" => [1,2,3],
//					"aggregate_col" => ["count", "last_name"]
//				],
//				"table" => "user"
//			],
//			"footer" => "Columns can be either a column name, an alias-column name key value pair, an alias-aggregate function-column name key => [agg, val] triangular pair. Anything else is ignored. Columns that don't exist are also ignored."
//		];
//
//		$examples[] = [
//			"header" => "Unusual aliases",
//			"query" => [
//				"columns" => [
//					"Single'quote" => "user_id",
//					"Double\"quote" => "user_id",
//					"Grave`accent" => "user_id",
//					"SlashR\r\nSlashN" => "user_id",
//				],
//				"table" => "user",
////				"db" => "public_list"
//			]
//		];
//
//		$examples[] = [
//			"header" => "Different database",
//			"query" => [
//				"table" => "user",
////				"db" => "public_list"
//			]
//		];
//
//		$examples[] = [
//			"header" => "Join on table in different database",
//			"query" => [
//				"table" => "user",
//				"join" => [
//					"table" => "user",
////					"db" => "public_list",
//					"order_by" => [
//						"first_name" => "ASC"
//					]
//				]
//			],
//		];
//
//		$examples[] = [
//			"header" => "Join with child join",
//			"query" => [
//				"columns" => "user_id",
//				"table" => "user",
//				"join" => [[
//					"columns" => "connection_id",
//					"table" => "connection",
//					"on" => "user_id",
//					"where" => [
//						["fd", ">=", 4100]
//					]
//				],[
//					"columns" => "country_name",
//					"table" => "geolocation",
//					"on" => [
//						"ip" => ["connection", "ip_address"]
//					]
//				]],
//			]
//		];
//
//		$examples[] = [
//			"header" => "Join with child join and limit",
//			"query" => [
//				"columns" => "user_id",
//				"table" => "user",
//				"join" => [[
//					"columns" => "connection_id",
//					"table" => "connection",
//					"on" => "user_id",
//					"where" => [
//						["fd", ">=", 4000]
//					]
//				],[
//					"columns" => "country_name",
//					"table" => "geolocation",
//					"on" => [
//						"ip" => ["connection", "ip_address"]
//					]
//				]],
//				"limit" => 5
//			],
//			"footer" => "Creates fancy sub query"
//		];
//
//		$examples[] = [
//			"header" => "Table with complex join",
//			"query" => [
//				"columns" => "user_id",
//				"table" => "user",
////				"db" => "public_list",
//				"join" => [[
//					"columns" => "user_id",
//					"table" => "user"
//				],[
//					"columns" => "user_id",
//					"table" => "user",
////					"db" => "public_list",
//				],[
//					"columns" => "user_role_id",
//					"table" => "user_role",
//				],[
//					"columns" => "admin_id",
//					"table" => "admin",
//					"on" => [
//						"admin_id" => ["user_role", "rel_id"],
//					]
//				],[
//					"columns" => ["FILE_ID"],
//					"table" => "FILES",
//					"db" => "information_schema",
//					"on" => [
//						"FILE_ID" => ["user", "user_id"]
//					],
//					"include_removed" => true
//				]]
//			],
//		];
//
//		$examples[] = [
//			"header" => "Must have at least one column",
//			"query" => [
//				"columns" => "user_id",
//				"table" => "user",
//				"where" => [
//					["user_id", "IS NOT", NULL]
//				]
//			],
//			"footer" => "Will otherwise result in an error when run."
//		];
//
//		$examples[] = [
//			"header" => "Why does this error?",
//			"query" => array (
//				'table' => 'error_log',
//				'left_join' =>
//					array (
//						0 =>
//							array (
//								'table' => 'user',
//								'on' =>
//									array (
//										'user_id' =>
//											array (
//												0 => 'error_log',
//												1 => 'created_by',
//											),
//									),
//							),
//					),
//				'where' =>
//					array (
//						0 =>
//							array (
//								0 => 'resolved',
//								1 => '=',
//								2 => NULL,
//							),
//						1 =>
//							array (
//								0 => 'resolved',
//								1 => '<>',
//								2 => false,
//							),
//						'id' => 'on_demand_table_208304139',
//					),
//				'order_by' =>
//					array (
//						'created' => 'desc',
//					),
//				'count' => true,
//			),
//		];
//
//		$examples[] = [
//			"header" => "Column formatting",
//			"query" => [
//				"columns" => [
//					"first_name",
//					"last_name",
//					"column_alias" => "user_id",
//					"wrong_name" //Will be ignored
//				],
//				"table" => "user"
//			],
//			"footer" => "The last column name is invalid and will be ignored without warning."
//		];
//
//		$examples[] = [
//			"header" => "Simple join",
//			"query" => [
//				"table" => "user",
//				"join" => "user_role"
//			],
//			"footer" => "If the entire join is just a table name, will assume the key is the main table ID column."
//		];
//
//		$examples[] = [
//			"header" => "Aggregate functions with distinct",
//			"query" => [
//				"distinct" => true,
//				"columns" => [
//					"rel_table",
//					"c",
//					"r",
//					"u",
//					"d",
//					"count_rel_id" => ["count", "rel_id"], //"COUNT(DISTINCT `rel_id`)"
//				],
//				"table" => "role_permission",
//				"where" => [
//					"role_id" => "some_value"
//				]
//			]
//		];
//
//		$examples[] = [
//			"header" => "Aggregate function with limit",
//			"query" => [
//				"columns" => [
//					"max_order" => ["max", "order"],
//				],
//				"table" => "cron_job",
//				"limit" => 1,
//			]
//		];
//
//		$examples[] = [
//			"header" => "Where AND and OR",
//			"query" => [
//				"columns" => [
//					"c",
//					"r",
//					"u",
//					"d",
//				],
//				"table" => "user_permission",
//				"where" => [
//					"user_id" => $user_id,
//					"rel_table" => $rel_table,
//				],
//				"or" => [
//					["rel_id", "in", [$unset_val, NULL]]
//				],
//				"limit" => 1
//			]
//		];
//
		$examples[] = [
			"header" => "Join with all the different comparison formats.",
			"query" => [
				"table" => "user",
				"join" => [
					"table" => "user_role",
					"on" => [
						"complete = comparison",
						["user_id", "=", "val"],
						["created", "<>", false],
						["user_id", "LIKE", "%val%"],
						["user_id", ">", "user", "user_id"],
						["user_id", "between", 1, 3],
					],
					"where" => [
						"role" => "admin",
						"rel_table" => "admin",

						"complete = comparison",
						["user_id", "=", "val"],
						["created", "<>", false],
						["user_id", "LIKE", "%val%"],
						["user_id", ">", "user", "user_id"],
						["user_id", "between", 1, 3],
					]
				],
				"where" => [
					"first_name" => "val",
					"last_name" => ["table_alias", "col"]
				]
			],
			"footer" => "<code>false</code> comparison values will be ignored."
		];

		foreach($examples as $example){
			$grid->set($this->getSQLCard($example));
		}

		$this->output->html($grid->getHTML());
	}

	private function getSQLCard(array $a): string
	{
		extract($a);
		$method = $method ?: "select";

		$card = new \App\UI\Card\Card([
			"header" => $header,
			"body" => ["html" => [[[
				"html" => str::pre(str::var_export($query, true), false, "php")
			],[
				"html" => str::pre($this->sql->{$method}($query, 1), false, "sql")
			]]]],
			"footer" => $footer
		]);
		return $card->getHTML();
	}
}