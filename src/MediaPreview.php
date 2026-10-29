<?php

namespace App\UI;

use App\Common\href;
use App\Common\Img;
use App\Common\str;
use Stringable;

/**
 * Renders media or an informative fallback on a reusable preview surface.
 *
 * The component is suitable for maps, photographs, document thumbnails,
 * signatures, scans, videos, and arbitrary embedded media. It can make the
 * complete preview a link while retaining a clean no-media fallback.
 */
class MediaPreview
{
	/**
	 * Render an image, arbitrary media fragment, or fallback state.
	 *
	 * Content settings:
	 * - `src`: Image URL passed to Img::generate().
	 * - `fallback_src`: Optional image URL applied by the shared browser error
	 *   handler if the primary `src` cannot be loaded. This is useful when both
	 *   sources should load independently of the PHP response.
	 * - `media`: Trusted arbitrary media HTML. When present it takes precedence
	 *   over `src`, allowing an iframe, video, canvas, SVG, or another component.
	 * - `image_settings`: Extra Img::generate() settings such as data, width, or
	 *   height. Component classes, src, and alt remain authoritative.
	 * - `alt`: Alternative text for the image. Defaults to an empty string.
	 * - `icon`: Fallback Icon name or complete Icon settings. Defaults to `image`.
	 * - `title` / `subtitle`: Fallback copy. Title defaults to "Preview
	 *   unavailable". Subtitle is optional.
	 * - `html`: Allows trusted HTML in fallback copy. The default is FALSE and
	 *   both fallback strings are escaped.
	 *
	 * Presentation settings:
	 * - `fit`: `cover` (default) or `contain`.
	 * - `height`: `auto` (default), `sm`, `md`, `lg`, or `xl`.
	 * - `surface`: `muted` (default), `soft`, `dark`, or `white`.
	 * - `zoom`: Gently scales image/media on hover. Defaults to FALSE.
	 * - `stretch`: Fills the parent's width and height. Defaults to FALSE.
	 *
	 * Link settings (`url`, `uri`, `href`, `hash`, `onclick`, `onClick`, or
	 * `div`) are handled by the common href helper and turn the whole preview
	 * into one anchor. `target` and `rel` are also supported. Root settings
	 * include `tag`, `id`, `class`, `only_class`, `style`, `only_style`, `data`,
	 * `tooltip`, `root_alt`, `role`, `tabindex`, `rtl`, `aria`, and explicitly
	 * named `attributes`.
	 *
	 * @param array<string,mixed> $settings Media, fallback, link, and element settings.
	 *
	 * @return string Complete media-preview HTML.
	 */
	public static function generate(array $settings = []): string
	{
		$fit = self::option($settings['fit'] ?? "cover", ["cover", "contain"], "cover");
		$height = self::option($settings['height'] ?? "auto", ["auto", "sm", "md", "lg", "xl"], "auto");
		$surface = self::option($settings['surface'] ?? "muted", ["muted", "soft", "dark", "white"], "muted");
		$classes = [
			"media-preview",
			"media-preview--fit-{$fit}",
			"media-preview--height-{$height}",
			"media-preview--surface-{$surface}",
			($settings['zoom'] ?? false) ? "media-preview--zoom" : NULL,
			($settings['stretch'] ?? false) ? "media-preview--stretch" : NULL,
		];

		if($settings['tooltip'] ?? NULL){
			Tooltip::generate($settings);
		}

		$link = self::link($settings);
		$tag = self::tag($settings['tag'] ?? NULL, $link ? "a" : "figure");
		$attributes = self::attributes($settings, $classes, (bool)$link) . $link;
		$content = self::previewContent($settings);

		return "<{$tag}{$attributes}>{$content}</{$tag}>";
	}

	/** Choose arbitrary media, an image, or the fallback in that order. */
	private static function previewContent(array $settings): string
	{
		$media = self::content($settings['media'] ?? NULL);
		if($media !== ""){
			return '<span class="media-preview__media">' . $media . '</span>';
		}

		if($settings['src'] ?? NULL){
			$image = is_array($settings['image_settings'] ?? NULL) ? $settings['image_settings'] : [];
			// Img predates null-coalescing and reads its optional keys directly.
			// Supplying the complete optional shape keeps this component warning-free.
			$image = array_replace([
				"svg" => NULL,
				"img" => NULL,
				"contents" => NULL,
				"id" => NULL,
				"type" => NULL,
				"style" => NULL,
				"default_style" => NULL,
				"only_style" => NULL,
				"title" => NULL,
				"width" => NULL,
				"height" => NULL,
				"border" => NULL,
				"data" => NULL,
			], $image);
			$image['src'] = $settings['src'];
			$image['alt'] = (string)($settings['alt'] ?? "");
			$image['alt_if_null'] = true;
			$image['class'] = str::getAttrArray($image['class'] ?? NULL, "media-preview__image");
			if($settings['fallback_src'] ?? NULL){
				$image['data'] = array_replace(
					is_array($image['data'] ?? NULL) ? $image['data'] : [],
					["media-preview-fallback-src" => (string)$settings['fallback_src']]
				);
			}
			return Img::generate($image) ?? "";
		}

		$title = (string)($settings['title'] ?? "Preview unavailable");
		$subtitle = (string)($settings['subtitle'] ?? "");
		if(!($settings['html'] ?? false)){
			$title = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
			$subtitle = htmlspecialchars($subtitle, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
		}
		$icon = $settings['icon'] ?? "image";
		$icon = $icon && (is_string($icon) || is_array($icon))
			? '<span class="media-preview__fallback-icon">' . Icon::generate($icon) . '</span>'
			: "";
		$subtitle = $subtitle !== "" ? "<small class=\"media-preview__fallback-subtitle\">{$subtitle}</small>" : "";

		return <<<HTML
<span class="media-preview__fallback">
    {$icon}
    <strong class="media-preview__fallback-title">{$title}</strong>
    {$subtitle}
</span>
HTML;
	}

	private static function content(mixed $content): string
	{
		if($content === NULL || $content === false) return "";
		if(is_array($content)) return implode("", array_map([self::class, 'content'], $content));
		if(is_string($content) || is_numeric($content)) return (string)$content;
		if($content === true) return "true";
		return $content instanceof Stringable ? (string)$content : "";
	}

	private static function link(array $settings): string
	{
		foreach(["url", "uri", "href", "hash", "onclick", "onClick", "div"] as $key){
			if(isset($settings[$key]) && $settings[$key] !== "") return href::generate($settings) ?? "";
		}
		return "";
	}

	private static function attributes(array $settings, array $classes, bool $linked): string
	{
		$class = str::getAttrTag("class", str::getAttrArray($settings['class'] ?? NULL, $classes, $settings['only_class'] ?? NULL));
		$style = str::getAttrTag("style", str::getAttrArray($settings['style'] ?? NULL, NULL, $settings['only_style'] ?? NULL));
		$id = str::getAttrTag("id", $settings['id'] ?? NULL);
		$data = str::getDataAttr($settings['data'] ?? NULL);
		$target = $linked ? str::getAttrTag("target", $settings['target'] ?? NULL) : NULL;
		$rel = $linked ? str::getAttrTag("rel", $settings['rel'] ?? NULL) : NULL;
		$role = str::getAttrTag("role", $settings['role'] ?? NULL);
		$tabindex = str::getAttrTag("tabindex", $settings['tabindex'] ?? NULL);
		$dir = ($settings['rtl'] ?? false) ? str::getAttrTag("dir", "rtl") : NULL;
		$title = !($settings['tooltip'] ?? NULL) ? str::getAttrTag("title", strip_tags((string)($settings['root_alt'] ?? ""))) : NULL;
		$aria = self::namedAttributes("aria", $settings['aria'] ?? NULL);
		$custom = self::customAttributes($settings['attributes'] ?? NULL);

		return "{$id}{$class}{$style}{$title}{$data}{$target}{$rel}{$role}{$tabindex}{$dir}{$aria}{$custom}";
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
			if($name && !in_array($name, ["id", "class", "style", "title", "data", "target", "rel", "role", "tabindex", "dir"], true)){
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
