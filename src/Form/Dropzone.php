<?php


namespace App\UI\Form;


use App\Common\str;
use App\Language\Language;
use App\Translation\Translator;
use App\UI\Button;
use App\UI\Icon;

class Dropzone extends Field implements FieldInterface {

	/**
	 * The Dropzone.js object holder.
	 *
	 * All settings can be set via the settings key.
	 * Will include all form key-vals when uploading.
	 *
	 * @param array $a
	 *
	 * @return string Returns an HTML string.
	 */
	public static function generateHTML(array $a): string
	{
		extract($a);

		# ID
		$id = str::getAttrTag("id", $id ?: str::id("dropzone"));
		$parent_id = str::getAttrTag("id", $parent_id);

		# Label
		$label = self::getLabel($label, $title, $name, $id, $for);

		# Parent class
		$parent_class_array = str::getAttrArray($parent_class, "mb-3", $only_parent_class);
		$parent_class = str::getAttrTag("class", $parent_class_array);

		# Class
		$class_array = str::getAttrArray($class, ["dropzone"], $only_class);
		$class_array[] = Language::getDirectionClass($language_id);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Description
		if($desc = self::getDesc($desc)){
			$parent_style_array = str::getAttrArray($parent_style, ["height" => "fit-content"], $only_parent_style);
			$parent_style = str::getAttrTag("style", $parent_style_array);
		}

		# Set dependency data
		self::setDependencyData($a);

		# Set the dropzone settings
		self::setSettings($a);

		$data = str::getDataAttr($a['data']);

		return "<div{$parent_id}{$parent_class}{$parent_style}>{$label}<div{$id}{$data}{$class}{$style}></div>{$desc}</div>";
	}

	private static function setSettings(array &$a): void
	{
		# Copy any settings to the data array
		if($a['settings']){
			$a['data']['settings'] = $a['settings'];
		}

		# Icon
		if($a['icon'] !== false){
			//If an icon hasn't been explicitly refused

			$a['data']['settings']['dictDefaultMessage'] .= "<div>" . Icon::generate([
					"type" => "light",
					"name" => $a['icon'] ?: "cloud-arrow-up",
					"size" => "3x",
					"style" => [
						"font-weight" => "100",
						"font-size" => "50pt",
						"color" => "hsl(214deg 10% 89%)",
					],
				]) . "</div>";
		}

		if($a['placeholder']){
			$a['data']['settings']['dictDefaultMessage'] .= $a['placeholder'];
		}

		# Add a faux-browse button
		$faux_button = [
			"disabled" => true,
			"title" => "Browse...",
			"basic" => true,
			"size" => "s",
			"ladda" => false,
			"style" => [
				"cursor" => "default",
				"width" => "auto",
				"color" => "#a0a8b1",
				"border-color" => "#a0a8b1",
				"opacity" => "1",
			],
		];

		# A piece of text underneath the mean instructions
		$element = [
			"text" => "Alternatively, click browse to select a file.",
		];
		// Put in a separate element so that it can be translated if requierd

		# Translate if enabled
		if(class_exists("App\\Translation\\Translator")){
			if(!Translator::set($element, [
				"subscription_id" => $a['subscription_id'],
				"rel_table" => "text",
				"to_language_id" => $a['language_id'],
				"parent_rel_id" => $a['parent_rel_id']
			])){
				return;
			}

			Language::setLanguageKeys($faux_button, $a);
		}

		$a['data']['settings']['dictDefaultMessage'] .= "<div style=\"
		  font-weight   : 200;
		  display       : block;
		  font-size     : smaller;
		  margin-top    : 0.5rem;
		  margin-bottom : 0.5rem;
		\">{$element['text']}</div>";


		# Generate the button and set it
		$a['data']['settings']['dictDefaultMessage'] .= "<div>" . Button::generate($faux_button) . "</div>";
	}
}