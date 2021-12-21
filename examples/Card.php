<?php


namespace App\UI\Examples;


use App\Common\Example\ExampleInterface;
use App\Common\Prototype;

class Card extends Prototype implements ExampleInterface {
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
				"hash" => "#",
				"basic" => rand(0,1) == 1
			];
		}
		return $button;
	}

	function dropdown_buttons(){
		return [[
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
		],
			true
		,[
			"title" => "Another title",
			"icon" => "trash",
			"hash" => [
				"rel_table" => "rel_table"
			]
		]];
	}
	public function getHTML ($a = NULL) {
		$card = new \App\UI\Card\Card([
			"body" => "This is the body."
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card\Card([
			"header" => [
				"title" => "This is the header",
				"buttons" => $this->dropdown_buttons()
			],
			"body" => "This is the body.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small"
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card\Card([
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

		$card = new \App\UI\Card\Card([
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

		$card = new \App\UI\Card\Card([
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
				"buttons" => $this->dropdown_buttons()
			]
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card\Card([
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

		$card = new \App\UI\Card\Card([
			"header" => [
				"title" => "Resizable",
			],
			"body" => "This card is resizable.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small",
			],
			"resizable" => true
		]);

		$html .= $card->getHTML();

		$card = new \App\UI\Card\Card([
			"header" => [
				"title" => "Draggable",
			],
			"body" => "This card is draggable. You should use <pre>Windows()</pre> instead though.",
			"footer" => [
				"html" => "This is the footer.",
				"class" => "small",
			],
			"draggable" => true
		]);

		$html .= $card->getHTML();

		$grid = new \App\UI\Grid();
		$grid->set($html);

		$this->output->html($grid->getHTML());
	}
}