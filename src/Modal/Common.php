<?php


namespace App\UI\Modal;

use App\Common\str;
use App\UI\Form\Form;
use App\UI\Icon;

abstract class Common extends \App\Common\Common {
	/**
	 * @param array  $a
	 * @param string $size
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	public function new(array $a, $size = "l"): string
	{
		extract($a);

		$buttons = ["save","cancel_md"];

		$current_class = get_class($this);
		$reflection_class = new \ReflectionClass($current_class);
		$namespace = $reflection_class->getNamespaceName();
		$field_class = $namespace."\\Field";

		$form = new Form([
			"action" => "insert",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $field_class::{str::getMethodCase($rel_table)}($a['vars']),
			"buttons" => $buttons,
			"modal" => true
		]);

		$modal = new \App\UI\Modal\Modal([
			"size" => $size,
			"header" => [
				"icon" => Icon::get("new"),
				"title" => str::title("New {$rel_table}"),
			],
			"body" => $form->getHTML(),
			"approve" => "change",
			"draggable" => true,
			"resizable" => true,
		]);

		return $modal->getHTML();
	}

	/**
	 * Generic edit modal frame.
	 *
	 * @param        $a
	 * @param string $size
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	public function edit(array $a, $size = "l"): string
	{
		extract($a);

		$$rel_table = $this->sql->select([
			"table" => $rel_table,
			"id" => $rel_id
		]);

		$buttons = ["save","cancel_md"];

		$current_class = get_class($this);
		$reflection_class = new \ReflectionClass($current_class);
		$namespace = $reflection_class->getNamespaceName();
		$field_class = $namespace."\\Field";

		$form = new Form([
			"action" => "update",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $field_class::{str::getMethodCase($rel_table)}($$rel_table),
			"buttons" => $buttons,
			"modal" => true
		]);

		$modal = new \App\UI\Modal\Modal([
			"size" => $size,
			"icon" => Icon::get("edit"),
			"header" => str::title("Edit {$rel_table}"),
			"body" => $form->getHTML(),
			"approve" => "change",
			"draggable" => true,
			"resizable" => true,
		]);

		return $modal->getHTML();
	}
}