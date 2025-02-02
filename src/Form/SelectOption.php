<?php

namespace App\UI\Form;

use App\Common\str;
use App\UI\Icon;
use App\UI\Tooltip;

class SelectOption {
	public static function getFormattedOption(array $option): array
	{
		# Format icon
		$icon = SelectOption::formatIcon($option);

		# Searchable, which will add text to the title
		SelectOption::formatSearchable($option);
		// Append values to the global option-title, but not the local title value

		# Alt, class and style
		$alt = str::getAttrTag("title", $option['alt']);
		$class = str::getAttrTag("class", $option['class']);
		$style = str::getAttrTag("style", $option['style']);

		# Data
		$data = str::getDataAttr($option['data']);

		# Fatten with a tooltip if required
		Tooltip::generate($option);

		# HTML, failing that, title
		if($html = $option['html']){
			$option['data']['html'] = $html;
			return $option;
		}

		$html = $option['title'];
		$option['html'] = "<div{$alt}{$class}{$style}{$data}>{$icon}{$html}</div>";
		$option['data']['html'] = $option['html'];

		return $option;
	}


	private static function formatSearchable(array &$option): void
	{
		if(!$option['searchable']){
			return;
		}

		# Get the searchable values
		$searchable = is_array($option['searchable']) ? $option['searchable'] : [$option['searchable']];

		# Clean up the searchable array
		$searchable_array = array_filter(array_unique(array_values(str::flatten($searchable))));

		if(!$searchable_array){
			return;
		}

		# Since we have searchable values, we can't rely on the system to create the alt
		if(!$option['alt']){
			if(!$option['tooltip']){
				// if there is no given alt or tooltip, set the untouched title as alt
				$option['alt'] = $option['title'];
			}
		}

		# Add searchable strings to the title (that will be hidden anyway)
		$option['title'] .= " | " . implode(" | ", $searchable_array);
	}

	private static function formatIcon(array $option): ?string
	{
		if(!is_array($option['icon'])){
			$option['icon'] = [
				"name" => $option['icon']
			];
		}

		return Icon::generate($option['icon']);
	}
}