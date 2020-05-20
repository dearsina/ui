<?php


namespace App\UI\Examples;

use App\Common\Example\ExampleInterface;
use App\UI\Card;
use App\UI\Grid;

class Accordion implements ExampleInterface {

	public function getHTML ($a = NULL)
	{
		$long_body = "Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet.";
		$rows[] = [
			"accordion" => [
				[
					"header" => "Title".rand(1,100),
					"body" => $long_body
				],
				[
					"header" => "Title".rand(1,100),
					"body" => $long_body
				],
				[
					"header" => "Title".rand(1,100),
					"body" => $long_body
				]
			]
		];
		foreach($rows as $row){
			$html .= \App\UI\Accordion::generate($row['accordion']);
		}

		$card = new Card([
			"header" => "Accordion with three elements",
			"body" => $html
		]);

		$first_card_html = $card->getHTML();

		$html = "";
		$rows = [];

		$rows[] = [
			"accordion" =>[
				"header" => "Title".rand(1,100),
				"body" => $long_body
			]
		];
		foreach($rows as $row){
			$html .= \App\UI\Accordion::generate($row['accordion']);
		}

		$card = new Card([
			"header" => "Accordion with only 1 element",
			"body" => $html
		]);

		$second_card_html = $card->getHTML();

		$html = "";
		$rows = [];

		for($i = 0; $i < 10; $i++){
			$rows[]["accordion"] = [
					"header" => "Title ".$i,
					"body" => $long_body
			];
		}

		foreach($rows as $row){
			$html .= \App\UI\Accordion::generate($row['accordion']);
		}

		$card = new Card([
			"header" => "Accordion rows that don't belong together",
			"body" => $html
		]);

		$third_card_html = $card->getHTML();

		$grid = new Grid();

		$grid->set([
			[
				"sm" => 4,
				"html" => $first_card_html
			],
			[
				"sm" => 4,
				"html" => $second_card_html
			],
			[
				"sm" => 4,
				"html" => $third_card_html
			]
		]);

		return $grid->getHTML();
	}
}