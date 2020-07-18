<?php


namespace App\UI\Modal;

use App\Common\str;
use App\UI\Form\Form;
use App\UI\Icon;

/**
 * Class Common
 * Contains generic methods that are commonly used.
 *
 * @package App\UI\Modal
 */
abstract class Common extends \App\Common\Common {

	public function all(array $a, $size = "l"): string
	{
		extract($a);

		$modal = new \App\UI\Modal\Modal([
			"size" => $size,
			"icon" => Icon::get($rel_table),
			"header" => str::title("All ".str::pluralise($rel_table)),
			"body" => [
				"style" => [
					"overflow-y" => "auto",
					"overflow-x" => "hidden",
				],
				"id" => "all_{$rel_table}",
			],
			"footer" => [
				"button" => ["close_md",[
					"hash" => [
						"rel_table" => $rel_table,
						"action" => "new"
					],
					"title" => "New",
					"icon" => Icon::get("new"),
					"colour" => "primary",
				]]
			],
			"draggable" => true,
			"resizable" => true,
		]);

		return $modal->getHTML();

	}
	/**
	 * Generic new modal frame.
	 *
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

		[$field_class, $method] = $this->getFieldClassAndMethod($rel_table);

		$form = new Form([
			"action" => "insert",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $field_class::{$method}($a['vars']),
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

		$$rel_table = $this->info($rel_table, $rel_id);

		$buttons = ["save","cancel_md"];

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

	/**
	 * Finds the Field class and the relevant method for this rel_table.
	 * Checks if they exist. Will error out of they don't.
	 *
	 * @param $rel_table
	 *
	 * @return array
	 * @throws \ReflectionException
	 */
	private function getFieldClassAndMethod($rel_table): array
	{
		$current_class = get_class($this);
		$reflection_class = new \ReflectionClass($current_class);
		$namespace = $reflection_class->getNamespaceName();
		$field_class = $namespace."\\Field";
		$method = str::getMethodCase($rel_table);

		if(!class_exists($field_class)){
			throw new \Exception("A <code>Field()</code> class for the <b>{$rel_table}</b> table could not be found.");
		}

		if(!method_exists($field_class, $method)){
			throw new \Exception("A <code>{$method}</code> method could not be found in the <code>{$field_class}</code> class.");
		}

		return [$field_class, $method];
	}
}