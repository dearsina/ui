<?php


namespace App\UI\Examples;


class Grid {
	function getHTML(){
		$grid = new \App\UI\Grid();
		$grid->set("<div style='background-color:red;'>This is a string</div>");
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([
			"html" => "This is an array",
			"style" => [
				"background-color" => "green"
			]
		]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([[
			"html" => "Siblings",
			"style" => [
				"background-color" => "blue"
			]
		],[
			"html" => "Siblings",
			"style" => [
				"background-color" => "cyan"
			]
		]]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([
			"html" => "This is a parent",
			"style" => [
				"background-color" => "green"
			]
		]);
		$grid->set([[
			"html" => "Siblings",
			"style" => [
				"color" => "white",
				"background-color" => "#003300"
			]
		],[
			"html" => "Siblings",
			"style" => [
				"color" => "white",
				"background-color" => "#006600"
			]
		]]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([
			"html" => "This is a parent",
			"style" => [
				"background-color" => "green"
			]
		]);
		$grid->set([[
			"html" => "Siblings",
			"style" => [
				"color" => "white",
				"background-color" => "#003300"
			]
		],[
			"html" => "Siblings",
			"style" => [
				"color" => "white",
				"background-color" => "#006600"
			]
		]]);
		$grid->set([[
			[[
				"html" => "Child siblings",
				"style" => [
					"color" => "white",
					"background-color" => "#000033"
				]
			],[
				"html" => "Child siblings",
				"style" => [
					"color" => "white",
					"background-color" => "#000066"
				]
			]]
		],[
			"html" => "Siblings",
			"style" => [
				"color" => "white",
				"background-color" => "#006600"
			]
		]]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([[
			"html" => "Siblings",
			"sm" => 8,
			"style" => [
				"background-color" => "yellow"
			]
		],[
			"html" => "Siblings",
			"sm" => 4,
			"style" => [
				"background-color" => "orange"
			]
		]]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([[
			"sm" => 8,
		],[
			"html" => "Only child",
			"sm" => 2,
			"style" => [
				"background-color" => "orange"
			]
		]]);
		$html .= $grid->getHTML();

		$html .= "<hr/>";

		$grid = new \App\UI\Grid();
		$grid->set([[
			"sm" => 4,
		],[
			"sm" => 6,
			"id" => "set",
			"html" => [[
				"id" => "new",
				"html" => "Newphew",
				"style" => [
					"background-color" => "#EE0000"
				]
			],[
				"id" => "nie",
				"html" => "Niece",
				"style" => [
					"background-color" => "#DD0000"
				]
			]]
		],[
			"html" => "Single parent",
			"sm" => 2,
			"style" => [
				"background-color" => "orange"
			]
		]]);

		$html .= $grid->getHTML();

		return $html;
	}
}