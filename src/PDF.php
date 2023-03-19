<?php


namespace App\UI;


use App\Common\Log;
use App\Common\PrintRun\PrintRun;
use App\Common\str;

class PDF {
	/**
	 * "Prints" a webpage as PDF using a headless Google Chrome instance.
	 * Saves the PDF as a temporary file and returns either the file contents
	 * or temporary file name.
	 *
	 * @param            $url
	 * @param int|null   $seconds
	 * @param array|null $older_output
	 * @param bool|null  $return_tmp_filename_only
	 * @param int|null   $rerun
	 *
	 * @return ?string Returns the actual PDF string or filename (if requested) or NULL on error
	 * @throws \Exception
	 * @link https://developers.google.com/web/updates/2017/04/headless-chrome
	 */
	static function print($url, ?int $seconds = 10, ?string $filename = NULL, ?bool $return_tmp_filename_only = NULL, ?int $rerun = 0, ?bool $silent = NULL): ?string
	{
		# Get the MD5 hash from the URL
		$md5 = md5($url);

		# Log the start of this print run
		if(!PrintRun::start($md5, $filename, $rerun, $silent)){
			// If we cannot start, we stop
			return NULL;
		}

		# Generate temporary filename
		$tmp_filename = PDF::generateTemporaryFilename($md5);

		# If for some reason the temporary file already exists, remove it
		if(file_exists($tmp_filename)){
			unlink($tmp_filename);
		}

		# An easier way to structure the CLI settings
		$settings = self::getChromeSettings($seconds, $tmp_filename);

		# Execute the headless command
		$log = shell_exec("google-chrome {$settings} '{$url}' 2>&1");
		// 2>&1 means that any output is piped to the stdout (stored in the $output variable)

		# Ensure the file can be accessed by all
		shell_exec("chmod 777 {$tmp_filename} 2>&1");

		# Ensure the PDF was generated successfully, if not, keep trying until it is
		if(!file_exists($tmp_filename) || (filesize($tmp_filename) < 3000) && (strpos($log, "ERROR:") !== false)){
			// If the file isn't created, or the Chrome log contains an error and the file is less than 3kb (if it's a dud)
			// At times, the log will contain an error but the PDF will still be generated, so we need to check the filesize

			# Count the number of attempts
			$rerun++;

			# Increase the seconds given to load the page
			$seconds++;

			if($rerun < 1000){
				$dt = \DateTime::createFromFormat('U.u', microtime(true));
				file_put_contents($_ENV['tmp_dir'] . "pdf.log", "RUN{$rerun} {$md5} " . $dt->format("H:i:s.u") . " " . str::stopTimer() . PHP_EOL . $log . PHP_EOL, FILE_APPEND);
				// Should only be used for testing purposes

				# Run it again
				return self::print($url, $seconds, $filename, $return_tmp_filename_only, $rerun);
			}

			# Stop this print run
			PrintRun::stop($md5, $rerun);

			# Error to the user
			Log::getInstance()->error([
				"log" => false,
				"title" => "PDF generation failed",
				"message" => "PDF generation failed, please try that again. Apologies for the inconvenience.",
			]);

			# Error for the log
			Log::getInstance()->error([
				"display" => false,
				"title" => "PDF generation failed",
				"message" => "{$rerun} attempts were made to make a PDF from the following URL without success: <code>{$url}</code> <code>{$settings}</code>.
					The following error message was returned: <code>{$log}</code> That took " . str::stopTimer() . " seconds.",
			]);

			return NULL;
		}

		if($rerun > 10){
			Log::getInstance()->error([
				"display" => false,
				"title" => "PDF took {$rerun} tries",
				"message" => "{$rerun} attempts were made before a PDF was made from the following URL: <code>{$url}</code> <code>{$settings}</code>. That took " . str::stopTimer() . " seconds.",
			]);
		}

		# Log the finish of this print run
		PrintRun::stop($md5, $rerun);

		if($return_tmp_filename_only){
			return $tmp_filename;
		}

		$tmp_contents = file_get_contents($tmp_filename);

		if(file_exists($tmp_filename)){
			unlink($tmp_filename);
		}

		return $tmp_contents;
	}

	/**
	 * Contains all the settings required to launch Google Chrome
	 * headless via CLI in a minimum-viable-product type way to
	 * consume as little memory as possible. Returns a string
	 * with the settings formatted in the required way.
	 *
	 * @param int    $seconds
	 * @param string $tmp_filename
	 *
	 * @return string
	 * @link https://peter.sh/experiments/chromium-command-line-switches
	 */
	public static function getChromeSettings(?int $seconds, string $tmp_filename): string
	{
		return self::formatHeadlessChromeSettings([
			# Run Chrome headless
			"headless" => true,
			# Wait till Javascript has done its job before drawing/printing
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

			# Attempts to fix the ERROR:command_buffer_proxy_impl.cc(128)] ContextResult::kTransientFailure: Failed to send GpuControl.CreateCommandBuffer. issue
			"disable-blink-features" => "AutomationControlled",
			// Experimental, doesn't seem to have much impact
			// @link https://stackoverflow.com/questions/70245747/webdriver-headless-issue

			# Attempts to fix the WARNING:sandbox_linux.cc(376)] InitializeSandbox() called with multiple threads in process gpu-process.
			"disable-software-rasterizer" => true,
			// Experimental, addressing the error by avoiding the GPU hardware acceleration with the above flags
			// @link https://stackoverflow.com/a/69037769/429071
			// @link https://stackoverflow.com/a/67578811/429071

			"disable-crash-reporter" => true,
			// Disable crash reporter for headless. It is enabled by default in official builds.

			"disable-seccomp-filter-sandbox" => true,
			// Disable the seccomp filter sandbox (seccomp-bpf) (Linux only).

			"disable-setuid-sandbox" => true,
			//	Disable the setuid sandbox (Linux only).

			"no-zygote" => true,
			/**
			 * Disables the use of a zygote process for forking child processes.
			 * Instead, child processes will be forked and exec'd directly.
			 * Note that --no-sandbox should also be used together with this flag because the sandbox needs the zygote to work.
			 */

			"disable-background-networking" => true,
			"disable-default-apps" => true,
			"disable-extensions" => true,
			"disable-sync" => true,
			"disable-translate" => true,
			"hide-scrollbars" => true,
			"metrics-recording-only" => true,
			"mute-audio" => true,
			"no-first-run" => true,
			"ignore-certificate-errors" => true,
			"ignore-ssl-errors" => true,
			"ignore-certificate-errors-spki-list" => true,
			/**
			 * Inspired by the below message. Gets rid of a
			 * bunch of stuff not needed for pdf printing.
			 * Depreciated flags have been removed.
			 *
			 * @link https://groups.google.com/a/chromium.org/g/headless-dev/c/f_tQUs__Yqw/m/nNYisdDBCwAJ
			 */

			// "trace-startup" => "*,disabled-by-default-memory-infra"
			// Will produce massive startup log file, is NOT needed


			// "disable-gpu" => true, // Only useful on Windows, will fail in Linux
			// Will give the following error: ERROR:gpu_init.cc(523) Passthrough is not supported, GL is disabled, ANGLE is

			// "disable-webgl" => true,

			// "use-gl" => "desktop",
			// Will fail

			// Adds a lot more output, not super useful, makes the PDF log very big
//			"enable-logging" => "stderr",
//			"v" => 1,

			// "user-data-dir" => "/var/www/tmp",
			/**
			 * Directory where the browser stores the user profile.
			 * Note that if this switch is added, the session will no longer be Incognito,
			 * unless Incognito mode is forced with --incognito switch
			 */

		]);
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
			
			#ui-view {margin-top: unset !important; }
			/** Will ignore the automatically generated margin top **/
			
			body { margin-top: 0; margin-left: 1cm; margin-right: 1cm; background-color: white; height: unset; zoom: 75%; }
			/** We're zooming out to 75% to allow for text to be legible */
			
			main { margin: 0; padding: 0; max-width: unset !important; }
			
			/** When printed, .containers will shrink if the max-width limit isn't removed */
			.container {max-width: unset !important;}
			
			/** For this to work, the .row immediately outside the card will need to be set to display:block */
			.card{ break-inside: avoid; page-break-inside:avoid; }
			
			/** Things we don't want to see in print view */
			
			/** This will avoid card colliding with the footer */
			main > .row > .col-sm > .row {
			    padding-bottom: 0.6cm;
			}
			
			/** The little chevron down */
			.collapse-toggle::after {display:none;}
			
			/** For optional footers */
			footer {
				position: fixed;
				bottom: 0;
				width: 100%;
				color: black;
				background: white;
			}
		}		
		</style>
EOF;
	}

	/**
	 * Similar to the above, but hides all headers/footers.
	 *
	 * I'm not sure why the fakeLoader doesn't hide by itself,
	 * but it does seem to solve an edge case and there is no
	 * harm in removing it.
	 *
	 * @return string
	 */
	public static function getDocFillCss(?string $format = NULL): string
	{
		# If no format is given, assume it's A4
		$format = $format ?: "A4";

		return <<<EOF
		<style>
		@media print {
			.fakeLoader {display: none;}
			@page { margin:0; padding: 0; size: $format; }
			.pace { display:none; }
			#ui-navigation { display:none; }
			#ui-footer { display:none; }
			
			#ui-view {margin-top: unset !important; }
			/** Will ignore the automatically generated margin top **/
			
			body { margin: 0; padding: 0; background-color: white; }
			main { margin: 0; padding: 0; max-width: unset !important; }
		}
		</style>
EOF;
	}


	public static function getDashboardCss(): string
	{
		return <<<EOF
		<style>
		@media print {
			.fakeLoader {display: none;}
			@page { size: A4; margin-top: 0; margin-left: 0; margin-right: 0; margin-bottom: 1.5cm;}
			.pace { display:none; }
			#ui-navigation { display:none; }
			#ui-footer { display:none; }
			
			#ui-view {margin-top: unset !important; }
			/** Will ignore the automatically generated margin top **/
			
			body { margin-top: 0; margin-left: 1cm; margin-right: 1cm; background-color: white; height: unset; zoom: 75%;}
			main { margin: 0; padding: 0; max-width: unset !important;}
			
			/** When printed, .containers will shrink if the max-width limit isn't removed */
			.container {max-width: unset !important;}
			
			/** For this to work, the .row immediately outside the card will need to be set to display:block */
			.card{ break-inside: avoid; page-break-inside:avoid; }
			
			/** Things we don't want to see in print view */
			
			/** Buttons in the header */
			.col-buttons {display:none !important;}
			
			/** Population cards cannot be hidden **/ 
			.card-body-population {
				max-height: unset !important;
				height: unset !important;
			}
			
			/** No point having buttons either **/
			.card-body-population .btn {
				display: none !important;
			}
			
			/** Make sure the entire list is shown **/
			.card-body-population {
				overflow-y: hidden !important;
			}
			
			.header, .header-space{
			  height: 3cm;
			}
			
			.header {
				position: fixed;
				top: 0;
				background: white;
				color: black;
				width: 100%;
				right: 1cm;		
				padding-top: 1cm;
				padding-left: 2.2cm;
			}
			
			.header-line {
				border-bottom: 0.9px solid #d8e2e9;
				padding-bottom:1cm;
				margin-right:.3cm;
				
				text-transform: uppercase;
				letter-spacing: 1.5px;
				font-weight: 400;
				font-size: smaller;
			}
			.header-left{
				float: left;
				height: 0;
			}
			.header-centre{
				float: left;
				width: 100%;
				text-align: center;
				height: 0;
				font-weight: 800;
			}
			.header-right{
				float:right;
			}
		}
		
		@media screen {
			.header {
				display: none;
			}
		}
			
		table {
			width: 100%;
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
		}

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