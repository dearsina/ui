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
			"colour" => "red"
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
	 * @param null $array_or_string
	 *
	 * @return string
	 * @throws \Exception
	 */
	static function generate($array_or_string = null, ?array $overrides = NULL){
		if(!is_array($array_or_string) && !strlen($array_or_string)){
			return false;
		}

		if(!is_array($array_or_string)){
			//if the only thing passed is the name of a generic button
			if(!$a = Badge::GENERIC[$array_or_string]) {
				//if a generic version is NOT found
				$a['title'] = strtoupper($array_or_string);
			}
		} else if (str::isNumericArray($array_or_string)){
			//if there are more than one badge
			foreach($array_or_string as $badge){
				$badge_array[] = Badge::generate($badge);
			}
			return implode("&nbsp;",$badge_array);
		} else {
			$a = $array_or_string;
		}

		if(is_array($overrides)){
			$a = array_merge_recursive($a, $overrides);
		}

		# Give it an ID
		$a['id'] = $a['id'] ?: str::id("badge");
		//placed here because IDs are used by the get_approve_script method

		extract($a);

		$id = str::getAttrTag("id", $id);

		# Optional approval attributes
		$approve_attr = str::getApproveAttr($a['approve']);

		# Is the badge a link?
		if($href = href::generate($a)){
			//if the badge is to be a link
			$tag_type = "a";
		} else {
			$tag_type = "div";
			$style["cursor"] = "default";
		}

		# Is there a tag override?
		if($tag){
			$tag_type = $tag;
		}

		# Is the given colour a hex colour?
		if(str::isHexColour($colour)){
			$style["background-color"] = $colour;
		} else {
			$colour = $colour ?: "dark";
			//default is a b&w theme
		}

		if($icon = Icon::generate($icon)){
			$icon .= " ";
			//for better spacing between icon and title
		}

		$class = str::getAttrTag("class", [
			"badge",
			$pill ? "rounded-pill" : "", //pill shape
			$basic ? "badge-outline-{$colour}" : "bg-{$colour}",
			$right ? "float-right" : "", //legacy shortcut
			"text-white",
			$class,
			$approve_attr ? "approve-decision" : ""
		]);

		$style = str::getAttrTag("style", $style);
		$script = str::getScriptTag($script);

		$alt = $alt ? $alt : $desc;
		$title_attr = str::getAttrTag("title", strip_tags($alt ?: $title));

		return /** @lang HTML */<<<EOF
<{$tag_type}{$href}{$id}{$class}{$style}{$title_attr}{$approve_attr}>{$icon}{$title}</{$tag_type}>{$script}
EOF;
	}
}