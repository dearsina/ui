<?php

namespace App\UI\Form;

use App\Common\str;
use App\UI\Icon;
use App\UI\Tooltip;

class SelectOption {
	private array $option;
	private string $option_value;
	private bool $selected;

	public function __construct(string $option_value, array $option, bool $selected)
	{
		$this->option_value = $option_value;
		$this->option = $option;
		$this->selected = $selected;

		$this->format();
	}

	public function getOption(): array
	{
		return $this->option;
	}

	private function format(): void
	{
		$this->option['value'] = $this->option_value;
		$this->option['selected'] = $this->selected;

		# Format icon
		$icon = $this->formatIcon();

		# HTML, failing that, title
		if($this->option['html']){
			$html = $this->option['html'];
		}

		else {
			$html = $this->option['title'];
		}

		# Searchable, which will add text to the title
		$this->formatSearchable();
		// Append values to the global option-title, but not the local title value

		# Alt, class and style
		$alt = str::getAttrTag("title", $this->option['alt']);
		$class = str::getAttrTag("class", $this->option['class']);
		$style = str::getAttrTag("style", $this->option['style']);

		# Data
		$data = str::getDataAttr($this->option['data']);

		# Fatten with a tooltip if required
		Tooltip::generate($this->option);

		$this->option['data']['html'] = "<div{$alt}{$class}{$style}{$data}>{$icon}{$html}</div>";
	}

	private function formatSearchable(): void
	{
		if(!$this->option['searchable']){
			return;
		}

		# Get the searchable values
		$searchable = is_array($this->option['searchable']) ? $this->option['searchable'] : [$this->option['searchable']];

		# Clean up the searchable array
		$searchable_array = array_filter(array_unique(array_values(str::flatten($searchable))));

		if(!$searchable_array){
			return;
		}

		# Since we have searchable values, we can't rely on the system to create the alt
		if(!$this->option['alt']){
			if(!$this->option['tooltip']){
				// if there is no given alt or tooltip, set the untouched title as alt
				$this->option['alt'] = $this->option['title'];
			}
		}

		# Add searchable strings to the title (that will be hidden anyway)
		$this->option['title'] .= " | " . implode(" | ", $searchable_array);
	}

	private function formatIcon(): ?string
	{
		if(!is_array($this->option['icon'])){
			$this->option['icon'] = [
				"name" => $this->option['icon']
			];
		}

		if(!$this->option['icon']['style']){
			$this->option['icon']['style'] = "margin-right: 5px;";
		}

		return Icon::generate($this->option['icon']);
	}
}