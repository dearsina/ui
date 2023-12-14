<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;

/**
 * Class Badge
 * Use:
 * <code>
 * Badge::generate($a);
 * </code>
 * @package App\Common
 */
class Badge {
	/**
	 * Generic badges that can be referenced by name only.
	 * Badges can be localised by including "rel_table" or "rel_id".
	 *
	 */
	const GENERIC = [
		"deleted" => [
			"title" => "DELETED",
			"colour" => "red",
		],
	];

	/**
	 * Generates a badge based on an array of settings.
	 * <code>
	 * $html .= Badge::generate([
	 *    "hash" => "{$rel_table}/{$rel_id}",
	 *    "colour" => "grey",
	 *    "icon" => "chevron-left",
	 *    "title" => "Return",
	 *    "pill" => true,
	 *    "alt" => "Text appears when hover",
	 * ]);
	 * </code>
	 *
	 * Multiple badges can be built at once.
	 * <code>
	 * Badge::generate([$badgeA, $badgeB, ..., $badgeN]);
	 * </code>
	 *
	 * @param array|string|null $array_or_string
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function generate($array_or_string = NULL, ?array $overrides = NULL): ?string
	{
		if(!is_array($array_or_string) && !strlen($array_or_string)){
			return NULL;
		}

		if(!is_array($array_or_string)){
			//if the only thing passed is the name of a generic button
			if(!$a = Badge::GENERIC[$array_or_string]){
				//if a generic version is NOT found
				$a['title'] = strtoupper($array_or_string);
			}
		}

		else if(str::isNumericArray($array_or_string)){
			//if there are more than one badge
			foreach($array_or_string as $badge){
				$badge_array[] = Badge::generate($badge);
			}
			return implode("&nbsp;", array_filter($badge_array));
		}

		else {
			$a = $array_or_string;
		}

		if(is_array($overrides)){
			$a = array_merge_recursive($a ?:[], $overrides);
		}

		# Give it an ID
		$a['id'] = $a['id'] ?: str::id("badge");
		//placed here because IDs are used by the get_approve_script method

		# Generate any tooltips
		Tooltip::generate($a);

		extract($a);

		$id = str::getAttrTag("id", $id);

		# Optional approval attributes
		$approve_attr = str::getApproveAttr($a['approve'], $a['icon'], $a['colour']);

		# Is the badge a link?
		if($href = href::generate($a)){
			//if the badge is to be a link
			$tag_type = "a";
		}
		else {
			$tag_type = "div";
			$style["cursor"] = $style["cursor"] ?: "default";
		}

		# Is there a tag override?
		if($tag){
			$tag_type = $tag;
		}

		# Is the given colour a hex colour?
		if(str::isHexColour($colour)){
			$style["background-color"] = $colour;
		}
		else {
			$colour = $colour ?: "dark";
			//default is a b&w theme
		}

		if($icon = Icon::generate($icon)){
			$icon .= " ";
			//for better spacing between icon and title
		}

		$style_array = str::getAttrArray($style, NULL, $only_style);
		$class_array = str::getAttrArray($class, ["badge", "text-white"], $only_class);

		$class_array[] = $pill ? "rounded-pill" : ""; //pill shape
		$class_array[] = $basic ? "badge-outline-{$colour}" : "bg-{$colour}";
		$class_array[] = $right ? "float-right" : ""; // Legacy shortcut
		$class_array[] = $approve_attr ? "approve-decision" : ""; // Legacy shortcut
		$class_array[] = $tooltip ? "tooltip-trigger" : ""; // Legacy shortcut

		$style = str::getAttrTag("style", $style_array);
		$class = str::getAttrTag("class", $class_array);

		$script = str::getScriptTag($script);
		$data = str::getDataAttr($data);

		$alt = $alt ?: $desc;

		# Set the title, but only if there is no tooltip
		$title_attr = $tooltip ? NULL : str::getAttrTag("title", strip_tags($alt ?: $title));

		return /** @lang HTML */ <<<EOF
<{$tag_type}{$href}{$id}{$class}{$style}{$title_attr}{$approve_attr}{$data}>{$icon}{$title}</{$tag_type}>{$script}
EOF;
	}
}