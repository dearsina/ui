<?php


namespace App\UI;


use App\Common\str;

class Copy {
	/**
	 * Returns copy button for the text asked to copy.
	 * Can take the following parameters:
	 * <code>
	 * Copy::generate([
	 * 	"id" => "",
	 * 	"style" => "",
	 * 	"class" => "",
	 * 	"text" => "",
	 * 	"alt" => ""
	 * ], $text);
	 * </code>
	 *
	 * Can also be boolean:
	 * <code>
	 * Copy::generate($copy, $text);
	 * </code>
	 *
	 * @param $a
	 * @param $text
	 *
	 * @return bool|string
	 */
	public static function generate($a, $text)
	{
		if(!$a){
			return false;
		}

		if(is_array($a)){
			extract($a);
		}

		# Clean up text (Escape double quotes)
		$text = str_replace("\"", "&quot;", $text);

		# Produce truncated version for display purposes
		$text_truncated = strlen($text) > 50 ? substr($text, 0, 50). "..." : $text;

		# ID (optional)
		$id = str::getAttrTag("id", $id);

		# Class
		$class_array = str::getAttrArray($class, "clipboard", $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Styles
		$style = str::getAttrTag("style", $style);

		# Title (alt)
		$alt = $alt ?: "Copy {$text_truncated} to clipboard";
		$alt = str::getAttrTag("title", $alt);

		# The text to copy
		$data['clipboard-text'] = $text;

		# Save the truncated text also for the alert (if needed)
		if($text != $text_truncated){
			$data['clipboard-text-truncated'] = $text;
		}

		# Create the data tag
		$data = str::getDataAttr($data);

		# Tag
		$tag = $tag ?: "span";

		return "<{$tag}{$id}{$class}{$style}{$data}{$alt}></{$tag}>";
	}
}