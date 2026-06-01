<?php

namespace App\UI;

use App\Common\str;

class Chart {
	private const BASE_OPTIONS = [
		'responsive' => true,
		'maintainAspectRatio' => false,
		'animation' => false,
		'plugins' => [
			'legend' => [
				'display' => true,
				'position' => 'top',
				'align' => 'start',
				'labels' => [
					'boxWidth' => 10,
					'usePointStyle' => true,
				],
			],
			'tooltip' => [
				'mode' => 'index',
				'intersect' => false,
			],
		],
	];

	private const CARTESIAN_OPTIONS = [
		'interaction' => [
			'mode' => 'index',
			'intersect' => false,
		],
		'scales' => [
			'x' => [
				'grid' => [
					'display' => false,
				],
				'ticks' => [
					'autoSkip' => true,
					'maxRotation' => 0,
				],
			],
			'y' => [
				'beginAtZero' => true,
				'grid' => [
					'color' => 'rgba(15, 23, 42, 0.08)',
				],
			],
		],
	];

	private const RADIAL_OPTIONS = [
		'scales' => [
			'r' => [
				'beginAtZero' => true,
				'grid' => [
					'color' => 'rgba(15, 23, 42, 0.08)',
				],
				'angleLines' => [
					'color' => 'rgba(15, 23, 42, 0.08)',
				],
			],
		],
	];

	private const PALETTE = [
		'#0071B8',
		'#14A349',
		'#C92228',
		'#F16422',
		'#FCD827',
		'#52AAE0',
	];

	public static function palette(): array
	{
		return self::PALETTE;
	}

	public static function colour(int $index, ?float $alpha = NULL): string
	{
		if(array_key_exists($index, self::PALETTE)){
			if($alpha === NULL){
				return self::PALETTE[$index];
			}

			return static::hexToRgba(self::PALETTE[$index], $alpha);
		}

		$hue = ($index * 47) % 360;
		$alpha = $alpha === NULL ? 1 : $alpha;

		return "hsla({$hue}, 68%, 46%, {$alpha})";
	}

	public static function colours(int $count, ?float $alpha = NULL, int $start_index = 0): array
	{
		$colours = [];

		for($i = 0; $i < $count; $i++){
			$colours[] = static::colour($start_index + $i, $alpha);
		}

		return $colours;
	}

	public static function options(string $type, ?array $options = NULL): array
	{
		$default_options = static::getDefaultOptions($type);
		$custom_options = $options ?: [];

		return str::array_merge_recursive_distinct($default_options, $custom_options);
	}

	public static function config(string $type, array $data, ?array $options = NULL): array
	{
		return [
			'type' => $type,
			'data' => $data,
			'options' => static::options($type, $options),
		];
	}

	public static function lineDataset(string $label, array $data, ?array $overrides = NULL, int $colour_index = 0): array
	{
		$dataset = [
			'label' => $label,
			'data' => $data,
			'borderColor' => static::colour($colour_index),
			'backgroundColor' => static::colour($colour_index, 0.12),
			'pointBackgroundColor' => static::colour($colour_index),
			'pointBorderColor' => '#ffffff',
			'pointHoverBackgroundColor' => static::colour($colour_index),
			'pointHoverBorderColor' => '#ffffff',
			'pointRadius' => 3,
			'pointHoverRadius' => 4,
			'borderWidth' => 2,
			'fill' => false,
			'tension' => 0.25,
		];
		$custom_overrides = $overrides ?: [];

		return str::array_merge_recursive_distinct($dataset, $custom_overrides);
	}

	public static function barDataset(string $label, array $data, ?array $overrides = NULL, int $colour_index = 0): array
	{
		$dataset = [
			'label' => $label,
			'data' => $data,
			'borderColor' => static::colour($colour_index),
			'backgroundColor' => static::colour($colour_index, 0.16),
			'hoverBackgroundColor' => static::colour($colour_index, 0.24),
			'borderWidth' => 1,
			'borderRadius' => 4,
		];
		$custom_overrides = $overrides ?: [];

		return str::array_merge_recursive_distinct($dataset, $custom_overrides);
	}

	public static function doughnutDataset(string $label, array $data, ?array $overrides = NULL, int $start_index = 0): array
	{
		$dataset = [
			'label' => $label,
			'data' => $data,
			'backgroundColor' => static::colours(count($data), 0.88, $start_index),
			'hoverBackgroundColor' => static::colours(count($data), 1, $start_index),
			'borderColor' => '#ffffff',
			'borderWidth' => 1,
			'hoverOffset' => 6,
		];
		$custom_overrides = $overrides ?: [];

		return str::array_merge_recursive_distinct($dataset, $custom_overrides);
	}

	public static function generate(array $a): string
	{
		$id = $a['id'] ?? NULL;
		$class = $a['class'] ?? NULL;
		$only_class = $a['only_class'] ?? NULL;
		$style = $a['style'] ?? NULL;
		$only_style = $a['only_style'] ?? NULL;
		$height = $a['height'] ?? NULL;
		$stage_style = $a['stage_style'] ?? NULL;
		$config = $a['config'] ?? NULL;
		$hash = $a['hash'] ?? NULL;
		$autoload = $a['autoload'] ?? true;
		$empty_message = $a['empty_message'] ?? NULL;
		$loading_message = $a['loading_message'] ?? NULL;
		$summary = $a['summary'] ?? NULL;
		$summary_target = $a['summary_target'] ?? NULL;
		$render_summary = array_key_exists('render_summary', $a) ? (bool)$a['render_summary'] : true;
		$script = $a['script'] ?? NULL;

		$id = $id ?: str::id('chart');
		$id_attr = str::getAttrTag('id', $id);
		$class_attr = str::getAttrTag('class', str::getAttrArray($class, [
			'chartjs-chart',
			is_array($hash) ? 'chartjs-ondemand' : NULL,
		], $only_class));
		$style_attr = str::getAttrTag('style', str::getAttrArray($style, [], $only_style));
		$stage_style_array = array_merge([
			'height' => $height ?: '320px',
			'position' => 'relative',
			'width' => '100%',
		], $stage_style ?: []);
		$stage_style_attr = str::getAttrTag('style', $stage_style_array);
		$loading_style_attr = str::getAttrTag('style', array_merge($stage_style_array, [
			'display' => 'none',
		]));
		$data_attr = str::getDataAttr([
			'settings' => $config ? base64_encode(json_encode($config)) : NULL,
			'hash' => $hash,
			'autoload' => $autoload === false ? 'false' : 'true',
			'empty_message' => $empty_message ?: 'No chart data is available for the selected filters.',
			'loading_message' => $loading_message ?: 'Loading chart...',
			'summary' => $summary ?: NULL,
			'summary_target' => $summary_target ?: NULL,
		], true);
		$loading_html = Wait::get($loading_message ?: 'Loading chart');
		$empty_html = $empty_message ?: 'No chart data is available for the selected filters.';
		$summary_html = $summary ?: '&nbsp;';
		$summary_block = $render_summary ? "\n\t<div class=\"chartjs-chart-summary text-muted smallest\" style=\"margin-top:0.75rem;\">{$summary_html}</div>" : '';
		$script_tag = str::getScriptTag($script);

		return <<<HTML
<div{$id_attr}{$class_attr}{$style_attr}{$data_attr}>
	<div class="chartjs-chart-stage"{$stage_style_attr}>
		<canvas class="chartjs-chart-canvas"></canvas>
	</div>
	<div class="chartjs-chart-loading"{$loading_style_attr}>{$loading_html}</div>
	<div class="chartjs-chart-empty text-muted small" style="display:none; margin-top:0.75rem;">{$empty_html}</div>
{$summary_block}
</div>{$script_tag}
HTML;
	}

	private static function getDefaultOptions(string $type): array
	{
		$base_options = self::BASE_OPTIONS;

		switch(strtolower($type)) {
		case 'line':
		case 'bar':
		case 'scatter':
		case 'bubble':
			$type_options = self::CARTESIAN_OPTIONS;
			return str::array_merge_recursive_distinct($base_options, $type_options);
		case 'radar':
			$type_options = self::RADIAL_OPTIONS;
			return str::array_merge_recursive_distinct($base_options, $type_options);
		case 'pie':
		case 'doughnut':
		case 'polararea':
		default:
			return $base_options;
		}
	}

	private static function hexToRgba(string $hex, float $alpha): string
	{
		$hex = ltrim($hex, '#');

		if(strlen($hex) !== 6){
			return "rgba(0, 113, 184, {$alpha})";
		}

		[$red, $green, $blue] = sscanf($hex, '%02x%02x%02x');

		return "rgba({$red}, {$green}, {$blue}, {$alpha})";
	}
}
