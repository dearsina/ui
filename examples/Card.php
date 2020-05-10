<?php


namespace App\UI\Examples;


class Card {
	function buttons_in_all_colours(){
		$colours = [
			"primary",
			"secondary",

			"success",
			"warning",
			"danger",
			"info",

			"navy",
			"blue",
			"aqua",
			"teal",
			"olive",
			"green",
			"lime",
			"yellow",
			"orange",
			"red",
			"maroon",
			"fuchsia",
			"purple",

			"black",
			"gray",
			"silver",
		];
		foreach($colours as $colour){
			$button[] = [
				"colour" => $colour,
				"title" => $colour,
				"hash" => "#"
			];
		}
		return $button;
	}
	function getHTML(){
		$card = new \App\UI\Card([
			"body" => "This is the body."
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card([
			"header" => "This is the header",
			"body" => "This is the body.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small"
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card([
			"header" => [
				"colour" => "green",
				"title" => "A card with two buttons in the header",
				"icon" => [
					"colour" => "red",
					"name" => "trash"
				],
				"button" => $this->buttons_in_all_colours()
			],
			"body" => "This is the body.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small"
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card([
			"header" => [
				"title" => "A card with an exceptionally longer header text that will encroach on the buttons",
				"icon" => [
					"name" => "arrow-left"
				],
				"button" => $this->buttons_in_all_colours()
			],
			"body" => "This is the body.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small"
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card([
			"header" => [
				"colour" => "green",
				"title" => "A card with two buttons in the header",
				"icon" => [
					"colour" => "red",
					"name" => "trash"
				]
			],
			"body" => "This is the body.",
			"footer" => [
				"html" => "This is the footer.",
				"buttons" => [[
					"title" => "Coloured title",
					"colour" => "warning",
					"icon" => "user",
					"hash" => [
						"rel_table" => "rel_table"
					]
				],[
					"header" => "Header",
					"colour" => "success",
					"style" => [
						"text-transform" => "uppercase"
					]
				],[
					"title" => "Another title",
					"icon" => "trash",
					"hash" => [
						"rel_table" => "rel_table"
					]
				]]
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card([
			"header" => [
				"title" => "Colours",
			],
			"body" => "These are all the colours.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small",
				"button" => $this->buttons_in_all_colours()
			]
		]);

		$html .= $card->getHTML();

		return $html;
	}
}