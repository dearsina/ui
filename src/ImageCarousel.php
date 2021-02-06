<?php


namespace App\UI;


use App\Common\Img;
use App\Common\str;

class ImageCarousel extends \App\Common\Common {
	/**
	 * Will generate an image carousel,
	 * assuming either an array of photos,
	 * or an array of Img::generate() method arrays
	 * have been included.
	 *
	 * Settings can also be added.
	 *
	 * <code>
	 * ImageCarousel::generate([
	 * 	"images" => $images,
	 * 	"settings" => [
	 * 		"pageDots" => true
	 * 	]
	 * ]);
	 * </code>
	 *
	 * The images array can be anything Img::generate can handle.
	 *
	 * <code>
	 * 	$images[] = [
	 * 	"src" => $attribute['value'],
	 * 	"style" => [
	 * 		"width" => "100%",
	 * 		"object-fit" => "cover",
	 * 		"display" => "block",
	 * 		"height" => "250px",
	 * 	],
	 * ];
	 * </code>
	 *
	 *
	 * @param array $a
	 *
	 * @return string|null
	 */
	public static function generate(array $a): ?string
	{
		if(str::isNumericArray($a)){
			$images = $a;
		} else {
			extract($a);
		}

		if(!$images){
			return NULL;
		}

		foreach($images as $image){
			$generated_images[] = Img::generate($image);
		}

		# ID (optional)
		$id = str::getAttrTag("id", $id ?: str::id("image_carousel"));

		# Class
		$class_array = str::getAttrArray($class, ["flickity"], $only_class);
		$class = str::getAttrTag("class", $class_array);

		# Style
		$style = str::getAttrTag("style", $style);

		# Settings (will override any existing data settings)
		$data['settings'] = $settings;

		# Data
		$data = str::getDataAttr($data);

		return "<div{$id}{$class}{$style}{$data}>".implode("\r\n", $generated_images)."</div>";

	}
}