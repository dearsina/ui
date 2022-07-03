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
abstract class Prototype extends \App\Common\Prototype {

	public function all(array $a, string $size = "l"): string
	{
		extract($a);

		$buttons = $buttons ?: [
			[
				"hash" => [
					"rel_table" => $rel_table,
					"action" => "new",
					"vars" => $vars
				],
				"title" => "New",
				"icon" => Icon::get("new"),
				"colour" => "primary",
			],
			"close_md",
		];

		$modal = new Modal([
			"size" => $size,
			"icon" => Icon::get($rel_table),
			"header" => str::title("All " . str::pluralise($rel_table)),
			"body" => [
				"style" => [
					"overflow-y" => "auto",
					"overflow-x" => "hidden",
				],
				"id" => "all_{$rel_table}",
			],
			"footer" => [
				"button" => $buttons,
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
	public function new(array $a, string $size = "l"): string
	{
		extract($a);

		$buttons = $buttons ?: ["save", "cancel_md"];

		[$field_class, $method] = $this->getFieldClassAndMethod($rel_table);

		if(!$fields = $field_class::{$method}($a['vars'])){
			throw new \Exception("No form fields were found running the <code>{$field_class}::{$method}</code> class method.");
		}

		$form = new Form([
			"action" => "insert",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $fields,
			"buttons" => $buttons,
			"modal" => true,
		]);

		# A different modal for when tabs are being used
		if(array_key_exists("tabs", $fields)){
			$modal = new Modal([
				"id" => $id,
				"size" => $size,
				"body" => [
					"style" => [
						"background-color" => "#fcfdfd",
					],
					"html" => $form->getHTML(),
				],
				"approve" => "change",
				"draggable" => true,
				"resizable" => true,
			]);
		}

		else {
			$modal = new Modal([
				"id" => $id,
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
		}

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
	public function edit(array $a, string $size = "l"): string
	{
		extract($a);

		# Any vars sent with the edit AJAX request will be included
		$$rel_table = array_merge($vars ?: [], $this->info($rel_table, $rel_id));
		// But only if the value doesn't exist in the rel_table, otherwise it will be overwritten

		$buttons = $buttons ?: ["save", "cancel_md"];

		[$field_class, $method] = $this->getFieldClassAndMethod($rel_table);

		$fields = $field_class::{$method}($$rel_table);

		$form = new Form([
			"action" => "update",
			"rel_table" => $rel_table,
			"rel_id" => $rel_id,
			"callback" => $this->hash->getCallback(),
			"fields" => $fields,
			"buttons" => $buttons,
			"modal" => true,
		]);

		# A different modal for when tabs are being used
		if(array_key_exists("tabs", $fields)){
			$modal = new Modal([
				"id" => $id,
				"size" => $size,
				"body" => [
					"style" => [
						"background-color" => "#fcfdfd",
					],
					"html" => $form->getHTML(),
				],
				"approve" => "change",
				"draggable" => true,
				"resizable" => true,
			]);
		}

		else {
			$modal = new Modal([
				"id" => $id,
				"size" => $size,
				"icon" => Icon::get("edit"),
				"header" => str::title("Edit {$rel_table}"),
				"body" => $form->getHTML(),
				"approve" => "change",
				"draggable" => true,
				"resizable" => true,
			]);
		}

		return $modal->getHTML();
	}
}