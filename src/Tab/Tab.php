<?php

namespace App\UI\Tab;

use App\Common\Exception\BadRequest;
use App\Common\str;
use App\UI\Badge;
use App\UI\Card\Card;
use App\UI\Grid;
use App\UI\Icon;

class Tab {
	private ?object $formatter;

	/**
	 * @param object|null $formatter Tabs can have custom grid formatters
	 */
	public function __construct(?object $formatter = NULL)
	{
		$this->formatter = $formatter;
	}

	/**
	 * Puts together a comprehensive class array for the
	 * tab header button.
	 *
	 * @param array $header
	 *
	 * @return array
	 */
	private function getTabHeaderClass(array $header): array
	{
		extract($header);

		# Class
		$class_array = str::getAttrArray($class, ["nav-link"], $only_class);

		# Icon only tab
		if(!$title){
			$class_array[] = "icon-only";
		}
		/**
		 * If the tab title is just an icon, add the icon-only class.
		 * This further shrinks the real estate needed for the tab
		 * header.
		 */

		# Active
		if($active){
			$class_array[] = "active";
		}
		/**
		 * Is this tab active or not? One tab is always designated as
		 * active.
		 */

		# Disabled
		if($disabled){
			$class_array[] = "disabled";
		}
		/**
		 * If the tab is disabled on the tab level,
		 * the button will also be disabled.
		 */

		return $class_array;
	}

	private function getTabHeaderIcon(array $header): ?string
	{
		extract($header);

		if(!$icon){
			return NULL;
		}

		if(is_string($icon)){
			$icon = [
				"name" => $icon,
				"type" => "light",
			];
		}

		return Icon::generate($icon);
	}

	private function getTabHeaderData(array $tab, array $header): string
	{
		extract($header);

		$data['bs-toggle'] = "tab";
		$data['bs-target'] = "#{$tab['id']}";

		return str::getDataAttr($data);
	}

	public function getTabHeaderContent(array $header): string
	{
		extract($header);

		# Icon
		$icon = $this->getTabHeaderIcon($header);

		# Badge
		$badge = Badge::generate($badge);

		# Dismissible
		if($dismissible){
			$dismissible = Icon::generate([
				"name" => "times",
				"class" => "tab-close",
			]);
		}
		/**
		 * If the tab is dismissible, a cross is added as suffix to
		 * the header and the user is able to close the tab.
		 */

		return "{$icon} {$title}{$html}{$badge}{$dismissible}";
	}

	public function getTabHeaderHTML(array $tab): string
	{
		# Build the header array
		if(is_array($tab['header'])){
			$header = $tab['header'];
			unset($tab['header']);
		}

		else if($tab['header']){
			$header['title'] = $tab['header'];
			unset($tab['header']);
		}

		else if($tab['icon']){
			$header['icon'] = $tab['icon'];
		}

		else {
			throw new BadRequest("All tabs must have a header or an icon.");
		}

		# Merge all other keys from tab into header (but header keys override)
		$header = array_merge($tab, $header);

		# Extract the header
		extract($header);

		# ID
		$header_id = str::getAttrTag("id", "{$tab['id']}-header");
		$button_id = str::getAttrTag("id", "{$tab['id']}-button");
		/**
		 * A designated title ID (in the header array) is disregarded,
		 * instead the tab level ID is used with a suffix. Every tab
		 * must have an ID, if one hasn't been included, one will be
		 * generated.
		 */

		# Class
		$class = str::getAttrTag("class", $this->getTabHeaderClass($header));

		# Style
		$style = str::getAttrTag("style", $style);

		# Data
		$data = $this->getTabHeaderData($tab, $header);

		# Type and role
		$button_type = str::getAttrTag("type", "button");
		$button_role = str::getAttrTag("role", "tab");

		$li_class = str::getAttrTag("class", "nav-item");
		$li_role = str::getAttrTag("role", "presentation");

		return "
		<li{$li_class}{$li_role}{$header_id}>
			<button{$button_id}{$class}{$style}{$data}{$button_type}{$button_role}>
				{$this->getTabHeaderContent($header)}
			</button>
		</li>";
	}

	private function getTabPaneClass(array $tab): array
	{
		extract($tab);

		# Class
		$class_array = str::getAttrArray($class, ["tab-pane"], $only_class);

		# Active
		if($tab['active']){
			$class_array[] = "active";
		}
		/**
		 * Is this tab active or not? One tab is always designated as
		 * active.
		 */

		return $class_array;
	}

	/**
	 * Creates the tab body.
	 *
	 * @param string|array|null $body
	 *
	 * @return string|null
	 */
	private function getTabBodyHTML($body): ?string
	{
		if(!$body){
			return NULL;
		}

		if(!is_array($body)){
			$body = [
				"html" => $body,
			];
		}

		extract($body);

		# ID
		$id = str::getAttrTag("id", $id);

		# Class
		$classArray = str::getAttrArray($class, "body", $only_class);
		$class = str::getAttrTag("class", $classArray);

		# Style
		$style = str::getAttrTag("style", $style);

		# Data
		$data = str::getDataAttr($data);

		# Contents (if it's a grid)
		if(is_array($html)){
			$grid = new Grid(["formatter" => $this->formatter]);
			// Grids can have custom formatters
			$html = $grid->getHTML([$html]);
		}

		return "<div{$id}{$class}{$style}{$data}>{$html}</div>";
	}

	/**
	 * Formats and return the footer of a single tab.
	 *
	 * @param array $tab
	 *
	 * @return string|null
	 */
	private function getTabFooterHTML(?array $footer): ?string
	{
		return (new Card())->getFooterHTML($footer);
		//We're borrowing from the Card class
	}

	public function getTabPaneHTML(array $tab): string
	{

		# ID
		$id = str::getAttrTag("id", $tab['id']);

		# Class
		$class = str::getAttrTag("class", $this->getTabPaneClass($tab));

		# Role
		$role = str::getAttrTag("role", "tabpanel");

		# Style
		$style = str::getAttrTag("style", $tab['style']);

		# Data
		$data = str::getDataAttr($tab['data'], true);

		return "
		<div{$id}{$class}{$style}{$data}{$role}>
			{$this->getTabBodyHTML($tab['body'])}
			{$this->getTabFooterHTML($tab['footer'])}
		</div>
		";
	}

	public function getTabsHTML(array $tabs): ?string
	{
		if(!$tabs['tabs'] && str::isNumericArray($tabs)){
			$tabs['tabs'] = $tabs;
		}

		if($tabs['tabs']){

			# Remove empty tabs
			if(!$tabs['tabs'] = array_filter($tabs['tabs'])){
				//if there are no real tabs left
				return NULL;
			}
			# Ensure the numerical array starts from zero
			$tabs['tabs'] = array_values($tabs['tabs']);

			# Ensure at least one tab is active

			if(!array_filter($tabs['tabs'], function($tab){
				return $tab['active'];
			})){
				//if none of the tabs are set to be active

				# Set the first tab to be active by default
				$tabs['tabs'][0]['active'] = true;
			}
		}

		if($tabs['vertical']){
			return $this->getVerticalTabsHTML($tabs);
		}

		else return $this->getHorizontalTabsHTML($tabs);
	}

	private function getHorizontalTabsHTML(array $tabs): string
	{
		$headers = [];
		$panes = [];

		if($tabs['tabs']){
			foreach($tabs['tabs'] as $tab){
				$tab['id'] = $tab['id'] ?: str::id("tab");
				$headers[] = $this->getTabHeaderHTML($tab);
				$panes[] = $this->getTabPaneHTML($tab);
			}
		}

		# ID
		$id = str::getAttrTag("id", $tabs['id'] ?: str::id("tab"));

		# Tab class
		$class_array = str::getAttrArray($tabs['class'], ["nav", "nav-tabs", "nav-tabs-horizontal"], $tabs['only_class']);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $tabs['style']);

		# Role
		$role = str::getAttrTag("role", "tablist");

		# Tab content class
		$content_class_array = str::getAttrArray($tabs['content_class'], ["tab-content"], $tabs['content_only_class']);
		$content_class = str::getAttrTag("class", $content_class_array);

		# Tab content style
		$content_style = str::getAttrTag("style", $tabs['content_style']);

		# The tab navigation
		$nav_tabs_html = implode("\r\n", $headers);

		# The tab panes (data)
		$tab_panes = implode("\r\n", $panes);

		return "
		<ul{$id}{$class}{$style}{$role}>{$nav_tabs_html}</ul>
		<div{$content_class}{$content_style}>{$tab_panes}</div>
		";
	}

	private function getVerticalTabsHTML(array $tabs): string
	{
		foreach($tabs['tabs'] as &$tab){
			$tab['id'] = $tab['id'] ?: str::id("panel");
			$tab['active'] = $tab['active'] ? "active" : NULL;
		}

		return <<<EOF
<div class="vertical-tabs">
	<div class="vertical-tabs-col vertical-tabs-icons">
		{$this->getVerticalTabsListHTML($tabs)}
	</div>
	<div class="vertical-tabs-col vertical-tabs-content">
		{$this->getVerticalTabsContentHTML($tabs)}
	</div>
</div>
EOF;

	}

	private function getVerticalTabsContentHTML(array $tabs): string
	{
		foreach($tabs['tabs'] as $tab){
			$li .= <<<EOF
<div id="{$tab['id']}"
class="tab-pane show  {$tab['active']} accordion-item"
role="tabpanel"
>
	{$this->getTabBodyHTML($tab)}
	{$this->getTabFooterHTML($tab)}
</div>
EOF;
		}

		return <<<EOF
<div id="accordion-left-tabs" class="tab-content accordion">
	{$li}
</div>
EOF;
	}

	private function getVerticalTabsListHeaderHTML(array $tab): string
	{
		if(is_array($tab['header'])){
			extract($tab['header']);
		}
		else if($tab['header']){
			$title = $tab['header'];
		}
		else if(!$tab['icon']){
			throw new BadRequest("All tabs must have a header or an icon.");
		}

		# Id
		$id = str::getAttrTag("id", "{$tab['id']}-tab");
		//A designated title ID (in the title array) is disregarded

		# Disabled
		if($tab['disabled']){
			$disabled = "disabled";
		}

		else {
			$disabled = "tab-clickable";
		}

		# Class
		$class_array = str::getAttrArray($class, ["nav-link", $tab['active'], $disabled], $only_class);

		# If the tab title is just an icon, add the icon-only class
		if(!$title){
			$class_array[] = "icon-only";
		}

		$class = str::getAttrTag("class", $class_array);

		# Icon
		$icon = $icon ?: $tab['icon'];
		if($icon){
			if(is_string($icon)){
				$icon = [
					"name" => $icon,
					"type" => "light",
				];
			}
			$icon = Icon::generate($icon);
		}

		# Badge
		$badge = Badge::generate($badge ?: $tab['badge']);

		# Style
		$style = str::getAttrTag("style", $style);

		return <<<EOF
<div
	{$id}
	{$class}
	{$style}
	role="tab"
	data-bs-toggle="tab"
	data-bs-target="#{$tab['id']}"
	aria-controls="{$tab['id']}"
	aria-selected="true"
>
	{$icon}
	{$title}
	{$html}
	{$badge}
</div>
EOF;
	}

	private function getVerticalTabsListHTML(array $tabs): string
	{
		foreach($tabs['tabs'] as $tab){
			$li .= <<<EOF
<li class="nav-item" role="presentation">
  {$this->getVerticalTabsListHeaderHTML($tab)}
</li>
EOF;
		}

		return <<<EOF
<ul class="nav nav-tabs left-tabs" role="tablist">
	{$li}
</ul>
EOF;

	}
}