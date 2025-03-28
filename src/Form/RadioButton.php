<?php

namespace App\UI\Form;

use App\ClientSignature\ClientSignature;
use App\Common\str;
use App\UI\Form\Field;
use App\UI\Form\FieldInterface;
use App\UI\Grid;
use App\UI\Icon;

class RadioButton extends Field implements FieldInterface {

	/**
	 * @inheritDoc
	 */
	public static function generateHTML(array $a)
	{
		extract($a);

		# Ensure the grand parent class is an array
		$grand_parent_class = is_array($grand_parent_class) ? $grand_parent_class : [$grand_parent_class];

		if($settings['centre']){
			$grand_parent_class[] = "text-center";
		}

		# Set dependency data
		self::setDependencyData($a);

		$grand_parent_class = self::getClass("mb-5", $grand_parent_class, $only_grand_parent_class);
		// 5 instead of 3 to give the buttons a bit more breathing room

		$buttons = self::getButtonGroup($a);

		# $data
		$data = self::getData($a);

		# Script (using $a because it may have changed in getSelectData())
		$script = str::getScriptTag($a['script']);

		return "
		<div{$grand_parent_class}{$data}>
			<div class=\"text-title\" style=\"width:max-content;line-height:unset;\">{$label['title']}</div>
			<div class=\"text-body\" style=\"margin-top:0;margin-bottom:2rem;\">{$label['desc']}</div>
			{$buttons}
		</div>
		{$script}
		";
	}

	public static function getButtonGroup(array $a): string
	{
		extract($a);
		$parent_class = self::getClass("button-radio btn-group", $parent_class, $only_parent_class);
		$role = str::getAttrTag("role", "group");
		$html = implode("", self::getInputButtons($a));
		return "<div{$parent_class}{$role}>{$html}</div>";
	}

	public static function getInputButtons(array $a): array
	{
		extract($a);

		$inputs = [];

		if(!$options){
			return $inputs;
		}

		foreach($options as $button){
			if(!is_array($button)){
				continue;
			}
			$inputs[] = self::getInputButton($a, $button);
		}

		return $inputs;

	}

	public static function getInputButton(array $a, array $button): string
	{
		extract($a);

		$class = is_array($class) ? $class : [$class];

		# Disabled
		if($settings['disabled']){
			$disabled = str::getAttrTag("disabled", "disabled");
			$class[] = "disabled";
		}

		# Validation
		self::setValidationData($a, $class);
		$data = self::getData($a);

		$type = str::getAttrTag("type", "radio");
		$class = self::getClass("btn-check", $class, $only_class);
		$name = str::getAttrTag("name", $name);
		$id = str::getAttrTag("id", $button['form_field_option_id']);
		$autocomplete = str::getAttrTag("autocomplete", "off");
		$checked = $value == $button['form_field_option_id'] ? " checked" : "";
		$value = str::getAttrTag("value", $button['form_field_option_id']);
		$label = self::getInputButtonLabel($button);

		return "<input{$id}{$type}{$class}{$name}{$value}{$autocomplete}{$checked}{$disabled}{$data}>{$label}";

	}

	private static function getInputButtonLabel(array $button): string
	{
		$for = str::getAttrTag("for", $button['form_field_option_id']);
		$class = self::getClass("btn btn-outline-{$button['colour']}");
		$icon = Icon::generate($button['icon']);
		return "<label{$class}{$for}>{$icon} {$button['value']}</label>";
	}
}