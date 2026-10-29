<?php

namespace App\UI;

use App\Common\str;
use Stringable;

/**
 * Arranges two arbitrary content regions as a reusable responsive split view.
 *
 * SplitView is intentionally content-agnostic. Either pane may contain media,
 * data points, forms, cards, tables, or any other rendered HTML. Presentation
 * is owned by `_split-view.scss`; callers select semantic layout modifiers and
 * never need to embed component CSS in a service renderer.
 */
class SplitView
{
	/**
	 * Render two adjacent panes that collapse into a vertical layout.
	 *
	 * Layout settings:
	 * - `ratio`: `equal` (default), `1-2`, or `2-1`.
	 * - `gap`: `none` (default), `sm`, `md`, or `lg`.
	 * - `collapse_at`: `sm`, `md`, `lg` (default), `xl`, `xxl`, or FALSE.
	 * - `height`: Optional shared minimum height: `sm`, `md`, `lg`, or `xl`.
	 * - `reverse_on_collapse`: Displays the second pane before the first after
	 *   collapse without changing the source order. Defaults to FALSE.
	 * - `rtl`: Emits `dir="rtl"`; logical CSS keeps pane flow appropriate.
	 *
	 * Pane settings are supplied through `first_settings` and `second_settings`:
	 * - `padding`: `none` (default), `sm`, `md`, or `lg`.
	 * - `surface`: `none` (default), `white`, `soft`, or `muted`.
	 * - `align`: `start` (default), `center`, `end`, or `stretch`.
	 * - `overflow`: `visible` (default), `hidden`, or `auto`.
	 * - Common `id`, `class`, `only_class`, `style`, `only_style`, `data`,
	 *   `role`, `tabindex`, `aria`, and custom `attributes` overrides.
	 *
	 * Root settings additionally support `tag` (defaults to `section`), `alt`,
	 * `tooltip`, and the same common element overrides. Both content arguments
	 * accept trusted strings, scalars, Stringable objects, and nested arrays.
	 * NULL and FALSE fragments are omitted.
	 *
	 * @param mixed               $first    Trusted content for the first pane.
	 * @param mixed               $second   Trusted content for the second pane.
	 * @param array<string,mixed> $settings Layout, pane, and element settings.
	 *
	 * @return string Complete split-view HTML.
	 */
	public static function generate(mixed $first, mixed $second, array $settings = []): string
	{
		$ratio = self::option($settings['ratio'] ?? "equal", ["equal", "1-2", "2-1"], "equal");
		$gap = self::option($settings['gap'] ?? "none", ["none", "sm", "md", "lg"], "none");
		$height = self::option($settings['height'] ?? "", ["sm", "md", "lg", "xl"], "");
		$collapse = array_key_exists('collapse_at', $settings) && $settings['collapse_at'] === false
			? ""
			: self::option($settings['collapse_at'] ?? "lg", ["sm", "md", "lg", "xl", "xxl"], "lg");

		$classes = [
			"ui-split-view",
			"ui-split-view--ratio-{$ratio}",
			"ui-split-view--gap-{$gap}",
			$height ? "ui-split-view--height-{$height}" : NULL,
			$collapse ? "ui-split-view--collapse-{$collapse}" : NULL,
			($settings['reverse_on_collapse'] ?? false) ? "ui-split-view--reverse-on-collapse" : NULL,
		];

		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$tag = self::tag($settings['tag'] ?? "section", "section");
		$attributes = self::attributes($settings, $classes, true);
		$first_attributes = self::paneAttributes(self::paneSettings($settings, 'first_settings'), "first");
		$second_attributes = self::paneAttributes(self::paneSettings($settings, 'second_settings'), "second");
		$first = self::content($first);
		$second = self::content($second);

		return <<<HTML
<{$tag}{$attributes}>
    <div{$first_attributes}>{$first}</div>
    <div{$second_attributes}>{$second}</div>
</{$tag}>
HTML;
	}

	/** Generate classes and common attributes for one pane. */
	private static function paneAttributes(array $settings, string $position): string
	{
		$padding = self::option($settings['padding'] ?? "none", ["none", "sm", "md", "lg"], "none");
		$surface = self::option($settings['surface'] ?? "none", ["none", "white", "soft", "muted"], "none");
		$align = self::option($settings['align'] ?? "start", ["start", "center", "end", "stretch"], "start");
		$overflow = self::option($settings['overflow'] ?? "visible", ["visible", "hidden", "auto"], "visible");

		return self::attributes($settings, [
			"ui-split-view__pane",
			"ui-split-view__pane--{$position}",
			"ui-split-view__pane--padding-{$padding}",
			"ui-split-view__pane--surface-{$surface}",
			"ui-split-view__pane--align-{$align}",
			"ui-split-view__pane--overflow-{$overflow}",
		]);
	}

	private static function paneSettings(array $settings, string $key): array
	{
		return is_array($settings[$key] ?? NULL) ? $settings[$key] : [];
	}

	private static function content(mixed $content): string
	{
		if($content === NULL || $content === false){
			return "";
		}
		if(is_array($content)){
			return implode("", array_map([self::class, 'content'], $content));
		}
		if(is_string($content) || is_numeric($content)){
			return (string)$content;
		}
		if($content === true){
			return "true";
		}
		return $content instanceof Stringable ? (string)$content : "";
	}

	private static function attributes(array $settings, array $classes, bool $root = false): string
	{
		$class = str::getAttrTag("class", str::getAttrArray($settings['class'] ?? NULL, $classes, $settings['only_class'] ?? NULL));
		$style = str::getAttrTag("style", str::getAttrArray($settings['style'] ?? NULL, NULL, $settings['only_style'] ?? NULL));
		$id = str::getAttrTag("id", $settings['id'] ?? NULL);
		$data = str::getDataAttr($settings['data'] ?? NULL);
		$role = str::getAttrTag("role", $settings['role'] ?? NULL);
		$tabindex = str::getAttrTag("tabindex", $settings['tabindex'] ?? NULL);
		$dir = $root && ($settings['rtl'] ?? false) ? str::getAttrTag("dir", "rtl") : NULL;
		$title = $root && !($settings['tooltip'] ?? NULL) ? str::getAttrTag("title", strip_tags((string)($settings['alt'] ?? ""))) : NULL;
		$aria = self::namedAttributes("aria", $settings['aria'] ?? NULL);
		$custom = self::customAttributes($settings['attributes'] ?? NULL);

		return "{$id}{$class}{$style}{$title}{$data}{$role}{$tabindex}{$dir}{$aria}{$custom}";
	}

	private static function namedAttributes(string $prefix, mixed $values): string
	{
		if(!is_array($values)) return "";
		$html = [];
		foreach($values as $name => $value){
			if($name = self::attributeName((string)$name)){
				$html[] = str::getAttrTag("{$prefix}-{$name}", self::attributeValue($value));
			}
		}
		return implode("", array_filter($html));
	}

	private static function customAttributes(mixed $values): string
	{
		if(!is_array($values)) return "";
		$html = [];
		foreach($values as $name => $value){
			$name = self::attributeName((string)$name);
			if($name && !in_array($name, ["id", "class", "style", "title", "data", "role", "tabindex", "dir"], true)){
				$html[] = str::getAttrTag($name, self::attributeValue($value), $value === true);
			}
		}
		return implode("", array_filter($html));
	}

	private static function attributeName(string $name): string
	{
		$name = strtolower(trim($name));
		return preg_match('/^[a-z][a-z0-9_.:-]*$/', $name) ? $name : "";
	}

	private static function attributeValue(mixed $value): mixed
	{
		return is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? "true" : "false") : $value);
	}

	private static function option(mixed $value, array $allowed, string $fallback): string
	{
		$value = strtolower(trim((string)$value));
		return in_array($value, $allowed, true) ? $value : $fallback;
	}

	private static function tag(mixed $tag, string $fallback): string
	{
		$tag = strtolower(trim((string)$tag));
		return $tag && preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : $fallback;
	}
}
