<?php


namespace App\UI;

use App\Common\href;
use App\Common\str;
use App\UI\Form\Field;
use App\UI\Tab\Tabs;
use Stringable;

/**
 * Class Grid
 * @package App\UI
 */
class Grid {
	public const FLOW_COLUMN = "column";
	public const FLOW_ROW = "row";

	private $grid;
	private $unstackable;
	private ?object $formatter;

	/**
	 * Format and return content in a HTML grid.
	 * <code>
	 * $grid = new Grid([
	 *    "unstackable" => NULL, //If set to TRUE will make the grid unstackable on small screens
	 *    "formatter" => NULL //If set to an anonymous function, will use that function to format the HTML per cell.
	 * ]);
	 * </code>
	 *
	 * @param null $a
	 */
	public function __construct($a = NULL)
	{
		$this->unstackable = $a['unstackable'];
		$this->formatter = $a['formatter'];
	}

	/**
	 * Add one or many grid cells.
	 * Cells can be infinately nested.
	 *
	 * Skip a column by entering an empty (no html key) cell.
	 *
	 * <code>
	 * $grid = new Grid();
	 * $grid->set([
	 *    "sm" => "",
	 *    "id" => "",
	 *    "html" => ""
	 * ]);
	 * </code>
	 *
	 * @param $a
	 *
	 * @return bool
	 */
	public function set($a)
	{
		if(!$a){
			return false;
		}

		$this->grid[] = $a;
		return true;
	}

	/**
	 * Formats and returns the HTML of one row (with one or many cells).
	 * To format (id/class/style) a row, prefix the attr with "row_".
	 *
	 * @param array $rows
	 *
	 * @return null|string
	 */
	private function getAllRowsHtml(array $rows): ?string
	{
		foreach($rows as $row){
			if(!is_array($row)){
				$row_html = $this->getAllColumnCellsForOneRowHtml(["html" => $row]);
				$row = [];
			}

			else if(str::isNumericArray($row)){
				$row_html = $this->getAllColumnCellsForOneRowHtml($row);
			}

			else if(str::isNumericArray($row['html'])){
				$row_html = $this->getAllColumnCellsForOneRowHtml($row['html']);
			}

			else if($row['html'] && $row['type'] != "html"){
				$row_html = $this->getAllColumnCellsForOneRowHtml([$row['html']]);
			}

			else {
				$row_html = $this->getAllColumnCellsForOneRowHtml([$row]);
			}

			# ID
			$id_tag = str::getAttrTag("id", $row['row_id']);

			# Tooltip
			$this->addTooltipToRow($row);

			# Class
			$class_array = str::getAttrArray($row['row_class'], "row", $row['only_row_class']);

			# Splits
			if($row['split']){
				$class_array[] = "col-split";
			}

			# Class tag
			$class_tag = str::getAttrTag("class", $class_array);

			# Style
			$style_tag = str::getAttrTag("style", $row['row_style']);

			# Row title
			$title = str::getAttrTag("title", $row['row_alt']);

			# Data
			$data = str::getDataAttr($row['row_data']);

			$html .= "<div{$id_tag}{$class_tag}{$style_tag}{$data}{$title}>{$row_html}</div>";
		}

		return $html;
	}

	/**
	 * Adds a tooltip to a row.
	 *
	 * Will need the tooltip to be in the row_tooltip key.
	 *
	 * Requires a workaround to avoid duplication,
	 * and to ensure the tooltip is added to the
	 * right (row_ prefixed) keys.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	private function addTooltipToRow(array &$row): void
	{
		if(!$row['row_tooltip']){
			return;
		}

		# Define the row tooltip
		$a = $row;
		// We copy the row array to a new variable

		# Convert the row tooltip to a tooltip
		$a['tooltip'] = $a['row_tooltip'];

		$a['class'] = [];
		$a['data'] = [];
		// We remove class and data from the array to avoid duplication

		# Then we feed that variable to the tooltip generator
		Tooltip::generate($a);

		# Then we merge any classes with the class array before generating the class tag
		if($a['class']){
			if(is_array($row['row_class'])){
				$row['row_class'] = array_merge($row['row_class'], $a['class']);
			}
			else if($row['row_class']){
				$row['row_class'] = array_merge([$row['row_class']], $a['class']);
			}
			else {
				$row['row_class'] = $a['class'];
			}
		}

		# And we do the same with the data array
		if($a['data']){
			$row['row_data'] = array_merge($row['row_data'] ?: [], $a['data']);
		}
	}

	/**
	 * Get the HTML for a single cell.
	 * Does not include badges.
	 *
	 * @param array|string|null $cell
	 *
	 * @return string
	 */
	private function getColumnCellHtml(&$cell): ?string
	{
		//If the array cell is empty, return NULL
		if(is_array($cell)){
			if(empty($cell)){
				$cell = [];
				return NULL;
			}
		}

		//If the NON-array cell is empty, return NULL
		else if($cell === NULL){
			// Here, empty is === NULL, not "" or 0
			$cell = [];
			return NULL;
		}

		# If the cell is not an array, assume the whole thing is the cell HTML
		if(!is_array($cell)){
			$col_html = $cell;

			# Set the original cell as an (empty) array
			$cell = [];

			return $col_html;
		}

		# If the entire cell is a numerical array, assume it's a whole row
		if(str::isNumericArray($cell)){
			# Return the whole row as HTML
			return $this->getAllRowsHtml($cell);
		}

		# If the HTML key is a numerical array, assume it's a whole row
		if(str::isNumericArray($cell['html'])){
			# Return the HTML of the row
			return $this->getAllRowsHtml($cell['html']);
		}

		# If we're dealing with tabs, generate them, with the optional formatter
		if(is_array($cell['tabs']) && array_filter($cell['tabs'])){
			return Tabs::generate($cell['tabs'], $this->formatter);
		}

		# If we have a formatter, use it to format the cell
		if($this->formatter){
			//If a custom formatter has been designated
			return ($this->formatter)($cell);
		}

		# If the cell is an accordion, generate and return it
		if($cell['accordion']){
			return Accordion::generate($cell['accordion']);
		}

		# If the cell has a checkbox, generate and return it
		if($cell['checkbox']){
			# Get the name, and ensure it ends with []
			$name = $cell['checkbox']['name'];
			if(substr($name, -2) != "[]"){
				$name .= "[]";
			}

			# If the cell has a title and/or body, use them as the label
			if($cell['title'] || $cell['body']){
				$label = [
					"icon" => $cell['icon'],
					"title" => self::generateTitle($cell['title']),
					"desc" => self::generateBody($cell['body']),
				];
				unset($cell['icon']);
			}

			# Otherwise, use the HTML as the label
			else {
				$label = $cell['html'];
			}

			# We have to unset any potential hashes, or else the UX will be confusing
			unset($cell['hash']);

			# Generate the field
			$field = [
				"type" => "checkbox",
				"name" => $name,
				"value" => $cell['checkbox']['value'],
				"placeholder" => false,
				"label" => $label,
				"style" => $cell['checkbox']['style'],
				"parent_style" => $cell['checkbox']['parent_style'],
				"only_grand_parent_class" => "mb",
			];

			# Return just the field as HTML
			return Field::getHTML($field);
		}

		# Finally, if none of the above, generate the title, body, and HTML
		$col_html = self::generateTitle($cell['title']);
		$col_html .= self::generateBody($cell['body']);
		$col_html .= $cell['html'];

		# Return the HTML
		return $col_html;
	}

	/**
	 * Formats and returns the HTML the entire row with all its columns.
	 *
	 * @param array $cols
	 *
	 * @return string
	 */
	private function getAllColumnCellsForOneRowHtml(array $cols): string
	{
		foreach($cols as $col){
			# Get the column cell HTML
			$col_html = $this->getColumnCellHtml($col);

			# If they're not to be stacked, make it so (default is stackable)
			$col_width = $this->unstackable ? "col" : "col-sm";

			# Buttons
			if($buttons = Button::get($col)){
				$col_width = "col-sm";
			}

			# Has a fixed column width been requested
			if($col['sm']){
				$col_width .= "-{$col['sm']}";
			}

			# ID
			$id_tag = str::getAttrTag("id", $col['id'] ?: $col['col_id']);

			# Icon
			$icon = Icon::generate($col['icon']);

			# Alt (title)
			$title = str::getAttrTag("title", $col['alt']);

			# Copy
			$copy = Copy::generate($col['copy'], $col_html);

			# Badges
			$col_html .= Badge::generate($col['badge']);

			# Tooltip
			Tooltip::generate($col);

			# Class
			$class_array = str::getAttrArray($col['class'], $col_width, $col['only_class']);
			$class_tag = str::getAttrTag("class", $class_array);

			# Styles
			$style_tag = str::getAttrTag("style", $col['style']);

			# Data value (used for sorting)
			$data_value = $this->getDataValueTag($col);
			$data = str::getDataAttr($col['data'], true);

			# Hash, URI, onClick
			if($href = href::generate($col)){
				$tag = "a";
				if($buttons){
					//If both hash and a button are found in the same cell
					$html .= "<div{$class_tag}{$id_tag}{$style_tag}{$data_value}{$data}{$title}>{$buttons}<a{$href}>{$icon}{$col_html}{$copy}</a></div>";
					//Breaks down the tag into two different tags, one remains a div with all the attributes, the other a child a tag with only the href attr
					continue;
				}

			}
			else {
				$tag = "div";
			}

			$html .= "<{$tag}{$href}{$id_tag}{$class_tag}{$style_tag}{$data_value}{$data}{$title}>{$icon}{$buttons}{$col_html}{$copy}</{$tag}>";
		}

		return $html;
	}

	/**
	 * The data-value value is used to identify changes in
	 * modal forms, and warn the user if there has been a change
	 * that hasn't been saved.
	 *
	 * @param array $col
	 *
	 * @return string|null
	 */
	public function getDataValueTag(array $col): ?string
	{
		if($col['type'] == "checkbox" && array_key_exists("checked", $col) && !$col['checked']){
			//if this is a checkbox and it's not checked
			return NULL;
		}

		return str::getDataAttr(["value" => $col['value']]);
	}

	/**
	 * Returns formatted HTML of the requested grid.
	 *
	 * @param string|array|null $a
	 *
	 * @return bool|string
	 */
	public function getHTML($a = NULL): ?string
	{
		$grid = $a ?: $this->grid;

		if(!$grid){
			return NULL;
		}

		return $this->getAllRowsHtml($grid);
	}

	/**
	 * Static method to quickly return a grid.
	 *
	 * @param array $cells An array of grid cells
	 *
	 * @return string Returns HTML
	 */
	public static function generate(array $cells, ?object $formatter = NULL): ?string
	{
		$grid = new Grid([
			"formatter" => $formatter,
		]);
		return $grid->getAllRowsHtml($cells);
	}

	/**
	 * Render arbitrary UI fragments in a modern responsive CSS Grid.
	 *
	 * This API is independent of the legacy Bootstrap row/column methods in this
	 * class. It is intended for reusable component composition: cards, panels,
	 * data points, media, forms, or any other rendered HTML may be supplied.
	 *
	 * Each item may be a trusted HTML string, scalar, Stringable object, nested
	 * array of fragments, or an associative definition containing `html` plus
	 * item settings. Item definitions support `span` (`all` or 1-12), `stretch`,
	 * and common element settings such as class, style, data, ARIA, and custom
	 * attributes.
	 *
	 * Layout settings:
	 * - `columns`: Column count from 1-12. Defaults to 2.
	 * - `flow`: `row` (default) or `column`. Column-major flow calculates the
	 *   required row count while retaining supplied DOM order.
	 * - `gap`: `none`, `xs`, `sm`, `md`, `lg`, or `xl`. A raw CSS length is also
	 *   accepted through a custom property. Defaults to `md`.
	 * - `collapse_at`: `sm`, `md`, `lg`, `xl`, `xxl`, or FALSE. The grid becomes
	 *   one column at and below that breakpoint. Defaults to `md`.
	 * - `responsive`: Backwards-friendly boolean alias; FALSE disables collapse.
	 * - `align`: `start`, `center`, `end`, or `stretch`.
	 * - `stretch`: Makes every item's direct child fill its grid area.
	 * - Standard `tag`, `id`, `class`, `only_class`, `style`, `only_style`,
	 *   `data`, `alt`, `tooltip`, `role`, `tabindex`, `rtl`, `aria`, and custom
	 *   `attributes` settings.
	 *
	 * Example:
	 * <code>
	 * Grid::responsive([
	 *     ["html" => $overview, "span" => "all"],
	 *     $left_panel,
	 *     $right_panel,
	 * ], [
	 *     "columns" => 2,
	 *     "gap" => "lg",
	 *     "collapse_at" => "lg",
	 *     "stretch" => TRUE,
	 * ]);
	 * </code>
	 *
	 * @param array<int,mixed>    $items    Rendered fragments or item definitions.
	 * @param array<string,mixed> $settings Layout and root element settings.
	 *
	 * @return string Responsive grid HTML, or an empty string when no items render.
	 */
	public static function responsive(array $items, array $settings = []): string
	{
		$rendered = [];
		foreach($items as $item){
			$html = self::responsiveItem($item, (bool)($settings['stretch'] ?? false));
			if($html !== ""){
				$rendered[] = $html;
			}
		}

		if(!$rendered){
			return "";
		}

		$columns = max(1, min(12, (int)($settings['columns'] ?? 2)));
		$flow = strtolower((string)($settings['flow'] ?? self::FLOW_ROW));
		$flow = $flow === self::FLOW_COLUMN ? self::FLOW_COLUMN : self::FLOW_ROW;
		$rows = max(1, (int)ceil(count($rendered) / $columns));
		$align = self::responsiveModifier(
			(string)($settings['align'] ?? (($settings['stretch'] ?? false) ? "stretch" : "start")),
			["start", "center", "end", "stretch"],
			"start"
		);
		$gap = strtolower(trim((string)($settings['gap'] ?? "md")));
		$gap_class = in_array($gap, ["none", "xs", "sm", "md", "lg", "xl"], true) ? $gap : "custom";

		$responsive = !array_key_exists('responsive', $settings) || (bool)$settings['responsive'];
		$collapse_at = $responsive ? strtolower((string)($settings['collapse_at'] ?? "md")) : "";
		$collapse_at = in_array($collapse_at, ["sm", "md", "lg", "xl", "xxl"], true) ? $collapse_at : "";

		$classes = [
			"ui-responsive-grid",
			"ui-responsive-grid--columns-{$columns}",
			"ui-responsive-grid--{$flow}-major",
			"ui-responsive-grid--gap-{$gap_class}",
			"ui-responsive-grid--align-{$align}",
			$collapse_at ? "ui-responsive-grid--collapse-{$collapse_at}" : NULL,
		];
		$styles = $flow === self::FLOW_COLUMN ? ["--ui-responsive-grid-rows" => $rows] : [];
		if($gap_class === "custom"){
			$styles['--ui-responsive-grid-gap'] = $gap ?: ".75rem";
		}

		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$tag = self::responsiveTag((string)($settings['tag'] ?? "div"), "div");
		$attributes = self::responsiveAttributes($settings, $classes, $styles, true);

		return "<{$tag}{$attributes}>" . implode("", $rendered) . "</{$tag}>";
	}

	/** Render one responsive-grid item and its layout wrapper. */
	private static function responsiveItem(mixed $item, bool $default_stretch): string
	{
		$settings = [];
		if(is_array($item) && !str::isNumericArray($item) && array_key_exists('html', $item)){
			$settings = $item;
			$item = $settings['html'];
			unset($settings['html']);
		}

		$html = self::responsiveContent($item);
		if($html === ""){
			return "";
		}

		$span = strtolower(trim((string)($settings['span'] ?? "1")));
		$span = $span === "all" ? "all" : (string)max(1, min(12, (int)$span));
		$classes = [
			"ui-responsive-grid__item",
			"ui-responsive-grid__item--span-{$span}",
			($settings['stretch'] ?? $default_stretch) ? "ui-responsive-grid__item--stretch" : NULL,
		];
		$tag = self::responsiveTag((string)($settings['tag'] ?? "div"), "div");
		$attributes = self::responsiveAttributes($settings, $classes);

		return "<{$tag}{$attributes}>{$html}</{$tag}>";
	}

	/** Flatten trusted rendered fragments for a grid item. */
	private static function responsiveContent(mixed $content): string
	{
		if($content === NULL || $content === false){
			return "";
		}
		if(is_array($content)){
			return implode("", array_map([self::class, 'responsiveContent'], $content));
		}
		if(is_string($content) || is_numeric($content)){
			return (string)$content;
		}
		if($content === true){
			return "true";
		}

		return $content instanceof Stringable ? (string)$content : "";
	}

	/** Generate common root/item attributes for the responsive API. */
	private static function responsiveAttributes(
		array $settings,
		array $default_class,
		array $default_style = [],
		bool $root = false
	): string {
		$class = str::getAttrTag("class", str::getAttrArray(
			$settings['class'] ?? NULL,
			$default_class,
			$settings['only_class'] ?? NULL
		));
		$style = str::getAttrTag("style", str::getAttrArray(
			$settings['style'] ?? NULL,
			$default_style,
			$settings['only_style'] ?? NULL
		));
		$id = str::getAttrTag("id", $settings['id'] ?? NULL);
		$data = str::getDataAttr($settings['data'] ?? NULL);
		$role = str::getAttrTag("role", $settings['role'] ?? NULL);
		$tabindex = str::getAttrTag("tabindex", $settings['tabindex'] ?? NULL);
		$dir = $root && ($settings['rtl'] ?? false) ? str::getAttrTag("dir", "rtl") : NULL;
		$aria = self::responsivePrefixedAttributes("aria", $settings['aria'] ?? NULL);
		$other = self::responsiveCustomAttributes($settings['attributes'] ?? NULL);
		$title = $root && !($settings['tooltip'] ?? NULL)
			? str::getAttrTag("title", strip_tags((string)($settings['alt'] ?? "")))
			: NULL;

		return "{$id}{$class}{$style}{$title}{$data}{$role}{$tabindex}{$dir}{$aria}{$other}";
	}

	private static function responsiveModifier(string $value, array $allowed, string $fallback): string
	{
		$value = strtolower(trim($value));
		return in_array($value, $allowed, true) ? $value : $fallback;
	}

	private static function responsiveTag(string $tag, string $fallback): string
	{
		$tag = strtolower(trim($tag));
		return $tag && preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : $fallback;
	}

	private static function responsivePrefixedAttributes(string $prefix, mixed $attributes): string
	{
		if(!is_array($attributes)){
			return "";
		}

		$html = [];
		foreach($attributes as $name => $value){
			$name = self::responsiveAttributeName((string)$name);
			if($name){
				$html[] = str::getAttrTag("{$prefix}-{$name}", self::responsiveAttributeValue($value));
			}
		}

		return implode("", array_filter($html));
	}

	private static function responsiveCustomAttributes(mixed $attributes): string
	{
		if(!is_array($attributes)){
			return "";
		}

		$reserved = ["id", "class", "style", "title", "data", "role", "tabindex", "dir"];
		$html = [];
		foreach($attributes as $name => $value){
			$name = self::responsiveAttributeName((string)$name);
			if(!$name || in_array($name, $reserved, true) || str_starts_with($name, "aria-")){
				continue;
			}

			$html[] = str::getAttrTag($name, self::responsiveAttributeValue($value), $value === true);
		}

		return implode("", array_filter($html));
	}

	private static function responsiveAttributeName(string $name): string
	{
		$name = strtolower(trim($name));
		return preg_match('/^[a-z][a-z0-9_.:-]*$/', $name) ? $name : "";
	}

	private static function responsiveAttributeValue(mixed $value): mixed
	{
		if(is_bool($value)){
			return $value ? "true" : "false";
		}
		return is_array($value) ? json_encode($value) : $value;
	}

	public static function generateRows(?array $rows): ?string
	{
		if(!$rows){
			return NULL;
		}

		if(!array_key_exists("rows", $rows)){
			$rows = [
				"rows" => $rows,
			];
		}

		if(!is_array($rows['rows'])){
			return NULL;
		}

		$row_class = is_array($rows['class']) ? $rows['class'] : [$rows['class']];

		$row_class[] = "grid-row";

		# Allow for the row barrier to be moved by the user
		if($rows['split'] !== false){
			$row_class[] = "col-split";
		}

		# If the breakpoint key is set to false, the columns won't fold on small screens
		if($rows['breakpoint'] === false){
			$row_class[] = "row-cols-2";
		}

		$grid = [];

		foreach($rows['rows'] as $key => $value){
			# Formatting of the left side of the row
			$left = [
				"class" => "small",
				"sm" => $rows['sm'],
			];

			if(is_int($key) && is_array($value)){
				/**
				 * If the value is itself an array,
				 * and the key is just a number,
				 * which can be done if there is a
				 * chance there is more than one
				 * row with the same key.
				 */
				foreach($value as $k => $v){
					if(!$v){
//						$v = "'$v'";
					}
					$left['html'] = $k;
					$grid[] = [
						"row_class" => $row_class,
						"html" => [$left, $v],
					];
				}
				continue;
			}

			$left['html'] = $key;
			$grid[] = [
				"row_class" => $row_class,
				"row_style" => $rows['style'],
				"html" => [$left, $value],
			];
		}

		return Grid::generate($grid);
	}

	/**
	 * Generate a cell title.
	 *
	 * @param $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateTitle($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["title" => $a];

		extract($a);

		# Title
		$title = $title ?: $html;

		# ID
		$id = str::getAttrTag("id", $id);

		# Icon
		$icon = Icon::generate($icon);

		# Badge
		if($badge = Badge::generate($badge)){
			$badge = " " . $badge;
		}

		# Button(s)
		$button = Button::generate($button);

		# Class
		$class_array = str::getAttrArray($class, "text-title", $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# If there is a link or action, that will ONLY apply to the title TEXT, not (optional) icons or badge
		if($href = href::generate($a)){
			$title = "<a{$href}>{$title}</a>";
		}

		# Alt
		$alt = str::getAttrTag("title", $alt);

		# Tag
		$tag = $tag ?: "div";

		# Subtitle
		if($subtitle){
			$subtitle = "<div class='text-subtitle'>{$subtitle}</div>";
		}

		return "<{$tag}{$id}{$class}{$style}{$href}{$alt}>{$icon}{$title}{$badge}{$button}{$subtitle}</{$tag}>";
	}

	/**
	 * @param string|array|null $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private static function generateBody($a): ?string
	{
		if(!$a){
			return NULL;
		}

		$a = is_array($a) ? $a : ["body" => $a];
		extract($a);

		$body = $body ?: $html;

		$tag = $tag ?: "p";
		if(is_string($body) && $body != strip_tags($body)){
			//if the body contains HTML, it will be pushed out of the p tag, so we must switch to a div
			$tag = "div";
			$default_style = [
				"margin-top" => "0",
				"margin-bottom" => "1rem",
			];
			//And format it like a p
		}
		$id = str::getAttrTag("id", $id);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "text-body", $only_class);
		$class = str::getAttrTag("class", $class_array);
		$style_array = str::getAttrArray($style, $default_style, $only_style);
		$style = str::getAttrTag("style", $style_array);
		$alt = str::getAttrTag("title", $alt);

		return "<{$tag}{$id}{$class}{$style}{$alt}>{$body}{$button}</{$tag}>";
	}
}
