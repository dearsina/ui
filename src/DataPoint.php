<?php

namespace App\UI;

use App\Common\href;
use App\Common\str;
use Stringable;

/**
 * Renders a labelled value with an optional comparison result.
 *
 * DataPoint is deliberately presentation-focused. It does not compare values
 * or calculate match scores; callers perform their own domain-specific
 * comparison and pass the resulting comparison state to this component.
 *
 * Data points may be rendered individually or collected into a configurable
 * matrix. The class supports ordinary labelled values, externally calculated
 * comparison results, and linked tiles containing a leading icon, title,
 * optional subtitle, and trailing directional icon.
 *
 * Values are treated as trusted HTML by default so callers can supply other UI
 * components, images, signatures, or preformatted values. Use the `text` type
 * when content must be escaped.
 */
class DataPoint
{
	public const TYPE_HTML = "html";
	public const TYPE_TEXT = "text";
	public const TYPE_CODE = "code";

	public const FLOW_COLUMN = "column";
	public const FLOW_ROW = "row";
	public const COMPARISON_MATCH = "match";
	public const COMPARISON_NEAR_MATCH = "near-match";
	public const COMPARISON_MISMATCH = "mismatch";
	public const COMPARISON_UNASSESSED = "unassessed";

	private const DEFAULT_COMPARISON_TITLE = "Compared value";
	private const DEFAULT_COLUMNS = 2;

	/**
	 * Render one data point.
	 *
	 * The optional `comparison` setting enables the comparison section and must
	 * contain both `value` and `matched`. Its optional `state` may be `match`,
	 * `near-match`, `mismatch`, or `unassessed`; when omitted, the matched boolean
	 * selects the positive or negative state. Its `title` defaults to "Compared value",
	 * while `tooltip` is attached specifically to the state indicator.
	 *
	 * Common settings include:
	 * - `type`: Value type. `html` is the default; `text` escapes and `code`
	 *   applies monospace presentation. Unknown types remain valid modifier
	 *   classes so new types can be introduced without changing this API.
	 * - `title_type`: Optional type used to render the title.
	 * - `formatter`: Optional callable receiving the value and settings before
	 *   the type is rendered. Retained as a backwards-compatible default for
	 *   both key and value content.
	 * - `key`: Settings applied only to the title/key element. Supports `type`,
	 *   `formatter`, and the common element settings such as class, style, data,
	 *   tooltip, ARIA, and custom attributes.
	 * - `value`: Settings applied only to the value element. Supports the same
	 *   content and element settings as `key`.
	 * - `comparison`: Comparison value, result, title, type, formatter, tooltip,
	 *   and icon overrides. Supplying `hash` and optional `approve` settings makes
	 *   the state indicator an accessible confirmed action.
	 * - `copy`: Controls the lower-right value-copy action. Boxed, non-linked
	 *   data points enable it by default. Set FALSE to remove it, TRUE to use the
	 *   rendered value, a scalar to copy that value instead, or an array containing
	 *   optional `text`, `alt`, `class`, and `data` overrides. Missing values,
	 *   linked tiles, and inline points do not show a default copy action.
	 * - `inline`: Removes the boxed surface and places the title and value on one
	 *   compact line. Prefer the inline() convenience method when known upfront.
	 * - `heading`: Applies the compact, high-contrast heading composition used
	 *   when an inline data point is placed in a coloured card header.
	 * - `inverse`: Uses light title/value colours for placement on a dark or
	 *   coloured surface.
	 * - `rtl`: Emits `dir="rtl"` and moves the logical leading border to the
	 *   right-hand side.
	 * - The standard `id`, `class`, `only_class`, `style`, `only_style`, `data`,
	 *   `alt`, `tooltip`, `tag`, link, ARIA, and custom attribute settings.
	 *
	 * @param string              $title    Label displayed above the primary value.
	 * @param mixed               $value    Primary value. Trusted HTML unless `type` is `text`.
	 * @param array<string,mixed> $settings Rendering, comparison, and element settings.
	 *
	 * @return string Complete data-point HTML.
	 */
	public static function generate(string $title, mixed $value, array $settings = []): string
	{
		$key_settings = is_array($settings['key'] ?? NULL) ? $settings['key'] : [];
		$value_settings = is_array($settings['value'] ?? NULL) ? $settings['value'] : [];
		$type = self::normaliseType((string)($value_settings['type'] ?? $settings['type'] ?? self::TYPE_HTML));
		$title_type = self::normaliseType((string)($key_settings['type'] ?? $settings['title_type'] ?? self::TYPE_HTML));
		$comparison = self::getComparison($settings['comparison'] ?? NULL);

		$classes = [
			"data-point",
			"data-point--type-{$type}",
			($settings['inline'] ?? false) ? "data-point--inline" : NULL,
			($settings['heading'] ?? false) ? "data-point--inline-heading" : NULL,
			($settings['inverse'] ?? false) ? "data-point--inverse" : NULL,
		];
		if($comparison){
			$classes[] = "data-point--compared";
			$classes[] = "data-point--{$comparison['state']}";
		}

		$key_content_settings = array_replace($settings, $key_settings);
		$value_content_settings = array_replace($settings, $value_settings);
		$title_html = self::renderValue($title, $title_type, $key_content_settings);
		$value_html = self::renderValue($value, $type, $value_content_settings);
		$is_missing = self::isMissingValue($value_html);
		if($is_missing){
			$value_settings['class'] = str::getAttrArray($value_settings['class'] ?? NULL, "text-silent");
		}
		$copy_control = self::copyControl($title, $value_html, $settings, $is_missing);
		if($copy_control){
			$classes[] = "data-point--copyable";
		}

		$settings['title'] = $settings['title'] ?? strip_tags($title);
		[$tag, $attributes] = self::getElement(
			$settings,
			$classes,
			[],
			"div"
		);
		[$key_tag, $key_attributes] = self::getElement(
			$key_settings,
			["data-point__title", "data-point__title--type-{$title_type}"],
			[],
			"span"
		);
		[$value_tag, $value_attributes] = self::getElement(
			$value_settings,
			["data-point__value", "data-point__value--type-{$type}"],
			[],
			"div"
		);
		$comparison_indicator = $comparison ? self::comparisonIndicator($comparison) : "";
		$header_actions = $comparison_indicator
			? '<span class="data-point__header-actions">' . $comparison_indicator . '</span>'
			: "";
		$comparison_html = $comparison ? self::comparisonHtml($comparison, $type) : "";

		return <<<HTML
<{$tag}{$attributes}>
    <div class="data-point__header">
		<{$key_tag}{$key_attributes}>{$title_html}</{$key_tag}>
        {$header_actions}
    </div>
	<{$value_tag}{$value_attributes}>{$value_html}</{$value_tag}>
    {$comparison_html}
	{$copy_control}
</{$tag}>
HTML;
	}

	/**
	 * Render a compact title/value pair without the standard boxed surface.
	 *
	 * Inline points are useful in headings, card footers, table cells, and other
	 * places where a complete DataPoint tile would be visually too heavy. All
	 * settings accepted by generate() remain available, including types, RTL,
	 * inverse colours, comparison metadata, links, tooltips, and attributes.
	 * The method only enables the `inline` setting and delegates rendering.
	 *
	 * @param string              $title    Short label displayed beside the value.
	 * @param mixed               $value    Trusted primary value unless `type` is `text`.
	 * @param array<string,mixed> $settings Standard DataPoint settings and overrides.
	 *
	 * @return string Complete inline data-point HTML.
	 */
	public static function inline(string $title, mixed $value, array $settings = []): string
	{
		$settings['inline'] = true;
		return self::generate($title, $value, $settings);
	}

	/**
	 * Convenience method for a data point with a known comparison outcome.
	 *
	 * The caller supplies the matched boolean, keeping comparison methodology
	 * outside the UI package. A caller with a non-binary outcome may additionally
	 * supply `comparison.state`; this method selects the correct border and
	 * indicator by placing the comparison values into the settings consumed by
	 * generate(). Any nested comparison settings are retained.
	 *
	 * @param string              $title          Label displayed above the primary value.
	 * @param mixed               $value          Primary value.
	 * @param mixed               $compared_value Value against which the primary value was compared.
	 * @param bool                $matched         TRUE for the positive state; FALSE for the negative state.
	 * @param array<string,mixed> $settings        Rendering, comparison, and element settings.
	 *
	 * @return string Complete compared data-point HTML.
	 */
	public static function compared(
		string $title,
		mixed $value,
		mixed $compared_value,
		bool $matched,
		array $settings = []
	): string {
		$comparison = is_array($settings['comparison'] ?? NULL) ? $settings['comparison'] : [];
		$comparison['value'] = $compared_value;
		$comparison['matched'] = $matched;
		$settings['comparison'] = $comparison;

		return self::generate($title, $value, $settings);
	}

	/**
	 * Render an icon, title, and optional subtitle as one linked data point.
	 *
	 * The root is always an `<a>` element, so the icon, title, subtitle, whitespace,
	 * and trailing icon form one accessible click target. A caller should supply
	 * one of the common link settings (`url`, `uri`, `href`, `hash`, `onclick`,
	 * `onClick`, or `div`). Those settings are forwarded unchanged to the common
	 * href helper; this includes application hashes that open Window objects.
	 *
	 * Link-specific settings include:
	 * - `icon` (`string|array|false|null`): A leading Icon name or complete Icon
	 *   settings array. The array is passed to Icon::generate(), allowing icon
	 *   type, colour, class, alternate text, and tooltip settings. Omit it or set
	 *   it to FALSE/NULL to render no leading icon.
	 * - `trailing_icon` (`string|array|false|null`): The trailing Icon name or
	 *   complete Icon settings array. It defaults to `chevron-right`, changes to
	 *   `chevron-left` when `rtl` is TRUE, and may be replaced with any supported
	 *   icon. Set it to FALSE/NULL to remove it.
	 * - `title_type` / `subtitle_type`: Open-ended render types. Both default to
	 *   `html`, so their content is trusted HTML. Set either to `text` to escape
	 *   its content. Each type also produces a type-specific CSS modifier class.
	 * - `title_formatter` / `subtitle_formatter`: Optional content-specific
	 *   callables. Each receives its content and the complete settings array, and
	 *   its return value is rendered according to the corresponding type.
	 * - `rtl` (`bool`): Emits `dir="rtl"`, lets the flex layout place the leading
	 *   icon on the right and copy to its left, and reverses the default chevron.
	 * - `alt` / `tooltip`: Supplies native alternate context or the standard UI
	 *   tooltip for the complete anchor. Icon-specific context belongs inside the
	 *   relevant icon settings array.
	 * - `target` / `rel`: Applies standard navigation attributes to the anchor.
	 * - `id`, `class`, `only_class`, `style`, `only_style`, `data`, `aria`, and
	 *   `attributes`: Applies the common element overrides and custom attributes.
	 *
	 * The subtitle is omitted entirely when it is NULL or an empty string. The
	 * leading icon is optional; the trailing directional icon is present by
	 * default. The title and subtitle remain visible text, so no separate ARIA
	 * label is required unless the caller needs more context.
	 *
	 * Direct example:
	 * <code>
	 * DataPoint::link("Company profile", "Official company overview", [
	 *     "icon" => ["name" => "building", "type" => "solid"],
	 *     "url" => "/company/04366849",
	 *     "alt" => "Open the company profile",
	 * ]);
	 * </code>
	 *
	 * Application hash example (the complete tile retains the application's
	 * normal hash/Window behaviour):
	 * <code>
	 * DataPoint::link("Filing history", "Accounts and other filings", [
	 *     "icon" => "file-alt",
	 *     "hash" => [
	 *         "rel_table" => "companies_house_link",
	 *         "action" => "view",
	 *         "vars" => ["link" => "filing_history"],
	 *     ],
	 * ]);
	 * </code>
	 *
	 * @param string              $title    Primary link text; trusted HTML unless `title_type` is `text`.
	 * @param mixed               $subtitle Optional supporting text beneath the title; trusted HTML unless
	 *                                      `subtitle_type` is `text`.
	 * @param array<string,mixed> $settings Icon, link, content, and element settings.
	 *
	 * @return string Complete linked data-point anchor HTML.
	 */
	public static function link(string $title, mixed $subtitle = NULL, array $settings = []): string
	{
		$title_type = self::normaliseType((string)($settings['title_type'] ?? self::TYPE_HTML));
		$subtitle_type = self::normaliseType((string)($settings['subtitle_type'] ?? self::TYPE_HTML));

		$settings['title'] = $settings['title'] ?? strip_tags($title);
		// A linked data point always has one semantic, clickable root element.
		$settings['tag'] = "a";
		[$tag, $attributes] = self::getElement(
			$settings,
			["data-point", "data-point--link"],
			[],
			"a"
		);

		$title_settings = $settings;
		$title_settings['formatter'] = $settings['title_formatter'] ?? NULL;
		$subtitle_settings = $settings;
		$subtitle_settings['formatter'] = $settings['subtitle_formatter'] ?? NULL;

		$title_html = self::renderValue($title, $title_type, $title_settings);
		$subtitle_html = $subtitle === NULL || $subtitle === ""
			? ""
			: '<span class="data-point__link-subtitle data-point__link-subtitle--type-' . $subtitle_type . '">'
				. self::renderValue($subtitle, $subtitle_type, $subtitle_settings)
				. '</span>';
		$leading_icon = self::linkedIcon($settings['icon'] ?? NULL, "data-point__leading-icon");
		$trailing_icon = array_key_exists('trailing_icon', $settings)
			? $settings['trailing_icon']
			: (($settings['rtl'] ?? false) ? "chevron-left" : "chevron-right");
		$trailing_icon = self::linkedIcon($trailing_icon, "data-point__trailing-icon");

		return <<<HTML
<{$tag}{$attributes}>
    {$leading_icon}
    <span class="data-point__link-copy">
        <span class="data-point__link-title data-point__link-title--type-{$title_type}">{$title_html}</span>
        {$subtitle_html}
    </span>
    {$trailing_icon}
</{$tag}>
HTML;
	}

	/**
	 * Render an array of data points as a responsive grid matrix.
	 *
	 * The matrix defaults to two columns with column-major flow. Set `flow` to
	 * `row` for row-major placement, or change `columns` to any positive number.
	 * Column-major flow is handled by CSS Grid, preserving the supplied DOM order
	 * for assistive technologies and restoring that order when the responsive
	 * layout collapses to one column.
	 *
	 * Each data point may be:
	 * - A pre-rendered HTML string.
	 * - An associative definition containing `title`, `value`, and any DataPoint
	 *   settings at the same level.
	 * - A linked definition containing `variant => "link"`, `title`, optional
	 *   `subtitle`, and any settings accepted by link(). Link settings may be
	 *   placed directly in the definition or in its nested `settings` array. If
	 *   both contain the same key, the nested setting takes precedence.
	 * - A tuple in the form `[title, value, settings]`.
	 *
	 * Matrix settings are passed to Grid::responsive(). They include `columns`,
	 * `flow`, `gap`, `responsive`, `collapse_at`, `align`, `stretch`, and the
	 * standard root-element settings supported by the responsive grid.
	 *
	 * Linked matrix example:
	 * <code>
	 * DataPoint::matrix([
	 *     [
	 *         "variant" => "link",
	 *         "title" => "Company profile",
	 *         "subtitle" => "Official company overview",
	 *         "icon" => "building",
	 *         "url" => "/company/04366849",
	 *     ],
	 *     [
	 *         "variant" => "link",
	 *         "title" => "Filing history",
	 *         "subtitle" => "Accounts and other filings",
	 *         "icon" => "file-alt",
	 *         "url" => "/company/04366849/filing-history",
	 *     ],
	 * ], [
	 *     "columns" => 2,
	 *     "flow" => DataPoint::FLOW_ROW,
	 * ]);
	 * </code>
	 *
	 * @param array<int,array|string> $data_points Data-point definitions or pre-rendered HTML.
	 * @param array<string,mixed>     $settings    Matrix layout and element settings.
	 *
	 * @return string Matrix HTML, or an empty string when no points are renderable.
	 */
	public static function matrix(array $data_points, array $settings = []): string
	{
		$html = [];
		foreach($data_points as $data_point){
			if($rendered = self::renderDefinition($data_point)){
				$html[] = $rendered;
			}
		}

		if(!$html){
			return "";
		}

		$settings['columns'] = max(1, (int)($settings['columns'] ?? self::DEFAULT_COLUMNS));
		$settings['flow'] = strtolower((string)($settings['flow'] ?? self::FLOW_COLUMN)) === self::FLOW_ROW
			? Grid::FLOW_ROW
			: Grid::FLOW_COLUMN;
		$settings['gap'] = $settings['gap'] ?? ".65rem";
		$settings['class'] = str::getAttrArray($settings['class'] ?? NULL, "data-point-matrix");

		return Grid::responsive($html, $settings);
	}

	/**
	 * Return a valid comparison only when both its value and outcome exist.
	 *
	 * @param mixed $comparison Candidate comparison setting.
	 *
	 * @return array<string,mixed>|null Normalised comparison or NULL.
	 */
	private static function getComparison(mixed $comparison): ?array
	{
		if(!is_array($comparison)
			|| !array_key_exists('value', $comparison)
			|| !array_key_exists('matched', $comparison)){
			return NULL;
		}

		$comparison['matched'] = (bool)$comparison['matched'];
		$fallback_state = $comparison['matched']
			? self::COMPARISON_MATCH
			: self::COMPARISON_MISMATCH;
		$state = strtolower(trim((string)($comparison['state'] ?? $fallback_state)));
		$state = str_replace(["_", " "], "-", $state);
		if(!in_array($state, [
			self::COMPARISON_MATCH,
			self::COMPARISON_NEAR_MATCH,
			self::COMPARISON_MISMATCH,
			self::COMPARISON_UNASSESSED,
		], true)){
			$state = $fallback_state;
		}
		$comparison['state'] = $state;
		$comparison['matched'] = $state === self::COMPARISON_MATCH;
		$comparison['title'] = $comparison['title'] ?? self::DEFAULT_COMPARISON_TITLE;
		return $comparison;
	}

	/**
	 * Render the exact-match check, near-match approximation, or mismatch cross.
	 *
	 * Tooltips are attached to this icon rather than the complete data point, so
	 * callers can explain the score or comparison methodology exactly where the
	 * user sees its result.
	 *
	 * @param array<string,mixed> $comparison Normalised comparison settings.
	 *
	 * @return string Comparison indicator wrapper and icon.
	 */
	private static function comparisonIndicator(array $comparison): string
	{
		$icon = [
			"name" => match($comparison['state']) {
				self::COMPARISON_MATCH => "check",
				self::COMPARISON_NEAR_MATCH => "almost-equal-to",
				default => "times",
			},
			"colour" => match($comparison['state']) {
				self::COMPARISON_MATCH => "success",
				self::COMPARISON_NEAR_MATCH => "amber",
				self::COMPARISON_UNASSESSED => "grey",
				default => "danger",
			},
			"type" => "thick"
		];

		if($comparison['tooltip'] ?? NULL){
			$icon['tooltip'] = $comparison['tooltip'];
		}
		if($comparison['alt'] ?? NULL){
			$icon['alt'] = $comparison['alt'];
		}

		if(is_string($comparison['icon'] ?? NULL)){
			$icon['name'] = $comparison['icon'];
		}
		else if(is_array($comparison['icon'] ?? NULL)){
			$icon = array_replace_recursive($icon, $comparison['icon']);
		}

		$indicator = "<span class=\"data-point__comparison-indicator\">" . Icon::generate($icon) . "</span>";
		if(empty($comparison['hash'])){
			return $indicator;
		}

		$link = href::generate(["hash" => $comparison['hash']]);
		if(!$link){
			return $indicator;
		}

		$approve_settings = $comparison['approve'] ?? NULL;
		if(is_array($approve_settings)){
			$approve_settings = array_replace([
				"class" => NULL,
				"rtl" => NULL,
				"yes" => NULL,
				"no" => NULL,
				"title" => NULL,
				"message" => NULL,
			], $approve_settings);
		}
		$approve = str::getApproveAttr(
			$approve_settings,
			$icon,
			$icon['colour'] ?? NULL
		);
		$class = str::getAttrTag("class", str::getAttrArray(
			($comparison['approve'] ?? NULL) ? "approve-decision" : NULL,
			"data-point__comparison-action"
		));
		$action_alt = $comparison['action_alt']
			?? $comparison['alt']
			?? "Use the verified value";
		$aria = str::getAttrTag("aria-label", strip_tags((string)$action_alt));

		return "<a {$link}{$class}{$aria}{$approve}>{$indicator}</a>";
	}

	/**
	 * Render the secondary compared-value section.
	 *
	 * The comparison may define its own `type`, `title_type`, or `formatter`.
	 * When no comparison type is supplied, it inherits the primary value type.
	 *
	 * @param array<string,mixed> $comparison Normalised comparison settings.
	 * @param string              $default_type Primary value type used as the fallback.
	 *
	 * @return string Compared-value HTML.
	 */
	private static function comparisonHtml(array $comparison, string $default_type): string
	{
		$type = self::normaliseType((string)($comparison['type'] ?? $default_type));
		$title_type = self::normaliseType((string)($comparison['title_type'] ?? self::TYPE_HTML));
		$title = self::renderValue($comparison['title'], $title_type, $comparison);
		$value = self::renderValue($comparison['value'], $type, $comparison);
		$value_class = str::getAttrTag("class", str::getAttrArray(
			self::isMissingValue($value) ? "text-silent" : NULL,
			["data-point__comparison-value", "data-point__comparison-value--type-{$type}"]
		));

		return <<<HTML
<div class="data-point__comparison">
    <div class="data-point__comparison-title data-point__comparison-title--type-{$title_type}">{$title}</div>
    <div{$value_class}>{$value}</div>
</div>
HTML;
	}

	/**
	 * Build the hover/focus copy action for a primary data-point value.
	 *
	 * The button uses the application's existing `clipboard` class and
	 * `data-clipboard-text` contract, so ClipboardJS initialisation and copied
	 * feedback remain centralised. Only visible text is inferred from HTML values;
	 * callers can pass `copy.text` when a richer component needs a different
	 * machine-readable representation.
	 *
	 * Copying is enabled by default only for ordinary boxed values. Inline points
	 * are commonly used inside headings, while linked points already have a click
	 * action and cannot safely contain a nested button. An explicit `copy` setting
	 * may enable an inline copy action, but linked points always remain excluded.
	 * Empty and "Not provided" values never produce a misleading copy button.
	 *
	 * @param string              $title      Data-point label used by accessible copy text.
	 * @param string              $value_html Fully rendered primary value.
	 * @param array<string,mixed> $settings   Complete DataPoint settings.
	 * @param bool                $is_missing Whether the value is the standard missing marker.
	 *
	 * @return string Clipboard button HTML, or an empty string when unavailable.
	 */
	private static function copyControl(
		string $title,
		string $value_html,
		array $settings,
		bool $is_missing
	): string {
		if($is_missing || self::hasLink($settings)){
			return "";
		}

		$copy_was_set = array_key_exists('copy', $settings);
		$copy = $copy_was_set ? $settings['copy'] : !($settings['inline'] ?? false);
		if($copy === false || $copy === NULL){
			return "";
		}

		$options = is_array($copy) ? $copy : [];
		if(array_key_exists('text', $options)){
			$copy_text = self::stringify($options['text']);
		}
		else if($copy_was_set && is_scalar($copy) && !is_bool($copy)){
			$copy_text = (string)$copy;
		}
		else {
			$copy_text = self::plainText($value_html);
		}

		if($copy_text === "" || self::isMissingValue($copy_text)){
			return "";
		}

		$label = (string)($options['alt'] ?? "Copy " . self::plainText($title) . " value");
		$classes = str::getAttrTag("class", str::getAttrArray(
			$options['class'] ?? NULL,
			["data-point__copy", "clipboard"]
		));
		$data = is_array($options['data'] ?? NULL) ? $options['data'] : [];
		// getDataAttr() protects quote delimiters, while htmlspecialchars() also
		// keeps tag-like copied text inert in the generated HTML. Browsers decode
		// the entities when ClipboardJS reads the data attribute, so users still
		// receive the original visible value.
		$data['clipboard-text'] = htmlspecialchars(
			$copy_text,
			ENT_QUOTES | ENT_SUBSTITUTE,
			"UTF-8"
		);
		$data = str::getDataAttr($data);
		$title_attribute = str::getAttrTag("title", $label);
		$aria_label = str::getAttrTag("aria-label", $label);

		return "<button type=\"button\"{$classes}{$data}{$title_attribute}{$aria_label}></button>";
	}

	/**
	 * Convert rendered value HTML into concise clipboard text.
	 *
	 * Line breaks are retained while markup, entities, and incidental horizontal
	 * whitespace are normalised. This copies what the reviewer can read rather
	 * than component tags or icon markup.
	 */
	private static function plainText(string $html): string
	{
		$html = preg_replace('/<br\s*\/?\s*>/i', "\n", $html);
		$text = html_entity_decode(strip_tags((string)$html), ENT_QUOTES | ENT_HTML5, "UTF-8");
		$text = preg_replace('/[\t ]+/', " ", $text);
		$text = preg_replace('/ *\R */u', "\n", (string)$text);

		return trim((string)$text);
	}

	/**
	 * Recognise the shared human-readable marker for unavailable data.
	 *
	 * Detection happens after formatting, allowing either plain text or trusted
	 * HTML such as `<span>Not provided</span>` to receive the same muted state.
	 */
	private static function isMissingValue(string $value): bool
	{
		return strcasecmp(self::plainText($value), "Not provided") === 0;
	}

	/**
	 * Render an Icon inside a stable linked-data-point wrapper.
	 *
	 * The wrapper owns layout and colour while the Icon settings remain available
	 * for weights, tooltips, alternate text, custom classes, and other supported
	 * Icon features.
	 *
	 * @param mixed  $icon  Icon name, Icon settings array, or a falsey value.
	 * @param string $class Wrapper class identifying its position.
	 *
	 * @return string Wrapped icon HTML, or an empty string.
	 */
	private static function linkedIcon(mixed $icon, string $class): string
	{
		if(!$icon || (!is_string($icon) && !is_array($icon))){
			return "";
		}

		return '<span class="' . $class . '">' . Icon::generate($icon) . '</span>';
	}

	/**
	 * Render one value according to its open-ended type.
	 *
	 * Only `text` changes the content itself by escaping HTML. The `html` and
	 * `code` types, as well as unknown future types, retain trusted HTML. Every
	 * type receives a CSS modifier, allowing presentation for money, percentage,
	 * image, signature, or future types to be added without changing markup.
	 *
	 * @param mixed               $value    Value to render.
	 * @param string              $type     Normalised type name.
	 * @param array<string,mixed> $settings Value-specific settings.
	 *
	 * @return string Rendered value.
	 */
	private static function renderValue(mixed $value, string $type, array $settings): string
	{
		if(is_callable($settings['formatter'] ?? NULL)){
			$value = $settings['formatter']($value, $settings);
		}

		$value = self::stringify($value);
		return $type === self::TYPE_TEXT
			? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")
			: $value;
	}

	/**
	 * Convert a matrix definition into data-point HTML.
	 *
	 * @param mixed $definition Pre-rendered string, associative definition, or tuple.
	 *
	 * @return string|null Rendered data point, or NULL for an invalid definition.
	 */
	private static function renderDefinition(mixed $definition): ?string
	{
		if(is_string($definition)){
			return $definition;
		}
		if(!is_array($definition)){
			return NULL;
		}

		if(str::isNumericArray($definition)){
			[$title, $value, $settings] = array_pad($definition, 3, NULL);
			if($title === NULL || !is_array($settings ?? [])){
				return NULL;
			}

			return self::generate((string)$title, $value, $settings ?? []);
		}

		if(($definition['variant'] ?? NULL) === "link"){
			if(!array_key_exists('title', $definition)){
				return isset($definition['html']) ? (string)$definition['html'] : NULL;
			}

			$title = (string)$definition['title'];
			$subtitle = $definition['subtitle'] ?? NULL;
			$nested_settings = is_array($definition['settings'] ?? NULL) ? $definition['settings'] : [];
			unset($definition['variant'], $definition['title'], $definition['subtitle'], $definition['settings']);

			return self::link($title, $subtitle, array_replace($definition, $nested_settings));
		}

		if(!array_key_exists('title', $definition) || !array_key_exists('value', $definition)){
			return isset($definition['html']) ? (string)$definition['html'] : NULL;
		}

		$title = (string)$definition['title'];
		$value = $definition['value'];
		$nested_settings = is_array($definition['settings'] ?? NULL) ? $definition['settings'] : [];
		unset($definition['title'], $definition['value'], $definition['settings']);

		return self::generate($title, $value, array_replace($definition, $nested_settings));
	}

	/**
	 * Produce the root tag and attributes shared by a point and its matrix.
	 *
	 * @param array<string,mixed> $settings      Element settings.
	 * @param array<int,string>   $default_class Required component classes.
	 * @param array<string,mixed> $default_style Required component styles/custom properties.
	 * @param string              $default_tag   Tag used when the element is not a link.
	 *
	 * @return array{0:string,1:string} Safe tag name and concatenated attributes.
	 */
	private static function getElement(
		array $settings,
		array $default_class,
		array $default_style,
		string $default_tag
	): array {
		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$link = self::getLink($settings);
		$tag = self::getTag($settings['tag'] ?? NULL, $link ? "a" : $default_tag);
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
		$target = str::getAttrTag("target", $settings['target'] ?? NULL);
		$rel = str::getAttrTag("rel", $settings['rel'] ?? NULL);
		$role = str::getAttrTag("role", $settings['role'] ?? NULL);
		$tabindex = str::getAttrTag("tabindex", $settings['tabindex'] ?? NULL);
		$dir = ($settings['rtl'] ?? false) ? str::getAttrTag("dir", "rtl") : NULL;
		$aria = self::getPrefixedAttributes("aria", $settings['aria'] ?? NULL);
		$other = self::getAttributes($settings['attributes'] ?? NULL);
		$title = ($settings['tooltip'] ?? NULL)
			? NULL
			: str::getAttrTag("title", strip_tags((string)($settings['alt'] ?? "")));

		return [
			$tag,
			"{$id}{$class}{$style}{$title}{$data}{$target}{$rel}{$role}{$tabindex}{$dir}{$aria}{$other}{$link}",
		];
	}

	/**
	 * Normalise an open-ended type into a safe CSS modifier suffix.
	 */
	private static function normaliseType(string $type): string
	{
		$type = strtolower(trim($type));
		$type = trim((string)preg_replace('/[^a-z0-9_-]+/', '-', $type), "-");
		return $type ?: self::TYPE_HTML;
	}

	/**
	 * Convert a supported value into an HTML-renderable string.
	 */
	private static function stringify(mixed $value): string
	{
		if($value === NULL){
			return "";
		}
		if(is_string($value) || is_numeric($value)){
			return (string)$value;
		}
		if(is_bool($value)){
			return $value ? "true" : "false";
		}
		if($value instanceof Stringable){
			return (string)$value;
		}

		$json = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		return $json === false ? "" : $json;
	}

	/**
	 * Resolve a safe root tag, falling back to the requested default.
	 */
	private static function getTag(?string $tag, string $default): string
	{
		$tag = strtolower(trim((string)$tag));
		return $tag && preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : $default;
	}

	/**
	 * Generate link attributes only when link-related settings are present.
	 */
	private static function getLink(array $settings): ?string
	{
		return self::hasLink($settings) ? href::generate($settings) : NULL;
	}

	/** Determine whether settings turn the complete data point into a link. */
	private static function hasLink(array $settings): bool
	{
		$link_keys = ["url", "uri", "href", "hash", "onclick", "onClick", "div"];

		return array_any($link_keys, static function(string $key) use ($settings): bool {
			return isset($settings[$key]) && $settings[$key] !== "";
		});
	}

	/**
	 * Convert an associative array into consistently prefixed attributes.
	 */
	private static function getPrefixedAttributes(string $prefix, ?array $attributes): ?string
	{
		if(!$attributes){
			return NULL;
		}

		$html = [];
		foreach($attributes as $name => $value){
			$name = self::normaliseAttributeName((string)$name);
			if($name){
				$html[] = str::getAttrTag("{$prefix}-{$name}", self::normaliseAttributeValue($value));
			}
		}

		return implode("", array_filter($html));
	}

	/**
	 * Generate caller-defined attributes while avoiding duplicate core ones.
	 */
	private static function getAttributes(?array $attributes): ?string
	{
		if(!$attributes){
			return NULL;
		}

		$reserved = ["id", "class", "style", "title", "data", "target", "rel", "role", "tabindex", "dir"];
		$html = [];
		foreach($attributes as $name => $value){
			$name = self::normaliseAttributeName((string)$name);
			if(!$name || in_array($name, $reserved, true) || str_starts_with($name, "aria-")){
				continue;
			}

			$html[] = str::getAttrTag($name, self::normaliseAttributeValue($value), $value === true);
		}

		return implode("", array_filter($html));
	}

	/**
	 * Validate a caller-defined attribute name.
	 */
	private static function normaliseAttributeName(string $name): string
	{
		$name = strtolower(trim($name));
		return preg_match('/^[a-z][a-z0-9_.:-]*$/', $name) ? $name : "";
	}

	/**
	 * Convert non-scalar attribute values into an HTML-friendly form.
	 */
	private static function normaliseAttributeValue(mixed $value): mixed
	{
		if(is_bool($value)){
			return $value ? "true" : "false";
		}
		if(is_array($value)){
			return json_encode($value);
		}

		return $value;
	}
}
