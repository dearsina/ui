<?php


namespace App\UI;


use App\Common\Common;
use App\Common\href;
use App\Common\Img;
use App\Common\str;

class EmailMessage extends Common {
	/**
	 * The title is the title of page.
	 * Not super userful.
	 *
	 * @var string|null
	 */
	private ?string $title = NULL;

	/**
	 * The preheader is the preview text in mobile browsers
	 * underneath the subject line.
	 *
	 * @var string|null
	 */
	private ?string $preheader = NULL;

	private ?array $header = [];
	private ?array $body = [];
	private ?array $footer = [];
	/**
	 * The different sections of the email
	 * @var array
	 */
	private array $sections;

	/**
	 * The base title text colour for the entire email.
	 * The default colour is #333333.
	 * @var string
	 */
	private string $title_colour = "#333333";

	/**
	 * The base body text colour for the entire email.
	 * The default colour is #666666.
	 * @var string
	 */
	private string $colour = "#666666";

	/**
	 * The base background colour for the entire email.
	 * The default background colour is #FFFFFF
	 * @var string
	 */
	private string $bg_colour = "#FFFFFF";

	/**
	 * An array of colour names => hex values.
	 *
	 * @var array
	 */
	private array $app_colours;

	public function __construct(?array $a = NULL)
	{
		if(!is_array($a)){
			return;
		}

		# Load all the app colours
		$this->loadColours();

		# Store the email sections
		$this->setAttr($a);
	}

	/**
	 * @return string|null
	 */
	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 * @param string|null $title
	 */
	public function setTitle(?string $title): void
	{
		$this->title = $title;
	}

	/**
	 * @return string|null
	 */
	public function getPreheader(): ?string
	{
		return $this->preheader;
	}

	/**
	 * @param string|null $preheader
	 */
	public function setPreheader(?string $preheader): void
	{
		$this->preheader = $preheader;
	}



	/**
	 * Loads the app colours
	 */
	private function loadColours(): void
	{
		$css_file = "https://{$_ENV['app_subdomain']}.{$_ENV['domain']}/css/app.css";
		if(!$handle = fopen($css_file, "r")){
			//If unable to open the app css
			return;
		}
		while (!feof($handle)) {
			$contents .= fread($handle, 100);
			if(preg_match("/:root\s*{([^}]+)}/", $contents, $matches)){
				break;
			}
		}
		$css = str_replace("--bs-","", $matches[1]);
		preg_match_all("/\s*([a-z\-]+): ([^;]+);\s*/", $css, $matches);
		foreach($matches[0] as $id => $string){
			$this->app_colours[$matches[1][$id]] = $matches[2][$id];
		}
	}

	/**
	 * Will return the hex value of the colour name,
	 * or the colour name itself if no hex value is found.
	 *
	 * @param $colour_name
	 *
	 * @return string
	 */
	private function getColourHexValue(?string $colour_name): ?string
	{
		if(!$colour_name){
			return NULL;
		}
		return $this->app_colours[$colour_name] ?: $colour_name;
	}

	/**
	 * @return string
	 */
	private function getColour(): string
	{
		return $this->getColourHexValue($this->colour);
	}

	/**
	 * @param string $colour
	 */
	public function setColour(string $colour): void
	{
		$this->colour = $colour;
	}

	/**
	 * @return string
	 */
	private function getTitleColour(): string
	{
		return $this->getColourHexValue($this->title_colour);
	}

	/**
	 * @param string $colour
	 */
	public function setTitleColour(string $title_colour): void
	{
		$this->title_colour = $title_colour;
	}

	/**
	 * @return string
	 */
	private function getBgColour(): string
	{
		return $this->getColourHexValue($this->bg_colour);
	}

	/**
	 * @param string $bg_colour
	 */
	public function setBgColour(string $bg_colour): void
	{
		$this->bg_colour = $bg_colour;
	}


	private function generateTag(string $tag, $a): string
	{
		extract($a);

		# Class
		$class_array = str::getAttrArray($class, $default_class, $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style_array = str::getAttrArray($style, $default_style, $only_style);
		$style_array['color'] = $this->getColourHexValue($style_array['color']);
		$style = str::getAttrTag("style", $style_array);
		
		# Align
		$align = str::getAttrTag("align", $align);
		$valign = str::getAttrTag("valign", $valign);

		# Background colour
		$bgcolor = str::getAttrTag("bgcolor", $bgcolor);

		return "<{$tag}{$align}{$valign}{$class}{$style}{$bgcolor}>{$html}</{$tag}>";
	}

	/**
	 * Header can include:
	 *  - `logo` (array)
	 *  - `url`
	 *  - `title`
	 *  - `style`
	 *  - `only_style`
	 *
	 * @param $a
	 */
	public function setHeader($a = NULL){
		# Array
		if(is_array($a)){
			$this->header = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->header['title'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->header = [];
			return true;
		}

		return true;
	}

	/**
	 * Footer can include:
	 *  - `title`
	 *  - `style`
	 *  - `only_style`
	 *
	 * @param array|NULL $a
	 */
	public function setFooter($a = NULL){
		# Array
		if(is_array($a)){
			$this->footer = $a;
			return true;
		}

		# Mixed
		if ($a){
			$this->footer['title'] = $a;
			return true;
		}

		# Clear
		if($a === false){
			$this->footer = [];
			return true;
		}

		return true;
	}

	/**
	 * Body must include
	 *  - `type`
	 * Body can include
	 *  - `preheader`
	 *  - `header`
	 *  - `body`
	 *  - `button`
	 *
	 * @param $a
	 */
	public function setBody($a = NULL): void
	{
		# Empty
		if(!$a){
			return;
		}

		# String
		if(!is_array($a)){
			$a = [[
				"copy" => [
					"body" => $a
				]
			]];
		}

		if(!str::isNumericArray($a)){
			$a = [$a];
		}

		foreach($a as $section){
			$this->body[] = $section;
		}
	}

	/**
	 * Get the whole email message body as HTML.
	 *
	 * @param array|null $a
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getHTML(?array $a = NULL): string
	{
		if($a){
			$this->setAttr($a);
		}

		$html[] = $this->getTop();
		$html[] = $this->getHeaderHTML();
		$html[] = $this->getBodyHTML();
		$html[] = $this->getFooterHTML();
		$html[] = $this->getTail();

		return implode("\r\n", $html);
	}

	/**
	 * The logo is an image that goes top left.
	 *
	 * @param $a
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private function getLogo($a): ?string
	{
		if(!$a){
			return NULL;
		}

		if(!is_array($a)){
			$a = [
				"src" => $a,
				"alt" => "Logo",
			];
		}

		# Default style
		$a['default_style'] = [
			"display" => "block",
			"font-family" => "Helvetica, Arial, sans-serif",
			"color" => $this->getColour(),
			"font-size" => "16px",
		];

		# If the logo is not an image (just text)
		if(!$a['src']){
			return $this->getHeaderTextHTML($a);
		}

		return $this->generateImageTag($a);
	}

	/**
	 * Expecting:
	 *  - `src`
	 * Optional:
	 *  - `url`
	 *  - `height`
	 *  - `width`
	 *  - `style`
	 *  - `default_style`
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	private function generateImageTag(array $a): ?string
	{
		# Actual image size
		if(!$size = Img::getimagesize($a['src'])){
			throw new \Exception("Information about the logo file [<code>{$a['src']}</code>] could no be extracted.");
		}

		# There is max image width of a hard 500px if not set
		$a['max_width'] = $a['max_width'] ?: 500;

		# Ensure images are not bigger than the max width
		if($size['width'] > $a['max_width']){
			//if the image is larger than 500px in width
			$scale = $a['max_width'] / $size['width'];
			$size['width'] = $a['max_width'];
			$size['height'] = floor($size['height'] * $scale);
			//Ensure the height is proportionate to the new max width
		}

		# Scale
		$height_scale = $a['height'] ? $a['height'] / $size['height'] : 1;
		$width_scale = $a['width'] ? $a['width'] / $size['width'] : 1;

		# Dimensions
		$a['width'] = $a['width'] ?: floor($size['width'] * $height_scale);
		$a['height'] = $a['height'] ?: floor($size['height'] * $width_scale);
		$a['style']['width'] = "{$a['width']}px";
		$a['style']['height'] = "{$a['height']}px";

		# Border needs to be set to zero
		$a['border'] = "0";

		$img = Img::generate($a);

		if($a['url']){
			return href::a([
				"url" => $a['url'],
				"target" => "_blank",
				"html" => $img,
			]);
		}

		return $img;
	}

	/**
	 * Prepares and returns the header text.
	 *
	 * @param $a
	 *
	 * @return string|null
	 */
	private function getHeaderTextHTML($a): ?string
	{
		if(!$a){
			return NULL;
		}

		if(!is_array($a)){
			$a = [
				"title" => $a
			];
		}

		$a['html'] .= $a['title'];
		$a['default_style'] = [
			"color" => $this->getColourHexValue($a['colour'] ?: $this->getColour()),
			"text-decoration" => "none",
		];

		return $this->generateTag("span", $a);
	}

	/**
	 * Prepares and returns the header bar.
	 *
	 * @return string|null
	 * @throws \Exception
	 */
	private function getHeaderHTML(): ?string
	{
		if(!$this->header){
			// Headers are optional
			return NULL;
		}

		extract($this->header);

		# Set the background colour for the header
		$bgcolor = $this->getColourHexValue($bg_colour ?: $this->getBgColour());

		# Set the logo
		$logo = $this->getLogo($logo);
		//<a href="http://alistapart.com/article/can-email-be-responsive/" target="_blank"><img alt="Logo" src="https://kycdd.co.za/assets/img/salt/logo.png" width="52" height="78" style="display: block; font-family: Helvetica, Arial, sans-serif; color: #666666; font-size: 16px;" border="0"></a>

		# Set the header text
		$title = $this->generateTag("td", [
			"align" => "right",
			"style" => [
				"padding" => "0 0 5px 0",
				"font-size" => "14px",
				"font-family" => "Arial, sans-serif",
				"color" => $this->getColourHexValue($colour ?: $this->getColour()),
				"text-decoration" => "none",
			],
			"html" => $this->getHeaderTextHTML($title)
		]);
		//<span style="color: #666666; text-decoration: none;">{$title}</span>

		return <<<EOF
<!-- HEADER -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="{$bgcolor}">
            <div align="center" style="padding: 0px 15px 0px 15px;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="500" class="wrapper">
                    <!-- LOGO/PREHEADER TEXT -->
                    <tr>
                        <td style="padding: 20px 0px 20px 0px;" class="logo">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td bgcolor="{$bgcolor}" width="100" align="left">
										{$logo}
									</td>
                                    <td bgcolor="{$bgcolor}" width="400" align="right" class="mobile-hide">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                {$title}
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
EOF;
	}
	
	private function getFooterHTML(): ?string
	{
		if(!$this->footer){
			// Footers are optional
			return NULL;
		}

		extract($this->footer);

		# Set the background colour for the footer
		$bgcolor = $this->getColourHexValue($bg_colour ?: $this->getBgColour());

		$default_style = [
			"font-size" => "10px",
			"line-height" => "13px",
			"font-family" => "Helvetica, Arial, sans-serif",
			"color" => $this->getColourHexValue($colour ?: $this->getColour())
		];

		if(is_array($footer)){
			$footer = $this->embeddedImplode("p", $footer);
		}

		$td = $this->generateTag("td", [
			"align" => "center",
			"valign" => "middle",
			"default_style" => $default_style,
			"html" => $html.$footer,
			"style" => $style
		]);
		//<td align="center" valign="middle" style=""><span class="appleFooter" style="color:#666666;">1234 Main Street, Anywhere, MA 01234, USA</span><br><a class="original-only" style="color: #666666; text-decoration: none;">Unsubscribe</a><span class="original-only" style="font-family: Arial, sans-serif; font-size: 12px; color: #444444;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</span><a style="color: #666666; text-decoration: none;">View this email in your browser</a></td>

		return <<<EOF
<!-- FOOTER -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="{$bgcolor}" align="center">
            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tr>
                    <td style="padding: 20px 0px 20px 0px;">
                        <!-- UNSUBSCRIBE COPY -->
                        <table role="presentation" width="500" border="0" cellspacing="0" cellpadding="0" align="center" class="responsive-table">
                            <tr>
                                {$td}
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
EOF;

	}

	/**
	 * Returns an image row.
	 *
	 * @param $img
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function getImageHTML($img): string
	{
		# If the image is still in array format, get the img string
		if(is_array($img)){
			$img['class'] = "img-max";
			$img['default_style'] = [
				"display" => "block",
				"padding" => "0",
				"color" => $this->getColour(),
				"text-decoration" => "none",
				"font-family" => "Helvetica, arial, sans-serif",
				"font-size" => "16px",
			];
			$img = $this->generateImageTag($img);
		}

		return <<<EOF
<!-- HERO IMAGE -->
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
	<tbody>
		 <tr>
			  <td class="padding-copy" style="padding: 25px 0 0 0;" align="center">
				  <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
					  <tr>
						  <td>{$img}</td>
						</tr>
				  </table>
			  </td>
		  </tr>
	</tbody>
</table>
EOF;
	}

	private function getTitleTextHTML($a): ?string
	{
		if(!$a){
			return NULL;
		}

		if(!is_array($a)){
			$a = [
				"title" => $a
			];
		}

		extract($a);

		# Set the title text colour
		if($title_colour){
			$color = $title_colour;
		} else if ($colour){
			$color = $colour;
		} else {
			$color = $this->getTitleColour();
		}
		$color = $this->getColourHexValue($color);

		$default_style = [
			"font-size" => "25px",
			"font-family" => "Helvetica, Arial, sans-serif",
			"color" => $color,
			"padding-top" => "20px",
		];

		return $this->generateTag("td", [
			"style" => $style,
			"class" => $class,
			"bgcolor" => $bgcolor,
			"align" => $align ?: "center",
			"valign" => $valign ?: NULL,
			"default_style" => $default_style,
			"default_class" => "padding-copy",
			"html" => $title.$html.$text
		]);
	}

	private function getBodyTextHTML($a): ?string
	{
		if(!$a){
			return NULL;
		}

		if(!is_array($a)){
			$a = [
				"body" => $a
			];
		}

		extract($a);

		# Set the body text colour
		if($body_colour){
			$color = $body_colour;
		} else if ($colour){
			$color = $colour;
		} else {
			$color = $this->getColour();
		}
		$color = $this->getColourHexValue($color);

		$default_style = [
			"padding" => "10px 0 0 0",
			"font-size" => "16px",
			"line-height" => "25px",
			"font-family" => "Helvetica, Arial, sans-serif",
			"color" => $color,
		];

		if(is_array($body)){
			$body = $this->embeddedImplode("p", $body);
		}

		return $this->generateTag("td", [
			"style" => $style,
			"class" => $class,
			"bgcolor" => $bgcolor,
			"align" => $align ?: "center",
			"default_style" => $default_style,
			"default_class" => "padding-copy",
			"html" => $body.$html.$text
		]);
	}

	private function embeddedImplode($tag, $array): ?string
	{
		if(!$array){
			return NULL;
		}
		return "<{$tag}>".implode("</{$tag}><{$tag}>", $array)."</{$tag}>";
	}

	private function getCopyHTML($a): string
	{
		extract($a);

		$trs[] = $this->getTitleTextHTML($title);
		$trs[] = $this->getBodyTextHTML($body);

		$table = $this->embeddedImplode("tr", array_filter($trs));

		return <<<EOF
<!-- COPY -->
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
	{$table}
</table>
EOF;
	}

	private function getArticlesHTML($a): string
	{
		if(!str::isNumericArray($a)){
			$a = [$a];
		}

		foreach($a as $article){
			$articles[] = $this->getArticleHTML($article);
		}

		return "<tr>".implode("</tr><tr>", $articles)."</tr>";
	}

	private function getArticleHTML($a): string
	{
		extract($a);

		if($image){
			$image['max_width'] = 105;
			$image['default_style'] = [
				"display" => "block",
				"padding" => "0",
				"color" => $this->getColour(),
				"text-decoration" => "none",
				"font-family" => "Helvetica, arial, sans-serif",
				"font-size" => "16px",
			];
			$image = $this->generateImageTag($image);
			$image = $this->generateTag("td", [
				"valign" => "top",
				"style" => [
					"padding" => "40px 0 0 0"
				],
				"class" => "mobile-hide",
				"html" => $image
			]);
		} else {
			//if there is no image, make sure the space of the image is kept the same
			$image = $this->generateTag("td", [
				"valign" => "top",
				"style" => [
					"padding" => "40px 0 0 0"
				],
				"class" => "mobile-hide",
				"html" => "&nbsp;"
			]);
		}
		//<td valign="top" style="padding: 40px 0 0 0;" class="mobile-hide"><a href="https://litmus.com/community" target="_blank"><img src="https://kycdd.co.za/assets/img/salt/litmus-logo.png" alt="Litmus" width="105" height="105" border="0" style="display: block; font-family: Arial; color: #666666; font-size: 14px; width: 105px; height: 105px;"></a></td>

		if($pretitle){
			if(!is_array($pretitle)){
				$pretitle = [
					"html" => $pretitle
				];
			}
			$pretitle['align'] = "left";
			$pretitle['default_style'] = [
				"padding" => "0 0 5px 25px",
				"font-size" => "13px",
				"font-family" => "Helvetica, Arial, sans-serif",
				"font-weight" => "normal",
				"color" => "#aaaaaa",
			];
			$pretitle['default_class'] = "padding-meta";
			$pretitle['html'] = $pretitle['html'].$pretitle['pretitle'];
			$tds[] = $this->generateTag("td", $pretitle);
		}
		//<td align="left" style="padding: 0 0 5px 25px; font-size: 13px; font-family: Helvetica, Arial, sans-serif; font-weight: normal; color: #aaaaaa;" class="padding-meta">Litmus Community</td>
		
		if($title){
			if(!is_array($title)){
				$title = [
					"html" => $title
				];
			}
			$title['align'] = "left";
			$title['default_style'] = [
				"padding" => "0 0 5px 25px",
				"font-size" => "22px",
				"font-family" => "Helvetica, Arial, sans-serif",
				"font-weight" => "normal",
				"color" => $this->getTitleColour(),
			];
			$title['default_class'] = "padding-copy";
			$title['html'] = $title['html'].$title['title'];
			$tds[] = $this->generateTag("td", $title);
		}
		//<td align="left" style="padding: 0 0 5px 25px; font-size: 22px; font-family: Helvetica, Arial, sans-serif; font-weight: normal; color: #333333;" class="padding-copy">A growing community for email professionals</td>

		if($body){
			if(!is_array($body)){
				$body = [
					"html" => $body
				];
			}
			$body['align'] = "left";
			$body['default_style'] = [
				"padding" => "10px 0 15px 25px",
				"font-size" => "16px",
				"line-height" => "24px",
				"font-family" => "Helvetica, Arial, sans-serif",
				"color" => $this->getColour(),
			];
			$body['default_class'] = "padding-copy";
			$body['html'] = $body['html'].$body['body'];
			$tds[] = $this->generateTag("td", $body);
		}
		//<td align="left" style="padding: 10px 0 15px 25px; font-size: 16px; line-height: 24px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Share knowledge, ask code questions, and learn from a growing library of articles on all things email.</td>

		if($button){
			$tds[] = <<<EOF
<td style="padding:0 0 45px 25px;" align="left" class="padding">
	<table role="presentation" border="0" cellspacing="0" cellpadding="0" class="mobile-button-container">
		<tr>
			<td align="center">{$this->getButtonHTML($button)}</td>
		</tr>
	</table>
</td>
EOF;
		}

		$table = "<tr>".implode("</tr><tr>", $tds)."</tr>";

		return <<<EOF
{$image}
<td style="padding: 40px 0 0 0;" class="no-padding">
	<!-- ARTICLE -->
	<table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%">
		{$table}
	</table>
</td>
EOF;

	}

	
	private function getColumnsHTML($a): array
	{
		if(!str::isNumericArray($a[0])){
			$a = [$a];
		}

		foreach($a as $pair){
			$pairs[] = <<<EOF
<!-- TWO COLUMNS -->
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td valign="top" style="padding: 0;" class="mobile-wrapper">
			{$this->getColumnHTML("left", $pair[0])}
			{$this->getColumnHTML("right", $pair[1])}
		</td>
	</tr>
</table>
EOF;
		}

		return $pairs;
	}

	private function getColumnHTML(string $side, $a): string
	{
		if(!is_array($a)){
			$a = [
				"copy" => [
					"body" => $a
				]
			];
		}

		extract($a);

		# Image
		if($image){
			$image['class'] = "img-max";
			$image['max_width'] = 240;
			$image['default_style'] = [
				"display" => "block",
				"padding" => "0",
				"color" => $this->getColour(),
				"text-decoration" => "none",
				"font-family" => "Helvetica, arial, sans-serif",
				"font-size" => "16px",
			];
			$image = $this->generateImageTag($image);
			$rows[] = $this->generateTag("td", [
				"align" => "center",
				"bgcolor" => $this->getBgColour(),
				"valign" => "middle",
				"html" => $image
			]);
		}

		# Header
		if($title){
			if(!is_array($title)){
				$title = [
					"title" => $title
				];
			}
			$title['style'] = [
				"padding" => "15px 0 0 0",
				"font-size" => "20px",
			];
			$title['bgcolor'] = $title['bg_colour'];

			$rows[] = $this->getTitleTextHTML($title);
		}

		# Body
		if($body){
			if(!is_array($body)){
				$body = [
					"body" => $body
				];
			}
			$body['style'] = [
				"padding" => "5px 0 0 0",
				"font-size" => "14px",
				"line-height" => "20px",
			];
			$body['bgcolor'] = $body['bg_colour'];

			$rows[] = $this->getBodyTextHTML($body);
		}

		$td = implode("</tr><tr>", $rows);

		return <<<EOF
<!-- {$side} COLUMN -->
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="47%" align="{$side}" class="responsive-table">
	<tr>
		<td style="padding: 20px 0 20px 0;">
			<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>{$td}</tr>
			</table>
		</td>
	</tr>
</table>
EOF;
;
	}

	private function getBodyHTML(): ?string
	{
		if(empty($this->body)){
			return NULL;
		}

		foreach($this->body as $section){
			$body[] = $this->getSectionHTML($section);
		}

		return implode("\r\n", $body);
	}

	private function getSectionHTML($a): string
	{
		# Set the background colour for the section
		$bgcolor = $this->getColourHexValue($a['bg_colour'] ?: $this->getBgColour());

		$rows = [];

		# Image, copy, button
		foreach($a as $item => $data){
			if($item == "articles"){
				$articles = $this->getArticlesHTML($data);
				continue;
			}
			if(!in_array($item, ["image", "copy", "columns", "button"])){
				continue;
			}
			$method = str::getMethodCase("get_{$item}_HTML");
			$row = $this->{$method}($data);

			if(is_array($row)){
				foreach($row as $r){
					$rows[] = "<tr><td>{$r}</td></tr>";
				}
			} else {
				$rows[] = "<tr><td>{$row}</td></tr>";
			}
		}

		$table = implode("\r\n", $rows);

		return <<<EOF
<!-- ONE COLUMN SECTION -->
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="{$bgcolor}" align="center" style="padding: 20px 15px 40px 15px;" class="section-padding">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                <tr>
                    <td>
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                            {$table}
                        </table>
                    </td>
                </tr>
                {$articles}
            </table>
        </td>
    </tr>
</table>
EOF;

	}

	/**
	 * The button (background) colour is set via the colour key,
	 * the text colour can be set via styles (color).
	 * The default text colour is white.
	 * The minimal requirement is:
	 *  - `title`
	 *  - `url`
	 *  - `colour`
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public function getButtonHTML(array $a): ?string
	{
		if(!$a){
			return NULL;
		}

		# Button text
		$a['html'] = $a['title'];

		# Button colour
		$bgcolor = $this->getColourHexValue($a['colour']);

		# Button default styles
		$a['default_style'] = [
			"font-size" => "16px",
			"font-family" => "Helvetica, Arial, sans-serif",
			"font-weight" => "normal",
			"color" => "#ffffff",
			"text-decoration" => "none",
			"background-color" => $bgcolor,
			"border-top" => "15px solid {$bgcolor}",
			"border-bottom" => "15px solid {$bgcolor}",
			"border-left" => "25px solid {$bgcolor}",
			"border-right" => "25px solid {$bgcolor}",
			"border-radius" => "3px",
			"-webkit-border-radius" => "3px",
			"-moz-border-radius" => "3px",
			"display" => "inline-block",
		];

		$a['default_class'] = "mobile-button";

		$button = href::a($a);

		return <<<EOF
<!-- BULLETPROOF BUTTON -->
<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" class="mobile-button-container">
	<tr>
		<td align="center" style="padding: 25px 0 0 0;" class="padding-copy">
			<table role="presentation" border="0" cellspacing="0" cellpadding="0" class="responsive-table">
				<tr>
					<td align="center">{$button}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
EOF;

	}

	private function getTop()
	{
		return <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
<title>{$this->getTitle()}</title>
<!--

    SALTED | A RESPONSIVE EMAIL TEMPLATE
    =====================================

    Based on code used and tested by Litmus (@litmusapp)
    Originally developed by Kevin Mandeville (@KEVINgotbounce)
    Cleaned up by Jason Rodriguez (@rodriguezcommaj)
    Presented by A List Apart (@alistapart)

    Email is surprisingly hard. While this has been thoroughly tested, your mileage may vary.
    It's highly recommended that you test using a service like Litmus and your own devices.

    Enjoy!

 -->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta http-equiv="X-UA-Compatible" content="IE=edge" />
<style type="text/css">
    /* CLIENT-SPECIFIC STYLES */
    #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" message */
    .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
    .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing */
    body, table, td, a{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
    table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook 2007 and up */
    img{-ms-interpolation-mode:bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */

    /* RESET STYLES */
    body{margin:0; padding:0;}
    img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
    table{border-collapse:collapse !important;}
    body{height:100% !important; margin:0; padding:0; width:100% !important;}

    /* iOS BLUE LINKS */
    .appleBody a {color:#68440a; text-decoration: none;}
    .appleFooter a {color:#999999; text-decoration: none;}

    /* MOBILE STYLES */
    @media screen and (max-width: 525px) {

        /* ALLOWS FOR FLUID TABLES */
        table[class="wrapper"]{
          width:100% !important;
        }

        /* ADJUSTS LAYOUT OF LOGO IMAGE */
        td[class="logo"]{
          text-align: left;
          padding: 20px 0 20px 0 !important;
        }

        td[class="logo"] img{
          margin:0 auto!important;
        }

        /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
        td[class="mobile-hide"]{
          display:none;}

        img[class="mobile-hide"]{
          display: none !important;
        }

        img[class="img-max"]{
          max-width: 100% !important;
          height:auto !important;
        }

        /* FULL-WIDTH TABLES */
        table[class="responsive-table"]{
          width:100%!important;
        }

        /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
        td[class="padding"]{
          padding: 10px 5% 15px 5% !important;
        }

        td[class="padding-copy"]{
          padding: 10px 5% 10px 5% !important;
          text-align: center;
        }

        td[class="padding-meta"]{
          padding: 30px 5% 0px 5% !important;
          text-align: center;
        }

        td[class="no-pad"]{
          padding: 0 0 20px 0 !important;
        }

        td[class="no-padding"]{
          padding: 0 !important;
        }

        td[class="section-padding"]{
          padding: 50px 15px 50px 15px !important;
        }

        td[class="section-padding-bottom-image"]{
          padding: 50px 15px 0 15px !important;
        }

        /* ADJUST BUTTONS ON MOBILE */
        td[class="mobile-wrapper"]{
            padding: 10px 5% 15px 5% !important;
        }

        table[class="mobile-button-container"]{
            margin:0 auto;
            width:100% !important;
        }

        a[class="mobile-button"]{
            width:80% !important;
            padding: 15px !important;
            border: 0 !important;
            font-size: 16px !important;
        }

    }
</style>
</head>
<body style="margin: 0; padding: 0;">

<!-- Some preview text. -->
<div style="display: none; max-height: 0; overflow: hidden;">
{$this->getPreheader()}		
</div>
<!-- Get rid of unwanted preview text. -->
<!-- DOESN'T ACTUALLY WORK
<div style="display: none; max-height: 0px; overflow: hidden;">
    &nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;
</div>
-->
EOF;
	}

	private function getTail()
	{
		return <<<EOF
</body>
</html>
EOF;

	}
}