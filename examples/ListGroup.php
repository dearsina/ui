<?php


namespace App\UI\Examples;

use App\Common\Example\Common;
use App\Common\Example\ExampleInterface;
use App\Common\str;
use App\UI\Card\Card;
use App\UI\Grid;
use App\UI\Icon;

class ListGroup extends Common implements ExampleInterface {

	private function items(int $number_of_items = 3){
		for($i = 0; $i < $number_of_items; $i ++){
			$items[] = [
				"html" => "Item {$i}",
//				"colour" => $this->getRandomColour()
			];
		}
		return $items;
	}
	public function getHTML ($a = NULL)
	{
		$grid = new Grid();

		$items = [[
			"html" => "Item with colour",
			"colour" => "info",
		],[
			"html" => "Item with badge",
			"badge" => "999",
		],[
			"html" => "Item as link",
			"hash" => "rel_table"
		],[
			"html" => "Item as active link",
			"active" => true,
			"hash" => "rel_table"
		],[
			"html" => "Item with button",
			"button" => [[
				"icon" => Icon::get("play"),
				"size" => "s",
				"hash" => "rel_table"
			]]
		],[
			"title" => "Title and body with button",
			"body" => "Title and body with button",
			"button" => [[
				"icon" => Icon::get("play"),
				"size" => "s",
				"hash" => "rel_table"
			]]
		],[
			"html" => "Item as disabled link",
			"disabled" => true,
			"hash" => "rel_table"
		],[
			"html" => "Item with an icon",
			"icon" => "globe"
		],
			"Simple item"
		];

		$code = '
$items = [[
	"html" => "Item with colour",
	"colour" => "info",
],[
	"html" => "Item with badge",
	"badge" => "999",
],[
	"html" => "Item as link",
	"hash" => "rel_table"
],[
	"html" => "Item as active link",
	"active" => true,
	"hash" => "rel_table"
],[
	"html" => "Item with button",
	"button" => [[
		"icon" => Icon::get("play"),
		"size" => "s",
		"hash" => "rel_table"
	]]
],[
	"title" => "Title and body with button",
	"body" => "Title and body with button",
	"button" => [[
		"icon" => Icon::get("play"),
		"size" => "s",
		"hash" => "rel_table"
	]]
],[
	"html" => "Item as disabled link",
	"disabled" => true,
	"hash" => "rel_table"
],[
	"html" => "Item with an icon",
	"icon" => "globe"
],
	"Simple item"
];

ListGroup::generate($items);';

		$card1 = new Card([
			"header" => "Different types of items",
			"body" => \App\UI\ListGroup::generate($items),
			"footer" => str::pre($code),
		]);

		$items = [
			"Simple item",
			"Another simple item",
			"Third simple item",
		];

		$code = '
$items = [
	"Simple item",
	"Another simple item",
	"Third simple item",
];

ListGroup::generate([
	"items" => $items,
	"flush" => true
]);';

		$card2 = new Card([
			"header" => "Flush items",
			"body" => \App\UI\ListGroup::generate([
				"items" => $items,
				"flush" => true
			]),
			"footer" => str::pre($code),
		]);

		$items = [[
			"html" => "Item with colour",
			"colour" => "warning",
		],[	"html" => "Item with link",
			"hash" => "rel_table"
		],[	"html" => "Item with badge",
			"badge" => "3",
		]];

		$code = '
$items = [[
	"html" => "Item with colour",
	"colour" => "warning",
],[	"html" => "Item with link",
	"hash" => "rel_table"
],[	"html" => "Item with badge",
	"badge" => "3",
]];

ListGroup::generate([
	"items" => $items,
	"horizontal" => true
]);';

		$card3 = new Card([
			"header" => "Horizontal items",
			"body" => \App\UI\ListGroup::generate([
				"items" => $items,
				"horizontal" => true
			]),
			"footer" => str::pre($code),
		]);

		$grid->set([
			$card1->getHTML(),
			$card2->getHTML(),
			$card3->getHTML(),
		]);


		$this->output->html($grid->getHTML());
	}
}