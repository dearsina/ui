<?php


namespace App\UI;


use App\Common\href;
use App\Common\str;
use Exception;

/**
 * Class Accordion
 *
 * Generate an accordion piece, with one or many collapsable elements.
 *
 * @package App\UI
 */
class Accordion {
	/**
	 * Set to true if you want the accordion to be open by default.
	 * @var bool
	 */
	public static ?bool $show = false;

	/**
	 * Expects either an array with a `header` and a `body`,
	 * or an array of arrays containing those elements.
	 * Both the `header` and the `body` can be either strings
	 * or arrays or attributes (id, class, style, etc).
	 * The `header` can also define `ignore_class` as a string
	 * or array of class names that should not trigger the accordion
	 * when clicked.
	 *
	 * <code>
	 * Accordion::generate([
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ]);
	 *
	 * Accordion::generate([[
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ],[
	 *    "header" => "Header",
	 *    "body" => "Body",
	 * ]]);
	 * </code>
	 *
	 * @param array|null $a
	 *
	 * @return bool|string
	 * @throws Exception
	 */
	public static function generate(?array $a): ?string
	{
		if(!is_array($a)){
			return NULL;
		}

		if(!str::isNumericArray($a)){
			//if there is only one accordion pair
			return self::generateCollapsable($a);
		}

		# This is the accordion wrapper ID
		$id = $id ?? str::id("accordion");

        $html = "";
		foreach($a as $collapsable){
			$html .= self::generateCollapsable($collapsable, $id);
		}

		# This is the wrapper class
		$class = str::getAttrTag("class", "accordion");

		$id = str::getAttrTag("id", $id);

		return "<div{$id}{$class}>{$html}</div>";
	}

	/**
	 * Generate one accordion element with a header and a body.
	 *
	 * @param array       $a
	 * @param string|null $parent_id
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function generateCollapsable(array $a, ?string $parent_id = NULL): string
	{
		extract($a);

		# If set to true, allows for the accordion to start open
		self::$show = $show;

		# This ID ties the two pieces together
		if(is_array($body) && $body['id']){
			$id = $body['id'];
		}
		else if(!$id){
			$id = str::id("collapse");
		}

		# If the first character of the ID is a number, prefix it with "id-"
		if(is_numeric(substr($id, 0, 1))){
			$id = "id-{$id}";
		}
		// This is to prevent querySelector from throwing an error
		
		$html .= self::generateHeaderHTML($header, $id);
		$html .= self::generateBodyHTML($body, $id, $parent_id);

		return $html;
	}

	/**
	 * The header of the accordion element.
	 *
	 * @param array|string $a
	 * @param string       $data_target_id The ID of the element to toggle.
	 *
	 * @return string
	 * @throws Exception
	 */
	private static function generateHeaderHTML($a, string $data_target_id): string
	{
		if($a === NULL){
			throw new Exception("A accordion item must have a title of some sort.");
		}
		$a = is_array($a) ? $a : ["title" => $a];
		extract($a);

		# All three words are valid
		$title = $title . $header . $html;
		//TODO Bring together so only one word (header?) is used

		$ignore_selector = self::getIgnoreSelector($ignore_class ?? NULL);
		$header_id = $id ?? NULL;
		if($ignore_selector && !$header_id){
			$header_id = str::id("accordion-header");
		}

		$id = str::getAttrTag("id", $header_id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "collapse-toggle", $only_class);

		if(self::$show){
			$class_array[] = "show";
		}
		else {
			$class_array[] = "collapsed";
		}

		$aria_expanded = self::$show ? "true" : "false";
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);

		$alt = str::getAttrTag("title", $alt);
		$collapse_attrs = self::getCollapseAttrs($data_target_id, $aria_expanded, $ignore_selector);
		$script = $ignore_selector ? self::getIgnoreClassScript($header_id, $data_target_id) : NULL;

		# Handle headers that are also links
		if($hash){
			$href = href::generate($a);
			$title = "<a{$href}>{$title}</a>";

			return <<<EOF
<div style="
	display: flex;
	align-items: center;
	justify-content: space-between;
">
	<div>
		{$icon}
		{$title}
		{$badge}
	</div>
	<div
		{$id}
		{$class}
		{$style}
		{$alt}
		{$collapse_attrs}
	>
		{$button}
	</div>
</div>
{$script}
EOF;
		}

		return <<<EOF
<div
	{$id}
	{$class}
	{$style}
	{$alt}
	{$collapse_attrs}
>
	{$icon}
	{$title}
	{$badge}
	{$button}
</div>
{$script}
EOF;
	}

	/**
	 * Build the accordion trigger attributes.
	 *
	 * When ignored child classes are configured, the Bootstrap data toggle cannot be
	 * used because Bootstrap's delegated listener would still receive clicks that
	 * bubble from those children. In that case a small local click handler handles
	 * the collapse toggle only after checking the ignore selector.
	 *
	 * @param string      $data_target_id  The ID of the element to toggle.
	 * @param string      $aria_expanded   Initial aria-expanded value.
	 * @param string|null $ignore_selector CSS selector for child elements to ignore.
	 *
	 * @return string Rendered HTML attributes.
	 */
	private static function getCollapseAttrs(string $data_target_id, string $aria_expanded, ?string $ignore_selector = NULL): string
	{
		$attrs[] = str::getAttrTag("data-bs-target", "#{$data_target_id}");
		$attrs[] = str::getAttrTag("aria-expanded", $aria_expanded);
		$attrs[] = str::getAttrTag("aria-controls", $data_target_id);

		if(!$ignore_selector){
			array_unshift($attrs, str::getAttrTag("data-bs-toggle", "collapse"));

			return implode("", array_filter($attrs));
		}

		$target_id = json_encode($data_target_id, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
		$ignore_selector = json_encode($ignore_selector, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
		$on_click = <<<JS
if(event.target.closest({$ignore_selector})){return;}const target=document.getElementById({$target_id});if(target&&window.bootstrap&&bootstrap.Collapse){bootstrap.Collapse.getOrCreateInstance(target,{toggle:false}).toggle();}
JS;

		$attrs[] = str::getAttrTag("onClick", $on_click);

		return implode("", array_filter($attrs));
	}

	/**
	 * Convert one or more ignore class names to a CSS selector.
	 *
	 * @param array|string|null $ignore_class Class name or names to ignore.
	 *
	 * @return string|null CSS selector such as ".class, .other-class".
	 */
	private static function getIgnoreSelector($ignore_class): ?string
	{
		if(!$ignore_class){
			return NULL;
		}

		$classes = self::getIgnoreClasses($ignore_class);
		if(!$classes){
			return NULL;
		}

		return implode(", ", array_map(
			fn($class) => ".{$class}",
			array_unique($classes)
		));
	}

	/**
	 * Normalise ignored class input.
	 *
	 * @param array|string $ignore_class Class name or names to ignore.
	 *
	 * @return array<int,string> Normalised class names without the leading dot.
	 */
	private static function getIgnoreClasses($ignore_class): array
	{
		$classes = [];
		foreach((array)$ignore_class as $class){
			if(is_array($class)){
				$classes = array_merge($classes, self::getIgnoreClasses($class));
				continue;
			}

			foreach(preg_split('/[\s,]+/', (string)$class) as $item){
				$item = trim($item);
				if(!$item){
					continue;
				}

				$classes[] = ltrim($item, ".");
			}
		}

		return array_values(array_unique($classes));
	}

	/**
	 * Keep custom ignore-class accordion headers in sync with their collapse body.
	 *
	 * @param string $header_id      Header element ID.
	 * @param string $data_target_id Collapse body ID.
	 *
	 * @return string Script tag.
	 */
	private static function getIgnoreClassScript(string $header_id, string $data_target_id): string
	{
		$header_id = json_encode($header_id, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
		$data_target_id = json_encode($data_target_id, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);

		return str::getScriptTag(<<<JS
(function(){
	const header = document.getElementById({$header_id});
	const body = document.getElementById({$data_target_id});
	if(!header || !body){
		return;
	}
	const sync = shown => {
		header.classList.toggle('collapsed', !shown);
		header.classList.toggle('show', shown);
		header.setAttribute('aria-expanded', shown ? 'true' : 'false');
	};
	body.addEventListener('show.bs.collapse', () => sync(true));
	body.addEventListener('shown.bs.collapse', () => sync(true));
	body.addEventListener('hide.bs.collapse', () => sync(false));
	body.addEventListener('hidden.bs.collapse', () => sync(false));
	sync(body.classList.contains('show'));
})();
JS);
	}

	/**
	 * The body of an accordion element.
	 *
	 * @param array|string $a
	 * @param string       $data_target_id This element's ID.
	 * @param string|null  $data_parent_id The accordion parent's ID that this collapsable belongs to.
	 *
	 * @return string
	 * @throws Exception
	 * @throws Exception
	 */
	private static function generateBodyHTML($a, string $data_target_id, ?string $data_parent_id): string
	{
		$a = is_array($a) ? $a : ["html" => $a];
		extract($a);

		$id = str::getAttrTag("id", $data_target_id);
		$icon = Icon::generate($icon);
		$badge = Badge::generate($badge);
		$button = Button::generate($button);
		$class_array = str::getAttrArray($class, "collapse", $only_class);
		if(self::$show){
			$class_array[] = "show";
		}
		$class = str::getAttrTag("class", $class_array);
		$style = str::getAttrTag("style", $style);
		$data = str::getDataAttr($data);
		$data_parent = str::getAttrTag("data-bs-parent", $data_parent_id ? "#{$data_parent_id}" : false);

		return "<div{$id}{$class}{$style}{$data_parent}{$data}>{$icon}{$html}{$badge}{$button}</div>";
	}
}
