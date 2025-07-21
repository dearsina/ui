<?php

namespace App\UI\Latex;

use App\Common\str;

class Latex {
	/**
	 * Returns a base64 encoded PNG image of the LaTeX formula.
	 *
	 * @param string $formula
	 *
	 * @return string
	 */
	public static function latexToPng(string $formula): string
	{
		// Remove % for PHP evaluation
		$phpCalc = str_replace('%', '', $formula);
		eval('$result = ' . $phpCalc . ';');
		$result = number_format($result, 2);

		$formula = str_replace('%', '\%', $formula);

		$latex = <<<LATEX
\\documentclass[preview]{standalone}
\\usepackage{amsmath}
\\begin{document}
\\[
$formula = $result
\\]
\\end{document}
LATEX;

		$filename = $_ENV['tmp_dir'] . str::uuid();

		// Write LaTeX file
		file_put_contents("{$filename}.tex", $latex);
		# Compile LaTeX to PDF
		str::exec("pdflatex --shell-escape {$filename}.tex", $output);

		# Check if PDF was created
		if (!file_exists("{$filename}.pdf")) {
			throw new \Exception("Failed to create PDF from LaTeX.");
		}

		# Convert PDF to PNG
		str::exec("convert -density 300 -trim {$filename}.pdf {$filename}.png", $output);

		# Check if PNG was created
		if (!file_exists("{$filename}.png")) {
			throw new \Exception("Failed to create PNG from PDF.");
		}

		# Read PNG file
		$png = file_get_contents("{$filename}.png");

		# Clean up temporary files
		shell_exec("rm {$filename}.aux {$filename}.log {$filename}.pdf {$filename}.tex {$filename}.png");

		return 'data:image/png;base64,' . base64_encode($png);
	}
}