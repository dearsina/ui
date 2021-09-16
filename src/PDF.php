<?php


namespace App\UI;


use App\Common\str;

class PDF {
	/**
	 * "Prints" a webpage as PDF using a headless Google Chrome instance.
	 * Saves the PDF as a temporary file and returns either the file contents
	 * or temporary file name.
	 *
	 * @param           $url
	 * @param bool|null $return_filename_only
	 *
	 * @return string
	 * @throws \Exception
	 * @link https://developers.google.com/web/updates/2017/04/headless-chrome
	 */
	static function print($url, ?int $seconds = 10, ?bool $refresh = NULL, ?bool $return_filename_only = NULL, ?int $rerun = 0): string
	{
		# Get the MD5 hash from the URL
		$md5 = md5($url);

		# Generate temporary filename
		$tmp_filename = PDF::generateTemporaryFilename($md5);

		# Force refresh the file?
		if($refresh){
			if(file_exists($tmp_filename)){
				unlink($tmp_filename);
			}
		}

		# If the file doesn't already exist, create it
		if($refresh || !file_exists($tmp_filename)){

			# An easier way to structure the CLI settings
			$settings = self::formatHeadlessChromeSettings([
				# Run Chrome headless
				"headless" => true,
				# Wait till Javascript has done it's job before drawing/printing
				"run-all-compositor-stages-before-draw" => true,
				# Set the user agent to be a hidden key, used on the recipient end to ensure this is a headless command
				"user-agent" => $_ENV['db_password'],
				# Wait n milliseconds before printing (gives script time to load JS)
				"virtual-time-budget" => $seconds * 1000,
				# Print the page to PDF (and give filename)
				"print-to-pdf" => $tmp_filename,
				# Remove the header and footer
				"print-to-pdf-no-header" => true,
				# Only log fatal errors (0 will log everything, 3 will only log fatal)
				"log-level" => "3",
				# Give Chrome privileges
				"no-sandbox" => true,

				//				"enable-logging" => "stderr",
				//				"v" => 1,

				"user-data-dir" => "/var/www/tmp",
			]);

			# Write the headless command
			$chrome_command = "google-chrome {$settings} '{$url}' 2>&1";
			// 2>&1 means that any output is piped to the stdout (stored in the $output variable)

			# Execute the command
			exec($chrome_command, $output);
		}

		$command = "chmod 777 {$tmp_filename} 2>&1";
		exec($command, $output);

		# Ensure the PDF was generated successfully, if not, try again (up to 10 times)
		if(filesize($tmp_filename) < 3000){
			//if file is less than 3kb (if it's dud)
			if($rerun == 10){
				//if 10 attempts have been made to create this PDF with no luck)

				$error = implode("\r\n", $output);
				throw new \Exception("
				10 attempts were made to make a PDF from the following URL without success: <code>{$url}</code>.
				The following error message was returned: <code>{$error}</code>");
			}

			# Count the number of attempts
			$rerun++;

			# Increase the seconds given to load the page
			$seconds++;

			# Run again
			return self::print($url, $seconds, $refresh, $return_filename_only, $rerun);
		}

		if($return_filename_only){
			return $tmp_filename;
		}

		$tmp_contents = file_get_contents($tmp_filename);

		if(file_exists($tmp_filename)){
			unlink($tmp_filename);
		}

		return $tmp_contents;
	}

	/**
	 * Returns CSS that will need to be placed in the page getting PDF-printed,
	 * to ensure correct formatting.
	 *
	 * @return string
	 */
	public static function getPrintCss(?bool $keep_footer = NULL): string
	{
		if($keep_footer){
			//If you want to keep the auto-generated footer
			$margin_bottom = "margin-bottom: 0.5cm;";
		}
		else {
			//If you don't want to keep the footer
			$margin_bottom = "margin-bottom: 0;";
		}

		return <<<EOF
<style>
		@media print {
		  @page { size: A4; margin-top: 1cm; margin-left: 0; margin-right: 0; {$margin_bottom}}
		  .pace { display:none; }
		  #ui-navigation { display:none; }
		  #ui-footer { display:none; }
		  body { margin-top: 0; margin-left: 1cm; margin-right: 1cm; background-color: white; height: unset; zoom: 75%; }
		  main { margin: 0; padding: 0; max-width: unset !important; }
		  
		  /** When printed, .containers will shrink if the max-width limit isn't removed */
		  .container {max-width: unset !important;}
		  /** For this to work, the .row immediately outside the card will need to be set to display:block */
		  .card{ break-inside: avoid; page-break-inside:avoid; }
		  /** Things we don't want to see in print view */
		  .collapse-toggle::after /** The little chevron down */
		  {
		  	display:none;
		  }
		  
		  /** For optional footers */
		  footer {
			position: fixed;
			bottom: 0;
			background: white;
			color: black;
		  }
		}		
</style>
EOF;
	}

	/**
	 * Similar to the above, but hides all headers/footers.
	 *
	 * @return string
	 */
	public static function getDocFillCss(): string
	{
		return <<<EOF
<style>
		@media print {
		  @page { margin:0; padding: 0; size: A4; }
		  .pace { display:none; }
		  #ui-navigation { display:none; }
		  #ui-footer { display:none; }
		  body { margin: 0; padding: 0; background-color: white; }
		  main { margin: 0; padding: 0; max-width: unset !important; }
		}
</style>
EOF;
	}

	/**
	 * Translate an array of settings to a CLI string.
	 * If setting has no value, set to TRUE.
	 *
	 * @param array $settings
	 *
	 * @return string|null
	 */
	private static function formatHeadlessChromeSettings(array $settings): ?string
	{
		foreach($settings as $key => $val){
			if($val === true){
				$string[] = "--{$key}";
			}
			else if(is_int($val)){
				$string[] = "--{$key}={$val}";
			}
			else {
				$string[] = "--{$key}=\"{$val}\"";
			}
		}

		return implode(" ", $string);
	}

	/**
	 * Deletes the temporary file. ONLY accepts:
	 *  - URL that was originally sent to print-to-PDF
	 *  - the tmp_filename
	 *  - The HTML string (depreciated)
	 *
	 * `***DON'T SEND THE PDF CONTENT***`
	 *
	 * @param string $a
	 *
	 * @return bool
	 */
	public static function delete(string $a): bool
	{
		# Check to see what kind of string $a is
		if(filter_var($a, FILTER_VALIDATE_URL)){
			//if it's the URL to the page that generated the PDF

			# Get the MD5 hash from the URL
			$md5 = md5($a);

			# Generate temporary filename
			$tmp_filename = PDF::generateTemporaryFilename($md5);
		}

		else if(strlen($a) == strlen(sys_get_temp_dir()) + 1 + 32){
			//if it is a file path
			$tmp_filename = $a;
		}

		else if(strpos($a, $_ENV['tmp_dir']) === 0){
			//if this is a temp file
			$tmp_filename = $a;
		}

		else {
			//if it is the HTML content
			$md5 = md5($a);

			# Generate temporary filename from the MD5
			$tmp_filename = PDF::generateTemporaryFilename($md5);
		}var_dump($a, $tmp_filename);

		# If the file for some reason doesn't exist, return true
		if(!file_exists($tmp_filename)){
			return true;
		}

		# Delete the (tmp) file
		return unlink($tmp_filename);
	}

	/**
	 * Wraps the HTML (body) with head tags, links to CSS.
	 *
	 * @param $html
	 *
	 * @return string
	 */
	static public function wrap(string $html): string
	{
		//		return <<<EOF
		//<!DOCTYPE html>
		//<html>
		//	<head>
		//		<link rel="stylesheet" type="text/css" href="https://{$_ENV['app_subdomain']}.{$_ENV['domain']}/css/app.css">
		//	</head>
		//	<body style="background-color: unset !important; height: unset !important;margin:0;padding:0;">
		//		{$html}
		//	</body>
		//</html>
		//EOF;
		return <<<EOF
<!DOCTYPE html>
<html>
	<head>
		<style>
		@media print {
		  @page { margin-top: 0;margin-left: 0;margin-right: 0; }
		  body { margin: 1.6cm; }
		}
		</style>
		<link rel="stylesheet" type="text/css" href="https://{$_ENV['app_subdomain']}.{$_ENV['domain']}/css/app.css">
	</head>
	<body style="background-color: unset !important; height: unset !important;margin:20px;padding:20px;">
		{$html}
	</body>
</html>
EOF;
	}

	public static function generateTemporaryFilename(?string $file_name = NULL): string
	{
		# Generate an arbitrary filename
		$tmp_file_name = $file_name ?: str::uuid();

		# Generate a tmp path + name
		$tmp_dir = sys_get_temp_dir();

		return "{$tmp_dir}/{$tmp_file_name}";
	}

	/**
	 * This was a hacky attempt at adding header and footer to a longer HTML document.
	 *
	 * @param $html
	 * @param $header
	 * @param $footer
	 *
	 * @return string
	 */
	static function addHeaderAndFooter($html, $header, $footer): string
	{
		$header = "This is the header";
		$footer = "<div class=float-right>Page {page} of {pages}</div>";

		return <<<EOF
<style>
#content {
	width:800px;
	/**
	 * This is set at 800px to represent how wide the page
	 * will end up being in the final ouput PDF. This is
	 * so that the page numbers can be accurately calculated.
	 */
}
.page-header
{
	border-bottom: 1px solid #ddd;
	padding: 0 20px 20px 20px;
	margin: 0 20px;
}
.page-content-frame
{
	width: 100%; 
	overflow: hidden; 
}
.page-content
{
	position: relative;
	left 0;
}
.page-footer
{
	padding: 10px 20px 0 20px;
	border-top:1px solid #ddd; 
	margin: 0 20px;
}

</style>
<div id="content">{$html}</div>
<div id="header">{$header}</div>
<div id="footer">{$footer}</div>
<div id="frame" class="page-frame"></div>
<script>
// Editable
var header_height = 50;
var footer_height = 50;
var content_margin = 40;

// HTML pieces
var frame = document.getElementById("frame");
var header = document.getElementById("header");
var content = document.getElementById("content");
var footer = document.getElementById("footer");

// Only use the footer and header if needed
if(header.innerHTML.length){
    var headerY = 50;
} else {
    var headerY = 0;
}
if(footer.innerHTML.length){
    var footerY = 50;
} else {
    var footerY = 0;
}

// Cannot be changed
var pageY = 1460;
var bodyY = pageY - (headerY + footerY + 80);

// Calculate how many pages this is
var pages = Math.ceil(content.getBoundingClientRect().height/bodyY);

// Write out the pages
for(let page = 0; page < pages; page++){
    var shift = page * bodyY;
    
    var html = '';
    
    if(header.innerHTML.length){
		var headerHTML = header.innerHTML; 
		headerHTML = headerHTML.replace('{page}', page + 1);
		headerHTML = headerHTML.replace('{pages}', pages);
		
		html += '<div class="page-header" style="height: '+headerY+'px;">'+headerHTML+'</div>';
    }
    
    html += '<div class="page-content-frame" style="height: '+bodyY+'px;margin: '+content_margin+'px 0; padding: 0 '+content_margin+'px;"><div class="page-content" style="top:-'+shift+'px;">'+content.innerHTML+'</div></div>';

    if(footer.innerHTML.length){
		var footerHTML = footer.innerHTML; 
		footerHTML = footerHTML.replace('{page}', page + 1);
		footerHTML = footerHTML.replace('{pages}', pages);
		
		html += '<div class="page-footer" style="height: '+footerY+'px;">'+footerHTML+'</div>';
	}
    
    frame.innerHTML += html;
}

// Remove the pieces
header.remove();
content.remove();
footer.remove();
</script>
EOF;
	}
}