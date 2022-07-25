<?php


namespace App\UI;


use App\Common\str;

/**
 * Class Copy
 * @package App\UI
 */
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
	 * 	"alt" => "",
	 * 	"secret" => ""//, "String replacement" or boolean TRUE
	 * ]);
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
	public static function generate($a, ?string $text = NULL): ?string
	{
		if(!$a){
			return NULL;
		}

		if(is_array($a)){
			# Clean up text (Escape double quotes)
			self::cleanUpText($a);
			extract($a);
		}

		$text_truncated = self::getTruncatedText($copy);

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
			$data['clipboard-text-truncated'] = $text_truncated;
		}

		# Create the data tag
		$data = str::getDataAttr($data);

		# Tag
		$tag = $tag ?: "span";

		return "<{$tag}{$id}{$class}{$style}{$data}{$alt}></{$tag}>";
	}

	private static function cleanUpText(array &$copy): void
	{
		$copy['text'] = str_replace("\"", "&quot;", $copy['text']);
	}

	/**
	 * Returns the truncated text that will be displayed
	 * in the alert to the user.
	 *
	 * @param array $copy
	 *
	 * @return string
	 */
	private static function getTruncatedText(array $copy): string
	{
		extract($copy);

		# Secret?
		if($secret){
			# If the string is to stay secret, replace it with the $secret text (or asterisks if no text is given)
			return is_scalar($secret) ? $secret : "***";
		}

		# Produce truncated version for display purposes
		return strlen($text) > 50 ? substr($text, 0, 50). "..." : $text;
	}

	public static function generateButton(array &$a): void
	{
		extract($a);

		switch(true){
		case is_scalar($copy):
			$copy = ["text" => $copy];
			break;
		case is_bool($copy):
			$copy = ["text" => str::useFirst([$title, $alt, $html])];
			break;
		default:
			return;
		}

		# if the text is an array, convert it to JSON
		if(is_array($copy['text'])){
			$copy['text'] = json_encode($copy['text']);
		}

		self::cleanUpText($copy);
		$a['data']['clipboard-text'] = $copy['text'];
		$a['data']['clipboard-text-truncated'] = self::getTruncatedText($copy);

		# Add the clipboard class
		$a['class'] = is_array($a['class']) ? $a['class'] : [$a['class']];
		$a['class'][] = "clipboard";

		# Disable ladda
		$a['ladda'] = false;

	}
}