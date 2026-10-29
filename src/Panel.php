<?php

namespace App\UI;

use App\Common\str;
use Stringable;

/**
 * Renders a lightweight, self-contained content section.
 *
 * A Panel sits between a Card and a ListGroup item: it has one continuous
 * surface and border, a compact heading containing an optional padded icon and
 * required title, an unrestricted body, and an optional footer. Header, body,
 * and footer are deliberately not separated unless `footer_separator` is TRUE.
 *
 * Panel is content-agnostic. Its body and footer can contain DataPoint matrices,
 * StatusBadges, tables, forms, messages, images, or any other rendered HTML.
 * Presentation belongs to `_panel.scss`; this class emits semantic component
 * classes and applies only caller-supplied common element overrides.
 *
 * Basic usage:
 * <code>
 * Panel::generate("Next accounts", DataPoint::matrix($data_points), [
 *     "icon" => "calendar-check",
 * ]);
 * </code>
 */
class Panel
{
	private const DEFAULT_COLOUR = "primary";

	/**
	 * Render a panel with a title, arbitrary body, and optional footer.
	 *
	 * Content composition:
	 * - `$body` and `footer` accept trusted HTML strings, scalar values,
	 *   Stringable objects, and nested arrays containing any of those values.
	 * - Array fragments are flattened recursively in their supplied order and
	 *   concatenated without an automatic separator or wrapper.
	 * - NULL and FALSE fragments are omitted, making conditional composition
	 *   straightforward. Unsupported objects and resources are also ignored.
	 * - Body and footer HTML are trusted and are not escaped. Components should
	 *   escape their own untrusted service values before being passed here.
	 *
	 * Component settings:
	 * - `icon` (`string|array|false|null`): Optional Icon name or complete
	 *   Icon::generate() settings. FALSE/NULL removes the icon and its padded
	 *   wrapper. The icon array can contain type, colour, tooltip, class, and any
	 *   other settings supported by Icon.
	 * - `footer`: Optional footer content accepting the same content types as the
	 *   body. An empty footer is omitted completely.
	 * - `footer_separator` (`bool`): Adds a subtle divider and extra inset above
	 *   the footer. Defaults to FALSE so all regions share one uninterrupted
	 *   surface.
	 * - `stretch` (`bool`): Makes the panel fill the available block-size of its
	 *   parent or grid cell. This is useful when sibling panels should have equal
	 *   heights without a service-specific height class. Defaults to FALSE.
	 * - `colour` (`string`): Theme colour for the icon treatment. Defaults to
	 *   `primary`. It becomes a safe `ui-panel--{colour}` modifier; theme colours
	 *   are styled by `_panel.scss`, while custom modifiers can be styled by the
	 *   consuming application.
	 * - `title_html` (`bool`): Render trusted HTML in the title. Defaults to FALSE,
	 *   which escapes the title with htmlspecialchars().
	 * - `heading_tag` (`string`): Semantic title tag from `h1` through `h6` or
	 *   `div`. Defaults to `h3`.
	 * - `rtl` (`bool`): Emits `dir="rtl"`; logical spacing automatically places
	 *   the optional icon and content correctly for right-to-left languages.
	 *
	 * Root element settings:
	 * - `tag`: Root tag; defaults to `section`.
	 * - `id`, `class`, `only_class`, `style`, `only_style`, and `data`.
	 * - `alt`: Native title text when no Bootstrap tooltip is configured.
	 * - `tooltip`: Boolean, string, or Tooltip configuration array.
	 * - `role`, `tabindex`, `aria`, and explicitly named `attributes`.
	 *
	 * Region settings:
	 * - `header_settings`, `title_settings`, `body_settings`, and
	 *   `footer_settings` accept the common `id`, `class`, `only_class`, `style`,
	 *   `only_style`, `data`, `aria`, and `attributes` element overrides.
	 * - Region settings affect only their named wrapper and never alter content.
	 *
	 * Complete example:
	 * <code>
	 * Panel::generate("Verification results", [
	 *     DataPoint::generate("Name", "Example Limited"),
	 *     $additional_html,
	 * ], [
	 *     "icon" => ["name" => "shield-check", "type" => "solid"],
	 *     "colour" => "green",
	 *     "footer" => StatusBadge::positive("Verified"),
	 *     "footer_separator" => TRUE,
	 *     "class" => "verification-panel",
	 *     "body_settings" => ["class" => "verification-panel__content"],
	 * ]);
	 * </code>
	 *
	 * @param string              $title    Required user-facing panel title.
	 * @param mixed               $body     Trusted body content or nested rendered fragments.
	 * @param array<string,mixed> $settings Component, content, and element settings.
	 *
	 * @return string Complete panel HTML.
	 */
	public static function generate(string $title, mixed $body, array $settings = []): string
	{
		$footer = self::renderContent($settings['footer'] ?? NULL);
		$colour = self::normaliseModifier((string)($settings['colour'] ?? self::DEFAULT_COLOUR), self::DEFAULT_COLOUR);
		$classes = [
			"ui-panel",
			"ui-panel--{$colour}",
			($settings['stretch'] ?? false) ? "ui-panel--stretch" : NULL,
			$footer && ($settings['footer_separator'] ?? false) ? "ui-panel--footer-separated" : NULL,
		];

		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$tag = self::normaliseTag((string)($settings['tag'] ?? "section"), "section");
		$attributes = self::elementAttributes($settings, $classes, true);
		$header_attributes = self::elementAttributes(
			self::regionSettings($settings, 'header_settings'),
			["ui-panel__header"]
		);
		$title_attributes = self::elementAttributes(
			self::regionSettings($settings, 'title_settings'),
			["ui-panel__title"]
		);
		$body_attributes = self::elementAttributes(
			self::regionSettings($settings, 'body_settings'),
			["ui-panel__body"]
		);

		$heading_tag = self::normaliseHeadingTag((string)($settings['heading_tag'] ?? "h3"));
		$title_html = ($settings['title_html'] ?? false)
			? $title
			: htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
		$icon_html = self::renderIcon($settings['icon'] ?? NULL);
		$body_html = self::renderContent($body);
		$footer_html = "";

		if($footer !== ""){
			$footer_attributes = self::elementAttributes(
				self::regionSettings($settings, 'footer_settings'),
				["ui-panel__footer"]
			);
			$footer_html = "<footer{$footer_attributes}>{$footer}</footer>";
		}

		return <<<HTML
<{$tag}{$attributes}>
    <header{$header_attributes}>
        {$icon_html}
        <{$heading_tag}{$title_attributes}>{$title_html}</{$heading_tag}>
    </header>
    <div{$body_attributes}>{$body_html}</div>
    {$footer_html}
</{$tag}>
HTML;
	}

	/**
	 * Render the optional icon inside the component-owned padded wrapper.
	 *
	 * @param mixed $icon Icon name, Icon settings array, or a falsey value.
	 *
	 * @return string Wrapped icon HTML, or an empty string.
	 */
	private static function renderIcon(mixed $icon): string
	{
		if(!$icon || (!is_string($icon) && !is_array($icon))){
			return "";
		}

		return '<span class="ui-panel__icon">' . Icon::generate($icon) . '</span>';
	}

	/**
	 * Flatten trusted content fragments into one HTML string.
	 *
	 * @param mixed $content Content supplied for the body or footer.
	 *
	 * @return string Concatenated HTML-ready content.
	 */
	private static function renderContent(mixed $content): string
	{
		if($content === NULL || $content === false){
			return "";
		}

		if(is_array($content)){
			return implode("", array_map([self::class, 'renderContent'], $content));
		}

		if(is_string($content) || is_numeric($content)){
			return (string)$content;
		}

		if($content === true){
			return "true";
		}

		return $content instanceof Stringable ? (string)$content : "";
	}

	/**
	 * Return a named region's settings, ignoring unsupported values.
	 *
	 * @param array<string,mixed> $settings Panel settings.
	 * @param string              $key      Region-settings key.
	 *
	 * @return array<string,mixed> Region element settings.
	 */
	private static function regionSettings(array $settings, string $key): array
	{
		return is_array($settings[$key] ?? NULL) ? $settings[$key] : [];
	}

	/**
	 * Generate attributes shared by the root and its semantic regions.
	 *
	 * @param array<string,mixed> $settings      Element settings.
	 * @param array<int,string>   $default_class Component classes.
	 * @param bool                $root          Whether root-only settings apply.
	 *
	 * @return string Concatenated element attributes.
	 */
	private static function elementAttributes(array $settings, array $default_class, bool $root = false): string
	{
		$class = str::getAttrTag("class", str::getAttrArray(
			$settings['class'] ?? NULL,
			$default_class,
			$settings['only_class'] ?? NULL
		));
		$style = str::getAttrTag("style", str::getAttrArray(
			$settings['style'] ?? NULL,
			NULL,
			$settings['only_style'] ?? NULL
		));
		$id = str::getAttrTag("id", $settings['id'] ?? NULL);
		$data = str::getDataAttr($settings['data'] ?? NULL);
		$role = str::getAttrTag("role", $settings['role'] ?? NULL);
		$tabindex = str::getAttrTag("tabindex", $settings['tabindex'] ?? NULL);
		$dir = $root && ($settings['rtl'] ?? false) ? str::getAttrTag("dir", "rtl") : NULL;
		$aria = self::prefixedAttributes("aria", $settings['aria'] ?? NULL);
		$other = self::customAttributes($settings['attributes'] ?? NULL);
		$title = $root && !($settings['tooltip'] ?? NULL)
			? str::getAttrTag("title", strip_tags((string)($settings['alt'] ?? "")))
			: NULL;

		return "{$id}{$class}{$style}{$title}{$data}{$role}{$tabindex}{$dir}{$aria}{$other}";
	}

	/**
	 * Convert a theme/custom name into a safe CSS modifier suffix.
	 */
	private static function normaliseModifier(string $value, string $fallback): string
	{
		$value = strtolower(trim($value));
		$value = trim((string)preg_replace('/[^a-z0-9_-]+/', '-', $value), "-");
		return $value ?: $fallback;
	}

	/**
	 * Resolve a safe root tag, falling back to the supplied semantic default.
	 */
	private static function normaliseTag(string $tag, string $fallback): string
	{
		$tag = strtolower(trim($tag));
		return $tag && preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : $fallback;
	}

	/**
	 * Restrict panel heading overrides to semantic heading elements or a div.
	 */
	private static function normaliseHeadingTag(string $tag): string
	{
		$tag = strtolower(trim($tag));
		return in_array($tag, ["h1", "h2", "h3", "h4", "h5", "h6", "div"], true) ? $tag : "h3";
	}

	/**
	 * Convert an associative array into consistently prefixed attributes.
	 */
	private static function prefixedAttributes(string $prefix, mixed $attributes): string
	{
		if(!is_array($attributes)){
			return "";
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
	 * Generate caller-defined attributes without duplicating core attributes.
	 */
	private static function customAttributes(mixed $attributes): string
	{
		if(!is_array($attributes)){
			return "";
		}

		$reserved = ["id", "class", "style", "title", "data", "role", "tabindex", "dir"];
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
