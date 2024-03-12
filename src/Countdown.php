<?php


namespace App\UI;

use App\Common\str;

/**
 * Class Countdown
 * @package App\UI
 */
class Countdown {

	/**
	 * Generates a countdown.
	 * All attributes are optional.
	 *
	 * <code>
	 * Countdown::generate([
	 * 	"id" => , //A div ID
	 * 	"class" => , //(Optional) additional classes
	 * 	"style" => , //(Optional) style
	 * 	"datetime" => , //A (mySQL) datetime string
	 * 	"modify" => , //(Optional) A string modifying the datetime (if not set, will use restart)
	 * 	"pre" => , //Text that goes before the timer
	 * 	"post" => , //Text that goes after the timer
	 * 	"stop" => , //Text to replace pre+timer+post with once the time is up.
	 * 	"precision" => ,//How frequently (in milliseconds) the countdown is updated.
	 * 	"format" => , //A custom format of time
	 * 	"callback" => , //(Optional) The name of a function to call at zero (if not set, will default to "onDemandReset")
	 * 	"vars" => , //Variables to send to the callback function
	 * 	"restart" => [ (Required, how long is the timer for?)
	 * 		"minutes" => 5,
	 * 		"hours" => 2,
	 * 	]
	 * ]);
	 * </code>
	 *
	 * Relies on a listener on the .countdown class.
	 *
	 * @param array|null $a
	 * @link http://hilios.github.io/jQuery.countdown/documentation.html#formatter
	 * @return string
	 * @throws \Exception
	 */
	public static function generate(?array $a = []) : string
	{
		extract($a);

		# Set the time (if blank, will be NOW())
		$dt = new \DateTime($datetime ?: "now");

		# As the server time is GMT/BST, adjust the time to UTC
		$dt->setTimezone(new \DateTimeZone('UTC'));

		# Modify the time
		if($modify){
			$dt->modify($modify);
		} else if ($restart){
			foreach($restart as $metric => $length){
				$modify_array[] = "+{$length} {$metric}";
			}
			$modify = implode(" ",$modify_array);
			$dt->modify($modify);
		}

		# Stop?
		if($stop){
			$settings["elapse"] = false;
		} else {
			$settings["elapse"] = true;
		}

		# Restart
		if($restart){
			$settings["elapse"] = false;
		}

		# Callback (unless explicitly set to not have a callback, the default is onDemandReset)
		if($callback !== false){
			$callback = $callback ?: "onDemandReset";
		}

		# Precision
		$settings["precision"] = $precision;

		# ID
		$id = $id ?: str::id("countdown");

		# Class
		$class_array = str::getAttrArray($class, "countdown", $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		$data_array = [
			"settings" => $settings,
			"utc" => $dt->format("Y-m-d H:i:s\\Z"),
			"pre" => $pre,
			"post" => $post,
			"stop" => $stop,
			"callback" => $callback,
			"vars" => $vars,
			"restart" => $restart
		];
		$data_attr = str::getDataAttr($data_array);

		$pause_play = Button::generate([
			"alt" => "Pause/restart the counter",
			"icon" => "pause",
			"basic" => true,
			"colour" => "grey",
			"onClick" => "countdownStopStart.call(this);",
			"size" => "s",
			"ladda" => false
		]);

		if($callback){
			//if something happens after the countdown finishes
			$refresh = Button::generate([
				"alt" => "Refresh now",
				"icon" => "recycle",
				"basic" => true,
				"colour" => "grey",
				"onClick" => "countdownRestart.call(this);",
				"size" => "s",
				"ladda" => false
			]);
			$buttons = "<div class=\"btn-group\" role=\"group\">{$pause_play}{$refresh}</div>";
		}

		$id = str::getAttrTag("id", $id);

		return <<<EOF
<div class="countdown-wrapper">
	<div{$id}{$class}{$style}{$data_attr}></div>
	{$buttons}
</div>
EOF;
	}
}