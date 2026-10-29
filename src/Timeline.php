<?php

namespace App\UI;

use App\Common\str;
use Stringable;

/**
 * Renders a vertical sequence of content cards connected by visual markers.
 *
 * Timeline does not interpret dates or business states. Callers provide the
 * ordered items and any rendered fragments required in each region.
 */
class Timeline
{
	/**
	 * Render an ordered vertical timeline.
	 *
	 * Each item may be a trusted HTML/scalar fragment (used as its body), or an
	 * associative definition with these regions:
	 * - `title`: Primary item heading.
	 * - `meta`: Supporting text such as a date range.
	 * - `body`: Arbitrary main content.
	 * - `aside`: Content aligned opposite the title, such as a comparison icon.
	 * - `footer`: Arbitrary content below the main body.
	 * - `html`: Alias for `body` when `body` is absent.
	 * - `marker`: Arbitrary marker content. When omitted, a simple dot is used;
	 *   set to FALSE to leave the marker visually empty.
	 * - `icon`: Convenience Icon name/settings used as marker content when an
	 *   explicit `marker` was not supplied.
	 * - `colour`: Theme modifier for the marker. Defaults to the root colour.
	 * - `settings`: Optional item wrapper settings. Common item settings may also
	 *   be placed directly in the definition; nested settings take precedence.
	 *
	 * All regions accept trusted strings, scalars, Stringable objects, or nested
	 * arrays of those fragments. No business data is escaped automatically.
	 *
	 * Root settings:
	 * - `colour`: Default theme colour; defaults to `primary`.
	 * - `compact`: Reduces vertical spacing when TRUE.
	 * - `tag`: Root tag; defaults to `ol`.
	 * - `item_tag`: Item tag; defaults to `li`.
	 * - `id`, `class`, `only_class`, `style`, `only_style`, `data`, `alt`,
	 *   `tooltip`, `role`, `tabindex`, `rtl`, `aria`, and custom `attributes`.
	 *
	 * Item wrapper settings support `id`, `class`, `only_class`, `style`,
	 * `only_style`, `data`, `role`, `tabindex`, `aria`, and `attributes`.
	 *
	 * @param array<int,mixed>    $items    Ordered item fragments or definitions.
	 * @param array<string,mixed> $settings Timeline and root element settings.
	 *
	 * @return string Timeline HTML, or an empty string when no items render.
	 */
	public static function generate(array $items, array $settings = []): string
	{
		$default_colour = self::modifier($settings['colour'] ?? "primary", "primary");
		$item_tag = self::tag($settings['item_tag'] ?? "li", "li");
		$rendered = [];
		foreach($items as $item){
			if($html = self::item($item, $default_colour, $item_tag)){
				$rendered[] = $html;
			}
		}
		if(!$rendered) return "";

		if($settings['tooltip'] ?? NULL) Tooltip::generate($settings);
		$classes = [
			"ui-timeline",
			"ui-timeline--{$default_colour}",
			($settings['compact'] ?? false) ? "ui-timeline--compact" : NULL,
		];
		$tag = self::tag($settings['tag'] ?? "ol", "ol");
		$attributes = self::attributes($settings, $classes, true);

		return "<{$tag}{$attributes}>" . implode("", $rendered) . "</{$tag}>";
	}

	/** Render one timeline definition. */
	private static function item(mixed $item, string $default_colour, string $tag): string
	{
		if(!is_array($item) || str::isNumericArray($item)){
			$item = ["body" => $item];
		}

		$title = self::content($item['title'] ?? NULL);
		$meta = self::content($item['meta'] ?? NULL);
		$body = self::content($item['body'] ?? ($item['html'] ?? NULL));
		$aside = self::content($item['aside'] ?? NULL);
		$footer = self::content($item['footer'] ?? NULL);
		if($title === "" && $meta === "" && $body === "" && $aside === "" && $footer === "") return "";

		$colour = self::modifier($item['colour'] ?? $default_colour, $default_colour);
		$item_settings = is_array($item['settings'] ?? NULL) ? array_replace($item, $item['settings']) : $item;
		$attributes = self::attributes($item_settings, ["ui-timeline__item", "ui-timeline__item--{$colour}"]);
		$marker = self::marker($item);
		$title = $title !== "" ? "<div class=\"ui-timeline__title\">{$title}</div>" : "";
		$aside = $aside !== "" ? "<div class=\"ui-timeline__aside\">{$aside}</div>" : "";
		$head = ($title !== "" || $aside !== "") ? "<div class=\"ui-timeline__head\">{$title}{$aside}</div>" : "";
		$meta = $meta !== "" ? "<div class=\"ui-timeline__meta\">{$meta}</div>" : "";
		$body = $body !== "" ? "<div class=\"ui-timeline__body\">{$body}</div>" : "";
		$footer = $footer !== "" ? "<footer class=\"ui-timeline__footer\">{$footer}</footer>" : "";

		return <<<HTML
<{$tag}{$attributes}>
    <div class="ui-timeline__rail"><span class="ui-timeline__marker">{$marker}</span></div>
    <div class="ui-timeline__content">{$head}{$meta}{$body}{$footer}</div>
</{$tag}>
HTML;
	}

	/** Resolve explicit marker content, an Icon, or the component's CSS dot. */
	private static function marker(array $item): string
	{
		if(array_key_exists('marker', $item)){
			return $item['marker'] === false ? "" : self::content($item['marker']);
		}
		if($item['icon'] ?? NULL){
			return (is_string($item['icon']) || is_array($item['icon'])) ? (Icon::generate($item['icon']) ?? "") : "";
		}
		return '<span class="ui-timeline__dot"></span>';
	}

	private static function content(mixed $content): string
	{
		if($content === NULL || $content === false) return "";
		if(is_array($content)) return implode("", array_map([self::class, 'content'], $content));
		if(is_string($content) || is_numeric($content)) return (string)$content;
		if($content === true) return "true";
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
			$name = self::attributeName((string)$name);
			if($name) $html[] = str::getAttrTag("{$prefix}-{$name}", self::attributeValue($value));
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

	private static function modifier(mixed $value, string $fallback): string
	{
		$value = strtolower(trim((string)$value));
		$value = trim((string)preg_replace('/[^a-z0-9_-]+/', '-', $value), '-');
		return $value ?: $fallback;
	}

	private static function tag(mixed $tag, string $fallback): string
	{
		$tag = strtolower(trim((string)$tag));
		return $tag && preg_match('/^[a-z][a-z0-9-]*$/', $tag) ? $tag : $fallback;
	}
}
