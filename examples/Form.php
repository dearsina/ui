<?php


namespace App\UI\Examples;

use App\Common\Example\ExampleInterface;
use App\Common\Prototype;
use Ramsey\Uuid\Codec\TimestampFirstCombCodec;
use Ramsey\Uuid\Generator\CombGenerator;
use Ramsey\Uuid\UuidFactory;

class Form extends Prototype implements ExampleInterface {

	private function getKeyValues($max_i){
		for($i = 0; $i < $max_i; $i++){
			$pair[$i] = "This is key {$i}";
		}
		return $pair;
	}

	public function getHTML ($a = NULL) {
		$desc = "Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus.";

		$factory = new UuidFactory();
		$codec = new TimestampFirstCombCodec($factory->getUuidBuilder());

		$factory->setCodec($codec);

		$factory->setRandomGenerator(new CombGenerator(
			$factory->getRandomGenerator(),
			$factory->getNumberConverter()
		));

		$timestampFirstComb = $factory->uuid4();

//		printf(
//			"UUID: %s\nVersion: %d\nBytes: %s\n",
//			$timestampFirstComb->toString(),
//			$timestampFirstComb->getFields()->getVersion(),
//			bin2hex($timestampFirstComb->getBytes())
//		);

		$desc = $timestampFirstComb->toString();

		$fields[] = [
			"type" => "input",
			"name" => "username",
			"alt" => "This is the alt text >>",
			"placeholder" => "Placeholder",
			"label" => [
				"title" => "Longer label to show length",
				"desc" => $desc
			],
			"desc" => $desc,
			"required" => true
		];

		$fields[] = [
			"id" => "this-has-an-id",
			"type" => "select",
			"name" => "selectwithid",
			"placeholder" => "With ID",
			"options" => $this->getKeyValues(30),
//			"value" => 4,
			"required" => true
		];

		$fields[] = [
			"type" => "select",
			"name" => "selectwithoutid",
			"placeholder" => "Without ID",
			"options" => $this->getKeyValues(30),
//			"value" => 4,
			"required" => true
		];

		$fields[] = [
			"type" => "select",
			"name" => "somedropdown_multi",
			"placeholder" => "PllAAcc",
			"options" => $this->getKeyValues(30),
//			"value" => [4,10],
			"multiple" => true,
			"required" => "Narrative"
		];

		$fields[] = [[
			"type" => "input",
			"disabled" => true,
			"name" => "disabled"
		],[
			"type" => "input",
			"disabled" => true,
			"name" => "disabled"
		]];

		$fields[] = [
			"type" => "textarea",
			"name" => "txa",
			"placeholder" => "The Placeholder",
			"alt" => "the Alt text",
			"desc" => $desc,
			"value" => "Value",
			"rows" => 5,
			"required" => true
		];

		$form = new \App\UI\Form\Form([
			"action" => "example_action",
			"rel_table" => NULL,
			"rel_id" => NULL,
			"fields" => $fields,
			"buttons" => ["save", "cancel"]
		]);

		$card = new \App\UI\Card\Card([
			"header" => "I am in need",
			"body" => $form->getHTML(),
			"footer" => "Footer",
		]);

//		$card_array[] = $card->getHTML();

		$fields = [];

		$fields[] = [
			"type" => "select",
			"name" => "test-select",
			"placeholder" => "Test select",
			"options" => $this->getKeyValues(30),
			"value" => 4,
//			"required" => true
		];

		$form = new \App\UI\Form\Form([
			"action" => "example_action",
			"rel_table" => NULL,
			"rel_id" => NULL,
			"fields" => $fields,
			"buttons" => ["save", "cancel"]
		]);

		$card = new \App\UI\Card\Card([
			"header" => "I am in need",
			"body" => $form->getHTML(),
			"footer" => "Footer",
		]);

		$card_array[] = $card->getHTML();

		$fields = [];

		$fields[]  = [
			"html" => "<p>This is just <b>HTML</b></p>"
		];

		$fields[] = [
			"type" => "input",
			"name" => "user_icon",
			"icon" => "user",
			"required" => true
		];

		$fields[] = [
			"type" => "range",
			"name" => "estimate",
			"desc" => $desc,
//			"label" => false,
			"min" => 0,
			"max" => 5000,
			"step" => 1,
//			"multiple" => 1,
//			"prefix" => "P",
//			"suffix" => "%",
			"min_colour" => [255,0,0],
			"max_colour" => [0,255,0],
//			"default" => 10,
			"value" => $estimate,
			"col" => 5,
			"col_class" => "col-margin-tight",
			"col_style" => "padding-left: .5rem;padding-right: 0;",
			"alt" => 'Your best guess at the odds" of this guest attending.'
		];

		$fields[] = [
			"type" => "range",
			"name" => "estimate_xx",
			"label" => false,
			"min" => 0,
			"max" => 1,
			"step" => 0.1,
			"multiple" => 100,
			"prefix" => "P",
			"suffix" => "%",
			"min_colour" => [255,0,0],
			"max_colour" => [0,255,0],
			"default" => .5,
			"value" => $estimate,
			"col" => 5,
			"col_class" => "col-margin-tight",
			"col_style" => "padding-left: .5rem;padding-right: 0;",
			"alt" => "Your best guess at the odd's of this guest attending."
		];

		$fields[] = [
			"type" => "input",
			"name" => "icon_suffix",
			"icon_suffix" => "user",
			"required" => true
		];

		$fields[] = [
			"type" => "input",
			"name" => "text_suffix",
			"icon_suffix" => ["title" => "could be anything"],
			"required" => $desc
		];

		$fields[] = [
			"type" => "radio",
			"name" => "simple_radio",
			"label" => [
				"label" => "This, the label",
				"desc" => "This radio field only has simple values",
			],
			"parent_desc" => "Parent desc also works",
			"value" => 3,
			"options" => explode(" ", "Exploded items from string list")
		];

		$fields[] = [
			"type" => "radio",
			"name" => "radiofield_xx",
			"value" => 3,
			"options" => [
				"" => "None",
				"1" => "Simple value",
				"2" => [
					"label" => "Complex value",
					"desc" => $desc
				],
				"3" => [
					"label" => [
						"title" => "Label as array",
						"desc" => $desc
					]
				]
			]
		];

		$fields[] = [
			"type" => "radio",
			"name" => "numaradio",
			"label" => "Radiofield with numerical array options",
			"value" => false,
			"options" => ["Red", "White", "Blue"],
			"required" => true
		];

		$fields[] = [
			"type" => "radio",
			"name" => "numaradio_input",
			"label" => "Radiofield with numerical array options",
			"value" => false,
			"options" => ["Red", "White", "Blue",[
				"type" => "text"
			]],
			"required" => true
		];

		$form = new \App\UI\Form\Form([
			"action" => "example_action",
			"rel_table" => NULL,
			"rel_id" => NULL,
			"fields" => $fields,
			"buttons" => ["save", "cancel"]
		]);

		$card = new \App\UI\Card\Card([
			"header" => "I am in need",
			"body" => $form->getHTML(),
			"footer" => "Footer",
		]);

		$card_array[] = $card->getHTML();

		$fields = [];

		$fields[] = [
			"type" => "radio",
			"name" => "numaradio_select",
			"label" => "Radiofield with numerical array options",
			"value" => false,
			"options" => ["Red", "White", "Blue",[
				"type" => "select",
				"options" => $this->getKeyValues(50),
				"desc" => $desc
			], "orange"],
			"required" => true
		];

		$fields[] = [
			"type" => "checkbox",
			"name" => "checkbox_select",
			"value" => [1,2],
			"options" => [
				"1" => "Simple value",
				"2" => [
					"label" => "Complex value",
					"desc" => $desc
				],
				"3" => [
					"label" => [
						"title" => "Label as array",
						"desc" => $desc
					]
				],
				"4" => [
					"type" => "select",
					"placeholder" => "A very long place holder",
					"options" => $this->getKeyValues(10),
					"desc" => $desc
				]
			],
			"required" => true
		];

		$fields[] = [
			"type" => "checkbox",
			"name" => "checkbox_input",
			"value" => [1,2],
			"options" => [
				"1" => "Simple value",
				"2" => [
					"label" => "Complex value",
					"desc" => $desc
				],
				"3" => [
					"label" => [
						"title" => "Label as array",
						"desc" => $desc
					]
				],
				"4" => [
					"type" => "input",
//					"title" => "The input title",
					"placeholder" => "Title won't work, but long place holder does",
					"desc" => "Or a description to why there is an \"other\" field"
				]
			],
			"required" => true
		];

		$fields[] = [
			"type" => "hidden",
			"name" => "hidden_field",
			"value" => "some_hidden_value"
		];

		$fields[] = [
			"type" => "email",
			"name" => "email",
			"desc" => "This is an email type field."
		];

		$fields[] = [
			"type" => "checkbox",
			"label" => [
				"title" => "A checked checkbox",
				"desc" => "this is the desc"
			],
			"name" => "single_value_checkbox_checked",
			"checked" => true
		];

		$fields[] = [
			"type" => "checkbox",
			"label" => [
				"title" => "An empty checkbox",
				"desc" => "this is the desc"
			],
			"name" => "single_value_checkbox_unchecked",
		];

		$form = new \App\UI\Form\Form([
			"action" => "example_action",
			"rel_table" => NULL,
			"rel_id" => NULL,
			"fields" => $fields,
			"buttons" => ["save", "cancel"]
		]);

		$card = new \App\UI\Card\Card([
			"header" => "I am in need",
			"body" => $form->getHTML(),
			"footer" => "Footer",
		]);

		$card_array[] = $card->getHTML();

		$grid = new \App\UI\Grid();
		$grid->set($card_array);

		$this->output->html($grid->getHTML());
	}
}