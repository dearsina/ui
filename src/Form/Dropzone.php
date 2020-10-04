<?php


namespace App\UI\Form;


use App\Common\str;
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

		# Class
		$class_array = str::getAttrArray($class, ["dropzone"], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

        # Description
        $desc = self::getDesc($desc);

		# Data (settings)
		$data = self::getSettings($a);

		return "<div{$parent_id}><div{$id}{$data}{$class}{$style}></div>{$desc}</div>";
	}

	private static function getSettings(array &$a): ?string
	{
        # Icon
        if($a['icon'] !== false){
            //If an icon hasn't been explicitly refused

            $a['settings']['dictDefaultMessage'] .= "<div>".Icon::generate([
                    "type" => "thin",
                    "name" => $a['icon'] ?: "folder",
                    "size" => "3x",
                    "style" => [
                        "font-weight" => "100",
                        "font-size" => "50pt",
                        "color" => "#dce1e5"
                    ]
                ])."</div>";
        }

		if($a['placeholder']){
			$a['settings']['dictDefaultMessage'] .= $a['placeholder'];
		}
		return str::getDataAttr(["settings" => $a['settings']]);
	}
}