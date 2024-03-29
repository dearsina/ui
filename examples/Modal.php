<?php


namespace App\UI\Examples;


use App\Common\Prototype;
use App\Common\str;
use App\UI\Card\Card;
use App\UI\Page;

class Modal extends Prototype implements \App\Common\Example\ExampleInterface {
	private $html;

	/**
	 * @param null $a
	 *
	 * @return void
	 */
	public function getHTML ($a = NULL): void
	{
		if(!$a['vars']['type']){
			$this->index($a);
			return;
		}

		$this->hash->set(-1);
		$this->hash->silent();
		$this->output->clear();

		$this->{$a['vars']['type']}();
	}

	private function getModalCard($header, $type, $body){
		$card = new Card([
			"header" => str::title($header),
			"body" => str::pre($body, [
				"language" => "php"
			]),
			"footer" => [
				"button" => [
					"hash" => [
						"rel_table" => "example",
						"rel_id" => "modal",
						"vars" => [
							"type" => $type
						]
					],
					"title" => "Open"
				]
			]
		]);

		return $card->getHTML();
	}

	/**
	 * @param $a
	 */
	private function index($a): void{
		extract($a);

		$simple =
'$modal = new Modal([
	"header" => "Header",
	"body" => "Body",
	"footer" => "Footer",
]);		
';

		$simple_approval =
'$modal = new Modal([
	"header" => "Header",
	"body" => "Body",
	"footer" => "Footer",
	"approve" => true,
]);		
';

		$complex_approval =
'$modal = new \App\UI\Modal\Modal([
	"header" => "Header",
	"body" => "Body",
	"footer" => "Footer",
	"approve" => [
		"icon" => "user",
		"colour" => "danger",
		"title" => "Title with icon",
		"message" => "Custom message in custom colour"
	],
]);
';

		$draggable_resizable =
'$modal = new Modal([
	"header" => "Header",
	"body" => "Body",
	"footer" => "Footer",
	"draggable" => true,
	"resizable" => true,
]);		
';

		$simple_with_child =
			'$modal = new \App\UI\Modal\Modal([
	"size" => "xl",
	"header" => "Parent header",
	"body" => "Parent body",
	"footer" => [
		"button" => [
			"title" => "Open child",
			"hash" => [
				"rel_table" => "example",
				"rel_id" => "modal",
				"vars" => [
					"type" => "simple"
				]
			]
		]
	],
]);	
';

		$simple_with_child_approval =
			'$modal = new \App\UI\Modal\Modal([
	"size" => "xl",
	"header" => "Parent header",
	"body" => "Parent body",
	"footer" => [
		"button" => [
			"title" => "Open child",
			"hash" => [
				"rel_table" => "example",
				"rel_id" => "modal",
				"vars" => [
					"type" => "simple_approval"
				]
			]
		]
	],
]);	
';

		$page = new Page([
			"title" => str::title($rel_id)
		]);

		$page->setGrid([[
			"html" => $this->getModalCard("Simple modal", "simple", $simple)
		],[
			"html" => $this->getModalCard("Modal with simple approval", "simple_approval", $simple_approval)
		],[
			"html" => $this->getModalCard("Modal with complex approval", "complex_approval", $complex_approval)
		],[
			"html" => $this->getModalCard("Dragggable resizeable modal", "draggable_resizable", $draggable_resizable)
		],[
			"html" => $this->getModalCard("Simple with child", "simple_with_child", $simple_with_child)
		],[
			"html" => $this->getModalCard("simple_with_child_approval", "simple_with_child_approval", $simple_with_child_approval)
		]]);

		$this->output->html($page->getHTML());
	}

	private function simple(): void{
		$modal = new \App\UI\Modal\Modal([
			"header" => "Header",
			"body" => "Body",
			"footer" => "Footer",
		]);

		$this->output->modal($modal->getHTML());
	}

	private function simple_with_child(){
		$modal = new \App\UI\Modal\Modal([
			"size" => "xl",
			"header" => "Parent header",
			"body" => "Parent body",
			"footer" => [
				"button" => [
					"title" => "Open child",
					"hash" => [
						"rel_table" => "example",
						"rel_id" => "modal",
						"vars" => [
							"type" => "simple"
						]
					]
				]
			],
		]);

		$this->output->modal($modal->getHTML());
	}

	private function simple_with_child_approval(){
		$modal = new \App\UI\Modal\Modal([
			"size" => "xl",
			"header" => "Parent header",
			"body" => "Parent body",
			"footer" => [
				"button" => [
					"title" => "Open child",
					"hash" => [
						"rel_table" => "example",
						"rel_id" => "modal",
						"vars" => [
							"type" => "simple_approval"
						]
					]
				]
			],
		]);

		$this->output->modal($modal->getHTML());
	}

	private function simple_approval(){
		$modal = new \App\UI\Modal\Modal([
			"header" => "Header",
			"body" => "Body",
			"footer" => "Footer",
			"approve" => true,
		]);

		$this->output->modal($modal->getHTML());
	}

	private function complex_approval(){
		$modal = new \App\UI\Modal\Modal([
			"header" => "Header",
			"body" => "Body",
			"footer" => "Footer",
			"approve" => [
				"icon" => "user",
				"colour" => "danger",
				"title" => "Title with icon",
				"message" => "Custom message in custom colour"
			],
		]);

		$this->output->modal($modal->getHTML());
	}

	private function draggable_resizable(){
		$modal = new \App\UI\Modal\Modal([
			"header" => "Header",
			"body" => "Body",
			"footer" => "Footer",
//			"approve" => true,
			"draggable" => true,
			"resizable" => true,
		]);

		$this->output->modal($modal->getHTML());
	}
}