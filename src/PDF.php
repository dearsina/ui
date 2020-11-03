<?php


namespace App\UI;


use App\Common\str;

class PDF {
	/**
	 * Given $html, will generate a PDF file from the HTML.
	 * Can be run several times, will only generate the PDF once.
	 *
	 * Will return the file contents, or just the file name if requested.
	 * Either way, will create the file as a local file that should
	 * be deleted at the end of the process using the PDF::delete() method.
	 *
	 * @param array     $a
	 * @param bool|null $return_filename_only
	 *
	 * @return string
	 */
	static function generate(array $a, ?bool $return_filename_only = NULL): string
	{
		extract($a);

		# Get the MD5 hash from the HTML contents
		$md5 = md5($html);

		# Generate temporary filename
		$tmp_filename = PDF::generateTemporaryFilename($md5);

		# If the file doesn't already exist, create it
		if(!file_exists($tmp_filename)){
			# Wrap the HTML in the wrapper
			$html = PDF::wrap($html);

			# Fire up the PDF maker
//			$snappy = new \Knp\Snappy\Pdf('xvfb-run --server-args="-screen 0, 1600x1200x24" /usr/bin/wkhtmltopdf');
			$snappy = new \Knp\Snappy\Pdf('/usr/bin/wkhtmltopdf');

			$options = [
				"page-size" => "A4",
				# Will trigger an error if Qt isn't patched
//				"disable-smart-shrinking" => "true",
				"margin-top" => 0,
				"margin-bottom" => 0,
				"margin-left" => 0,
				"margin-right" => 0,
			];

			# None of the below will work without patching QT
//			$snappy->setOption('page-width', '1600px');
//			$snappy->setOption('page-height', '1200px');
//			$snappy->setOption('viewport-size', '1600x1200');
//			$snappy->setOption('orientation', 'Landscape');
//			$snappy->setOption('use-xserver', 'true'); //Won't work without patched QT

//			$snappy->setOption('header-font-name', 'Barlow');
//			$snappy->setOption('header-font-size', 8);
//			$snappy->setOption('header-line', true);
//			$snappy->setOption('header-spacing', 10);
//			$snappy->setOption('header-left', "Header left");
//			$snappy->setOption('header-right', "Header right");
//
//			$snappy->setOption('footer-font-name', 'Barlow');
//			$snappy->setOption('footer-font-size', 6);
//			$snappy->setOption('footer-line', true);
//			$snappy->setOption('footer-spacing', 10);
//			$snappy->setOption('footer-left', "Footer left text");
//			$snappy->setOption('footer-right', "[page] of [toPage]");

			# Generate the PDF
			$snappy->generateFromHtml($html, $tmp_filename, $options);
//			$snappy->generate("http://whatismyscreenresolution.net/", $tmp_filename, $options);
//			$snappy->generate("https://css-tricks.com/examples/ResizeAtFullRes/", $tmp_filename);
		}

		# Return the file (or just filename)
		return $return_filename_only ? $tmp_filename : file_get_contents($tmp_filename);
	}

	/**
	 * Can accept either the tmp filename, or the HTML string.
	 * Will delete the temporary file (only).
	 *
	 * DON'T SEND THE PDF CONTENT
	 *
	 * @param string $a
	 *
	 * @return bool
	 */
	public static function delete(string $a): bool
	{
		if(strlen($a) == strlen(sys_get_temp_dir()) + 1 + 32){
			$tmp_filename = $a;
		} else {
			$md5 = md5($a);

			# Generate temporary filename
			$tmp_filename = PDF::generateTemporaryFilename($md5);
		}

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
		return <<<EOF
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="https://{$_ENV['app_subdomain']}.{$_ENV['domain']}/css/app.css">
	</head>
	<body style="background-color: unset !important; height: unset !important;margin:20px;padding:20px;">
		{$html}
	</body>
</html>
EOF;
	}

	static private function generateTemporaryFilename(?string $file_name = NULL): string
	{
		# Generate an arbitrary filename
		$tmp_file_name = $file_name ?: str::uuid();

		# Generate a tmp path + name
		$tmp_dir = sys_get_temp_dir();
		return "{$tmp_dir}/{$tmp_file_name}";
	}
}