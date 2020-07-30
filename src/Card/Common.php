<?php


namespace App\UI\Card;


use App\Common\str;
use App\UI\Form\Form;
use App\UI\Icon;

abstract class Common extends \App\Common\Common {
	/**
	 * Generic edit card frame.
	 *
	 * @param        $a
	 * @param string $size
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	public function edit(array $a): string
	{
	    extract($a);

		$$rel_table = $this->info($rel_table, $rel_id);

		$buttons = ["save","cancel"];

		[$field_class, $method] = $this->getFieldClassAndMethod($rel_table);

		$form = new Form([
			"action" => "update",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $field_class::{$method}($$rel_table),
			"buttons" => $buttons,
			"modal" => true
		]);

	    $card = new \App\UI\Card\Card([
			"header" => [
				"title" => str::title($rel_table),
				"icon" => Icon::get($rel_table),
			],
			"body" => $form->getHTML(),
	    ]);

	    return $card->getHTML();
	}

	public function new(array $a): string
	{
		extract($a);

		$buttons = ["save","cancel"];

		[$field_class, $method] = $this->getFieldClassAndMethod($rel_table);

		$form = new Form([
			"action" => "insert",
			"rel_table" => $rel_table,
			"callback" => $this->hash->getCallback(),
			"fields" => $field_class::{$method}(),
			"buttons" => $buttons,
			"modal" => true
		]);

		$card = new \App\UI\Card\Card([
			"header" => [
				"title" => str::title($rel_table),
				"icon" => Icon::get($rel_table),
			],
			"body" => $form->getHTML(),
		]);

		return $card->getHTML();
	}
}