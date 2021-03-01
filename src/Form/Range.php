<?php


namespace App\UI\Form;


use App\Common\str;

/**
 * Class Range
 * @package App\UI\Form
 */
class Range extends Field implements FieldInterface {

	/**
	 * The range slider is just an input field,
	 * with Javascript and CSS applied for looks.
	 * The following attributes are allowed:
	 * <code>
	 * "type" => "range",
	 * "name" => "estimate",
	 * "desc" => "desc",
	 * "label" => "label",
	 * "value" => $estimate !== null ? $estimate : .5,
	 * "alt" => "Your best guess at the odds of this guest attending.",
	 * "min" => 0, // Minimum value
	 * "max" => 1, // Maximum value
	 * "step" => 0.1, //Step
	 * "multiple" => 100, //Step * Multiple is displayed
	 * "prefix" => "P", //Prefix to displayed value
	 * "suffix" => "%", //Suffix to displayed value
	 * "min_colour" => [255,0,0], //Start colour (R,G,B)
	 * "max_colour" => [0,255,0], //End colour (R,G,B)
	 * </code>
	 *
	 * @param array $a
	 *
	 * @return string
	 */
	public static function generateHTML (array $a) {
		$a['script'] = self::getRangeScript($a);
		$a['value'] = $a['value'] === NULL ? ($a['default'] ?: $a['min']) : $a['value'];
		$a['parent_class'] = str::getAttrArray($a['parent_class'], "input-group-range");
		return Input::generateHTML($a);
	}

	/**
	 * This script is added to a default input field,
	 * to style the range slider.
	 *
	 * @param $a
	 *
	 * @return string
	 */
	private static function getRangeScript($a){
		extract($a);

		$rangeslider_id = str::id("rangeslider");
		/**
		 * This is the ID of the generated range slider,
		 * not of the range field holding the value.
		 * It's used internally only so that more than
		 * one range slider can be generated on one page,
		 * and share common functions.
		 */

		# Remove linebreaks and tags.
		$alt = str::i(str_replace(["\r\n","\r","\n"], " ", $alt));

		return /** @lang JavaScript */<<<EOF
$(document).ready(function () {
	
	var id = "{$rangeslider_id}";
	var min_hsl = RGB2HSL([{$min_colour[0]}, {$min_colour[1]}, {$min_colour[2]}]);
	var max_hsl = RGB2HSL([{$max_colour[0]}, {$max_colour[1]}, {$max_colour[2]}]);
	var step = "$step";
	var multiple = "$multiple";
	var prefix = "$prefix";
	var suffix = "$suffix";
		
	$('#{$id}').rangeslider({
	
		// Feature detection the default is `true`.
		// Set this to `false` if you want to use
		// the polyfill also in Browsers which support
		// the native <input type="range"> element.
		polyfill: false,
	
		// Default CSS classes
		rangeClass: 'rangeslider',
		disabledClass: 'rangeslider--disabled',
		horizontalClass: 'rangeslider--horizontal',
		verticalClass: 'rangeslider--vertical',
		fillClass: 'rangeslider__fill',
		handleClass: 'rangeslider__handle',
	
		// Callback function
		onInit: function() {
		    // Give the range slider an ID that can be referenced throughout the code
		    this.\$range[0].setAttribute('rangeslider_id', id);
		    
		    // Get the range value (set in the input tag, not the rangeslider div)
		    var value = $('#{$id}').val();
		    
		    // Set the alt text
		    $(".rangeslider__handle", "[rangeslider_id=" + id + "]").attr("title","{$alt}");

		    // Set the colour
		    setRangeColour(id, value, this.max, min_hsl, max_hsl);
		    
      		updateRangeHandle(id, this.value, multiple, step, prefix, suffix);
		},
	
		// Callback function
		onSlide: function(position, value) {
		    // Update the range colour
		    setRangeColour(id, value, this.max, min_hsl, max_hsl);
		    
		    // Update the range text
    		updateRangeHandle(id, value, multiple, step, prefix, suffix);
		},
	
		// Callback function
		onSlideEnd: function(position, value) {}
	});
	
	window.setTimeout(function(){
	    //Slider needs recalculation to fit into a modal
	    $('#{$id}').rangeslider('update', true);
	}, 500);
});
{$script}
EOF;
	}
}