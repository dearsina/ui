<?php

namespace App\UI\Tab;

use App\Common\str;

/**
 * A shortcut method to the Tab() class.
 */
class Tabs {
	/**
	 * Generate tabs HTML.
	 *
	 * @param array|null  $tabs
	 * @param object|null $formatter If a customer formatter is to be used (used when tabs have forms),
	 *
	 * @return string|null
	 */
	public static function generate(?array $tabs = NULL, ?object $formatter = NULL): ?string
	{
		$tab = new Tab($formatter);
		return $tab->getTabsHTML($tabs);
	}

	public static function generateTab(?array &$data = NULL): ?array
	{
		# Ensure there is an ID
		$data['id'] = $data['id'] ?: str::id("tab");

		$tab = new Tab();

		return [
			$tab->getTabHeaderHTML($data),
			$tab->getTabPaneHTML($data)
		];
	}

	public static function generateHeaderHTML(?array $data = NULL): ?string
	{
		$tab = new Tab();
		return $tab->getTabHeaderHTML($data);
	}

	public static function generatePaneHTML(?array $data = NULL): ?string
	{
		$tab = new Tab();
		return $tab->getTabPaneHTML($data);
	}
}