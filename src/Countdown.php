<?php


namespace App\UI;

use App\Common\str;

class Countdown {

	/**
	 * Generates a countdown.
	 * All attributes are optional.
	 *
	 * <code>
	 * Countdown::generate([
	 * 	"id" => , //A div ID
	 * 	"datetime" => , //A (mySQL) datetime string
	 * 	"modify" => , //A string modifying the datetime
	 * 	"pre" => , //Text that goes before the timer
	 * 	"post" => , //Text that goes after the timer
	 * 	"stop" => , //Text to replace pre+timer+post with once the time is up.
	 * 	"precision" => ,//How frequently (in milliseconds) the countdown is updated.
	 * 	"format" => , //A custom format of time
	 * 	"callback" => , //The name of a function to call at zero
	 * 	"vars" => , //Variables to send to the callback function
	 * 	"restart" => [
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
		$dt = new \DateTime($datetime);

		# As the server time is GMT/BST, adjust the time to UTC
		$dt->setTimezone(new \DateTimeZone('UTC'));

		# Modify the time
		if($modify){
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

		# Precision
		$settings["precision"] = $precision;

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
		return "<span class=\"countdown\"{$data_attr}></span>";
	}
}