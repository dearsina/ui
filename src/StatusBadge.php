<?php

namespace App\UI;

use App\Common\href;
use App\Common\str;

/**
 * Generates compact, reusable status indicators containing an icon and text.
 *
 * Status badges are intended for short, immediately recognisable states such
 * as "Matched", "Mismatch", "Active", or "Not compared". The component owns
 * only the HTML structure and its semantic CSS classes; its presentation lives
 * in `_status-badge.scss`.
 *
 * The positive(), negative(), and neutral() methods provide consistent common
 * states. generate() is the lower-level factory behind those helpers and can
 * be used for any icon and theme colour.
 *
 * This class does not decide whether a business condition is positive or
 * negative. The calling service remains responsible for interpreting its data
 * and selecting the appropriate badge state.
 *
 * Basic usage:
 * <code>
 * StatusBadge::positive("Matched");
 * StatusBadge::negative("Mismatch");
 * StatusBadge::neutral("Not compared");
 * StatusBadge::generate("Pending", "clock", "orange");
 * </code>
 */
class StatusBadge
{
	/**
	 * Generate the standard positive status badge.
	 *
	 * A positive badge always starts with the shared positive defaults: a check
	 * icon and the green colour theme. The optional settings are forwarded to
	 * generate(), allowing the caller to add an ID, classes, tooltip, data
	 * attributes, accessibility metadata, or any other supported setting.
	 *
	 * Example:
	 * <code>
	 * StatusBadge::positive("Matched", [
	 *     "tooltip" => "The submitted and returned values match.",
	 *     "data" => ["field" => "company_name"],
	 * ]);
	 * </code>
	 *
	 * @param string              $text   The user-facing text displayed alongside the check icon.
	 * @param array<string,mixed> $settings Optional element and icon settings accepted by generate().
	 *
	 * @return string The complete status-badge HTML element.
	 */
	public static function positive(string $text, array $settings = []): string
	{
		return self::generate($text, "check", "green", $settings);
	}

	/**
	 * Generate the standard negative status badge.
	 *
	 * A negative badge always starts with the shared negative defaults: a cross
	 * icon and the red colour theme. Use this state when the caller has made a
	 * definite negative determination, for example a failed comparison or an
	 * unhealthy condition. An unknown or untested state should normally use
	 * neutral() instead.
	 *
	 * Example:
	 * <code>
	 * StatusBadge::negative("Mismatch", [
	 *     "alt" => "The client-submitted value does not match.",
	 *     "class" => "company-name-result",
	 * ]);
	 * </code>
	 *
	 * @param string              $text   The user-facing text displayed alongside the cross icon.
	 * @param array<string,mixed> $settings Optional element and icon settings accepted by generate().
	 *
	 * @return string The complete status-badge HTML element.
	 */
	public static function negative(string $text, array $settings = []): string
	{
		return self::generate($text, "times", "red", $settings);
	}

	/**
	 * Generate the standard neutral status badge.
	 *
	 * A neutral badge always starts with the shared neutral defaults: a circle
	 * icon and the grey colour theme. It is suitable for unavailable, unknown,
	 * pending, untested, or informational values where neither a positive nor a
	 * negative conclusion should be implied.
	 *
	 * Example:
	 * <code>
	 * StatusBadge::neutral("Not compared", [
	 *     "aria" => ["label" => "This value was not compared"],
	 * ]);
	 * </code>
	 *
	 * @param string              $text   The user-facing text displayed alongside the circle icon.
	 * @param array<string,mixed> $settings Optional element and icon settings accepted by generate().
	 *
	 * @return string The complete status-badge HTML element.
	 */
	public static function neutral(string $text, array $settings = []): string
	{
		return self::generate($text, "circle", "grey", $settings);
	}

	/**
	 * Generate a status badge with an explicit icon and colour.
	 *
	 * This is the meta/factory method used by positive(), negative(), and
	 * neutral(). It may also be called directly to represent additional states
	 * such as pending, warning, or informational statuses. Every argument is
	 * optional: omitted or NULL content is simply left out of the rendered badge.
	 * Calling generate() without arguments produces a valid base badge element.
	 *
	 * The icon may be a Font Awesome icon name or the same configuration array
	 * accepted by Icon::generate(). A NULL icon omits the icon wrapper. The colour
	 * is converted into a safe CSS modifier, for example `orange` becomes
	 * `status-badge--orange`; a NULL colour omits that modifier. Colours defined
	 * in the UI theme are styled automatically by `_status-badge.scss`.
	 * A custom modifier can be used when the consumer supplies the corresponding
	 * CSS class in its own stylesheet.
	 *
	 * Text is escaped with htmlspecialchars() by default. This makes ordinary
	 * service values safe to display without requiring callers to escape them
	 * first. Set `html` to TRUE only when the supplied text is trusted HTML. A
	 * NULL text value omits the text wrapper, which permits icon-only badges.
	 *
	 * Supported settings:
	 * - `id`: Element ID.
	 * - `class`: Additional class name or array of class names.
	 * - `only_class`: Replace the component's default classes entirely.
	 * - `style`: Additional inline style string or associative style array.
	 * - `only_style`: Replace styles supplied through `style`.
	 * - `data`: Associative array converted into `data-*` attributes.
	 * - `alt`: Native title text when no Bootstrap tooltip is configured.
	 * - `tooltip`: Boolean, string, or Tooltip configuration array.
	 * - `tag`: Explicit root tag. Defaults to `span`, or `a` for a link.
	 * - `icon`: String replacement or array merged over the supplied icon.
	 * - `indicator`: Set to `dot` to render a compact, solid circular indicator
	 *   instead of the supplied Font Awesome icon. The dot is decorative and the
	 *   badge text remains the accessible status description.
	 * - `surface`: Optional visual surface modifier, for example `raised`. The
	 *   value becomes `status-badge--surface-{value}` so presentation remains in
	 *   `_status-badge.scss` and additional surfaces can be introduced later.
	 * - `html`: Allow trusted HTML in the text when TRUE.
	 * - `rtl`: Add the semantic `dir="rtl"` attribute when TRUE. This gives the
	 *   text the correct bidirectional context and places the icon on the right.
	 * - `url`, `uri`, `href`, `hash`, `onclick`, `onClick`, `div`: Link settings
	 *   understood by href::generate().
	 * - `target` and `rel`: Link attributes.
	 * - `role` and `tabindex`: Common interaction/accessibility attributes.
	 * - `aria`: Associative array converted into `aria-*` attributes.
	 * - `attributes`: Additional explicitly named HTML attributes.
	 *
	 * Example using a custom state and several settings:
	 * <code>
	 * StatusBadge::generate("Pending", [
	 *     "name" => "clock",
	 *     "type" => "solid",
	 * ], "orange", [
	 *     "id" => "verification-status",
	 *     "class" => ["verification-result"],
	 *     "tooltip" => [
	 *         "title" => "Verification is still running.",
	 *         "placement" => "bottom",
	 *     ],
	 *     "data" => ["status" => "pending"],
	 *     "aria" => ["live" => "polite"],
	 * ]);
	 * </code>
	 *
	 * @param string|null                     $text     Optional user-facing text. Escaped unless `html` is TRUE.
	 * @param array<string,mixed>|string|null $icon     Optional icon name or Icon::generate() configuration.
	 * @param string|null                     $colour   Optional UI colour used for the CSS modifier class.
	 * @param array<string,mixed>|null        $settings Optional root, link, tooltip, and icon settings.
	 *
	 * @return string The complete status-badge HTML element.
	 */
	public static function generate(
		?string $text = NULL,
		array|string|null $icon = NULL,
		?string $colour = NULL,
		?array $settings = NULL
	): string
	{
		$settings ??= [];
		$settings['title'] = $settings['title'] ?? $text;

		$icon = self::applyIconOverrides($icon, $settings['icon'] ?? NULL);
		unset($settings['icon']);

		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$link = self::getLink($settings);
		$tag = self::getTag($settings['tag'] ?? NULL, (bool)$link);
		$colour = self::normaliseColour($colour);
		$indicator = self::normaliseModifier((string)($settings['indicator'] ?? ""));
		$surface = self::normaliseModifier((string)($settings['surface'] ?? ""));

		$class = str::getAttrTag("class", str::getAttrArray(
			$settings['class'] ?? NULL,
			[
				"status-badge",
				$colour ? "status-badge--{$colour}" : NULL,
				$indicator ? "status-badge--indicator-{$indicator}" : NULL,
				$surface ? "status-badge--surface-{$surface}" : NULL,
			],
			$settings['only_class'] ?? NULL
		));
		$style = str::getAttrTag("style", str::getAttrArray(
			$settings['style'] ?? NULL,
			NULL,
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
		$attributes = self::getAttributes($settings['attributes'] ?? NULL);
		$title = ($settings['tooltip'] ?? NULL)
			? NULL
			: str::getAttrTag("title", strip_tags((string)($settings['alt'] ?? "")));

		if($indicator === "dot"){
			$icon_html = '<span class="status-badge__icon status-badge__dot" aria-hidden="true"></span>';
		}
		else{
			$icon_html = Icon::generate($icon);
			$icon_html = $icon_html
				? "<span class=\"status-badge__icon\">{$icon_html}</span>"
				: "";
		}
		$text_html = "";
		if($text !== NULL){
			$text = ($settings['html'] ?? false)
				? $text
				: htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
			$text_html = "<span class=\"status-badge__text\">{$text}</span>";
		}

		return "<{$tag}{$id}{$class}{$style}{$title}{$data}{$target}{$rel}{$role}{$tabindex}{$dir}{$aria}{$attributes}{$link}>"
			. $icon_html
			. $text_html
			. "</{$tag}>";
	}

	/**
	 * Apply an icon override from the settings array to the primary icon argument.
	 *
	 * A string override replaces the original icon completely. An array override
	 * is recursively merged over the original icon configuration, allowing a
	 * caller to retain the helper's default icon name while changing properties
	 * such as Font Awesome weight, classes, transforms, or accessibility text.
	 * Unsupported override types are ignored and the original icon is returned.
	 * When the primary icon is NULL, an override may supply the complete icon.
	 *
	 * @param array<string,mixed>|string|null $icon      Original icon name or configuration.
	 * @param mixed                           $overrides Optional override supplied through `settings.icon`.
	 *
	 * @return array<string,mixed>|string|null The resolved icon passed to Icon::generate().
	 */
	private static function applyIconOverrides(array|string|null $icon, mixed $overrides): array|string|null
	{
		if(!$overrides){
			return $icon;
		}

		if(is_string($overrides)){
			return $overrides;
		}

		if(!is_array($overrides)){
			return $icon;
		}

		if(!is_array($icon)){
			$icon = $icon !== NULL && $icon !== ""
				? ["name" => $icon]
				: [];
		}

		return array_replace_recursive($icon, $overrides);
	}

	/**
	 * Resolve and validate the root HTML tag.
	 *
	 * When no explicit tag is provided, linked badges use an anchor and all
	 * other badges use a span. Explicit tag names are restricted to a safe HTML
	 * name pattern so a malformed setting cannot break the generated markup.
	 * Invalid explicit values gracefully fall back to a span.
	 *
	 * @param string|null $tag     Explicit tag requested through the settings array.
	 * @param bool        $is_link Whether href::generate() produced link attributes.
	 *
	 * @return string A safe lowercase tag name without angle brackets.
	 */
	private static function getTag(?string $tag, bool $is_link): string
	{
		$tag = strtolower(trim((string)$tag));
		if(!$tag){
			return $is_link ? "a" : "span";
		}

		return preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : "span";
	}

	/**
	 * Generate link attributes only when link-related settings are present.
	 *
	 * Avoiding an unconditional href::generate() call keeps an ordinary status
	 * badge as a non-interactive element. The returned string may contain an
	 * href and/or onclick attribute depending on the common href rules.
	 *
	 * @param array<string,mixed> $settings Resolved status-badge settings.
	 *
	 * @return string|null Generated link attributes, or NULL for a non-link badge.
	 */
	private static function getLink(array $settings): ?string
	{
		$link_keys = ["url", "uri", "href", "hash", "onclick", "onClick", "div"];
		$has_link = array_any($link_keys, static function(string $key) use ($settings): bool {
			return isset($settings[$key]) && $settings[$key] !== "";
		});

		return $has_link ? href::generate($settings) : NULL;
	}

	/**
	 * Convert a caller-supplied colour into a safe CSS modifier suffix.
	 *
	 * Colour names are lowercased, surrounding whitespace is removed, and
	 * unsupported characters are converted to hyphens. NULL or an empty result
	 * returns NULL so callers can intentionally render the base badge without a
	 * colour modifier. The method deliberately returns a class suffix rather than
	 * inline colour CSS so visual rules remain in the SCSS component.
	 *
	 * @param string|null $colour Optional theme colour or custom modifier name.
	 *
	 * @return string|null Normalised value used in `status-badge--{colour}`.
	 */
	private static function normaliseColour(?string $colour): ?string
	{
		$colour = strtolower(trim((string)$colour));
		$colour = trim((string)preg_replace('/[^a-z0-9_-]+/', '-', $colour), "-");

		return $colour ?: NULL;
	}

	/**
	 * Convert an optional component modifier into a safe CSS class suffix.
	 *
	 * Unlike normaliseColour(), this method deliberately returns an empty string
	 * for an empty value. Optional indicator and surface modifiers can therefore
	 * be omitted without adding a meaningless fallback class to the badge.
	 *
	 * @param string $value Optional modifier supplied by the caller.
	 *
	 * @return string Safe lowercase modifier, or an empty string when omitted.
	 */
	private static function normaliseModifier(string $value): string
	{
		$value = strtolower(trim($value));
		return trim((string)preg_replace('/[^a-z0-9_-]+/', '-', $value), "-");
	}

	/**
	 * Convert an associative array into consistently prefixed HTML attributes.
	 *
	 * This is primarily used for the `aria` setting, where `label` becomes
	 * `aria-label` and `live` becomes `aria-live`. Attribute names and values are
	 * normalised before being handed to the common attribute formatter.
	 *
	 * @param string                   $prefix     Attribute prefix without the trailing hyphen.
	 * @param array<string,mixed>|null $attributes Attribute suffixes and their values.
	 *
	 * @return string|null Concatenated HTML attributes, or NULL when none were supplied.
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
	 * Generate caller-defined HTML attributes from the `attributes` setting.
	 *
	 * Attributes already handled by first-class settings are excluded to avoid
	 * duplicate output. ARIA attributes are also excluded here because callers
	 * should provide them through the dedicated `aria` setting. Invalid names are
	 * silently ignored. A TRUE value keeps a boolean-style attribute present.
	 *
	 * @param array<string,mixed>|null $attributes Explicit attribute names and values.
	 *
	 * @return string|null Concatenated HTML attributes, or NULL when none are valid.
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
	 * Validate and normalise a caller-defined HTML attribute name.
	 *
	 * Names are lowercased and must begin with a letter. Subsequent characters
	 * may contain letters, numbers, underscores, periods, colons, or hyphens.
	 * Returning an empty string signals that the attribute must be discarded.
	 *
	 * @param string $name Untrusted attribute name supplied by the caller.
	 *
	 * @return string Safe lowercase attribute name, or an empty string when invalid.
	 */
	private static function normaliseAttributeName(string $name): string
	{
		$name = strtolower(trim($name));
		return preg_match('/^[a-z][a-z0-9_.:-]*$/', $name) ? $name : "";
	}

	/**
	 * Convert non-scalar attribute values into an HTML-friendly representation.
	 *
	 * Booleans become the literal strings `true` or `false`, arrays are JSON
	 * encoded, and scalar/null values pass through unchanged. Final quote
	 * escaping is performed by str::getAttrTag().
	 *
	 * @param mixed $value Attribute value supplied by the caller.
	 *
	 * @return mixed Normalised attribute value suitable for the common formatter.
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
