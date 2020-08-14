<?php


namespace App\UI\Form;


use App\Common\str;

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

		# Class
		$class_array = str::getAttrArray($class, ["dropzone"], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Data (settings)
		$data = self::getSettings($a);

		return "<div{$id}{$data}{$class}{$style}></div>";
	}

	private static function getSettings(array &$a): ?string
	{
		if($a['placeholder']){
			$a['settings']['dictDefaultMessage'] = $a['placeholder'];
		}
		return str::getDataAttr(["settings" => $a['settings']]);
	}
}