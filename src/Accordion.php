<?php


namespace App\UI;


use App\Common\str;
use Exception;

/**
 * Class Accordion
 *
 * Generate an accordion piece, with one or many collapsable elements.
 *
 * @package App\UI
 */
class Accordion {
	/**
	 * Expects either an array with a `header` and a `body`,
	 * or an array of arrays containing those elements.
	 * Both the `header` and the `body` can be either strings
	 * or arrays or attributes (id, class, style, etc).
	 *
	 * <code>
	 * Accordion::generate([
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ]);
	 *
	 * Accordion::generate([[
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ],[
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ]]);
	 * </code>
	 *
	 * @param array|null $a
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public static function generate(?array $a){
		if(!is_array($a)){
			return false;
		}

		if(!str::isNumericArray($a)){
			//if there is only one accordion pair
			return self::generateCollapsable($a);
		}

		# This is the accordion wrapper ID
		$id = str::id("accordion");

		foreach($a as $collapsable){
			$html .= self::generateCollapsable($collapsable, $id);
		}

		# This is the wrapper class
		$class = str::getAttrTag("class", "accordion");

		$id = str::getAttrTag("id", $id);

		return "<div{$id}{$class}>{$html}</div>";
	}

	/**
	 * Generate one accordion element with a header and a body.
	 *
	 * @param array       $a
	 * @param string|null $parent_id
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function generateCollapsable(array $a, ?string $parent_id = NULL) : string
	{
		extract($a);

		# This ID ties the two pieces together
		$id = $id ?: str::id("collapse");
		
		$html .= self::generateHeaderHTML($header, $id);
		$html .= self::generateBodyHTML($body, $id, $parent_id);

		return $html;
	}

	/**
	 * The header of the accordion element.
	 *
	 * @param array|string $a
	 * @param string $data_target_id The ID of the element to toggle.
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function generateHeaderHTML($a, string $data_target_id) : string
	{
		if(!$a){
			throw new Exception("A accordion item must have a title of some sort.");
		}
		$a = is_array($a) ? $a : ["title" => $a];
		extract($a);

		# All three words are valid
		$title = $title.$header.$html;
		//TODO Bring together so only one word (header?) is used

		$id = str::getAttrTag("id", $id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "collapse-toggle collapsed", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);
		
		return <<<EOF
<div
	{$id}
	{$class}
	{$style}
	data-bs-toggle="collapse"
	data-bs-target="#{$data_target_id}"
	aria-expanded="false"
	aria-controls="{$data_target_id}"
>
	{$icon}
	{$title}
	{$badge}
	{$button}
</div>
EOF;
	}

	/**
	 * The body of an accordion element.
	 *
	 * @param array|string $a
	 * @param string       $data_target_id This element's ID.
	 * @param string|null  $data_parent_id The accordion parent's ID that this collapsable belongs to.
	 *
	 * @return string
	 * @throws Exception
	 * @throws Exception
	 */
	private static function generateBodyHTML($a, string $data_target_id, ?string $data_parent_id) : string
	{
		$a = is_array($a) ? $a : ["html" => $a];
		extract($a);

		$id = str::getAttrTag("id", $data_target_id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "collapse", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);

		$data_parent = str::getAttrTag("data-parent", $data_parent_id ? "#{$data_parent_id}" : false);

		return "<div{$id}{$class}{$style}{$data_parent}>{$icon}{$html}{$badge}{$button}</div>";
	}
}