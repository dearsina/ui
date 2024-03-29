<?php


namespace App\UI\Examples;


use App\Common\EmailWrapper\EmailWrapper;
use App\Common\Example\ExampleInterface;
use App\Common\href;
use App\Common\Prototype;
use App\Common\str;
use App\UI\Grid;

class EmailMessage extends Prototype implements ExampleInterface {

	/**
	 * @inheritDoc
	 */
	public function getHTML($a = NULL)
	{
		$grid = new Grid();

		$link = href::a([
			"html" => "https://{$_ENV['domain']}",
			"url" => "https://{$_ENV['domain']}"
		]);

		$year = date("Y");

		$examples[] = [
			"header" => "Welcome email",
			"array" => [
				"title_colour" => "primary",
				"colour" => "#000",
				"header" => [
					"logo" => [
						"logo_url" => "https://kycdd.co.za",
						"logo_src" => "https://kycdd.co.za/assets/img/kycdd_logo_v4_black.png",
						"image_height" => 30,
						"image_width" => 100,
						"alt" => "Click here to go to KYCDD"
					]
				],
				"body" => [[
					"bg_colour" => "silent",
					"image" => [
						"src" => "https://kycdd.co.za/assets/img/welcome_v1.png",
					],
					"copy" => [
						"title" => [
							"align" => "left",
							"title" => "Welcome to {$_ENV['title']}!"
						],
						"body" => [
							"body" => "An account has been set up for you. Please press the button below to verify your email address and set up a password.",
							"align" => "left"
						]
					],
					"button" => [
						"colour" => "primary",
						"title" => "Verify email",
						"url" => "https://{$_ENV['domain']}"
					]
				],[
					"bg_colour" => "silent",
					"copy" => [
						"body" => [
							"colour" => "grey",
							"body" => [
								"You can alternatively copy and paste the below link in your browser window.",
								$link
							],
							"align" => "left"
						]
					],
				]],
				"footer" => [
					"colour" => "grey",
					"footer" => [
						"Sent by KYC DD (Pty) Limited
                    <br>Office 203, 139 Greenway, Greenside, Johannesburg, 2193.
                    <br>Incorporated in South Africa, 2020/181847/07.
                    <br>Copyright © {$year}, All rights reserved.",
						"Next row"
					]
				]
			]
		];

		$examples[] = [
			"header" => "Header",
			"array" => [
				"title_colour" => "primary",
				"colour" => "#000",
				"header" => [
					"title" => [
						"header_text" => "Right aligned header text<br> with line break and own colour.",
						"header_text_colour" => "grey",
						"style" => [
							"font-style" => "italic"
						]
					],
					"logo" => [
						"logo_url" => "https://kycdd.co.za",
						"logo_src" => "https://kycdd.co.za/assets/img/kycdd_logo_v4_black.png",
						"image_height" => 30,
						"image_width" => 100,
						"alt" => "Click here to go to KYCDD"
					]
				],
			]
		];

		$examples[] = [
			"header" => "Image, header, body, button",
			"array" => [
				"title_colour" => "primary",
//				"colour" => "orange",
				"body" => [[
					"image" => [
						"src" => "https://kycdd.co.za/assets/img/salt/responsive-email.png",
						"alt" => "Can an email really be responsive?",
						"url" => "https://kycdd.co.za",
					],
					"copy" => [
						"title" => [
							"title" => "Yes. Email can be responsive, too.",
							"align" => "left",
						],
						"body" => [
							"body" => "Maecenas sed ante pellentesque, posuere leo id, eleifend dolor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Praesent laoreet malesuada cursus. Maecenas scelerisque congue eros eu posuere. Praesent in felis ut velit pretium lobortis rhoncus ut erat.",
							"align" => "left",
						],
					],
					"button" => [
						"title" => "Red button",
						"colour" => "red",
						"url" => "https://kycdd.co.za",
					]
				]]
			]
		];

		$examples[] = [
			"header" => "Article snippet",
			"array" => [
				"title_colour" => "primary",
//				"colour" => "grey",
				"bg_colour" => "silent",
				"body" => [[
					"articles" => [[
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
							"alt" => "Litmus",
							"url" => "https://kycdd.co.za",
						],
						"pretitle" => "Pretitle",
						"title" => "Title",
						"body" => "This article has every element available. Pretitle, title, body, button and image.",
						"button" => [
							"title" => "Primary button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					],[
						//						"image" => [
						//							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
						//							"alt" => "Litmus",
						//							"url" => "https://kycdd.co.za",
						//						],
						"pretitle" => "Pretitle",
						"title" => "No image to my left",
						"body" => "This article has every element available except image. Pretitle, title, body, button but no image.",
						"button" => [
							"title" => "Primary button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					],[
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
							"alt" => "Litmus",
							"url" => "https://kycdd.co.za",
						],
						//						"pretitle" => "Pretitle",
						//						"title" => "No text just button",
						//						"body" => "This article has every element available except image. Pretitle, title, body, button but no image.",
						"button" => [
							"title" => "Just image and button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					]]
				]]
			]
		];

		$examples[] = [
			"header" => "Two columns",
			"array" => [
//				"title_colour" => "primary",
//				"colour" => "orange",
				"body" => [[
					"copy" => [
						"title" => [
							"title" => "Many rows",
							"colour" => "#000",
						],
						"body" => "Order of keys matter, any can be omitted.",
					],
					"columns" => [[[
						"title" => "Left title",
						"body" => "Left body."
					],[

						"title" => "Right title",
						"body" => "Right body."
					]],[[
						"title" => "Left title",
						"body" => "This row has images also.",
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/fluid-images.png",
							"alt" => "Can an email really be responsive?",
							"url" => "https://kycdd.co.za",
						],
					],[

						"title" => "Right title",
						"body" => "Right body.",
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/fluid-images.png",
							"alt" => "Can an email really be responsive?",
							"url" => "https://kycdd.co.za",
						],
					]],[[
						"title" => [
							"title" => "Left title",
							"bg_colour" => "red",
						],
						"body" => [
							"body" => "This cell has a custom background and text colour.",
							"colour" => "white",
							"bg_colour" => "red"
						]
					],[

						"title" => "Right title",
						"body" => "Right body."
					]]],
					"button" => [
						"title" => "You can still have buttons",
						"colour" => "green",
						"url" => "https://kycdd.co.za",
					],
				]]
			]
		];

		$examples[] = [
			"header" => "All sections",
			"array" => [
				"title_colour" => "primary",
				"colour" => "#000",
				"header" => [
					"title" => "Right aligned header text<br> with line break and own colour.",
					"logo" => [
						"logo_url" => "https://kycdd.co.za",
						"logo_src" => "https://kycdd.co.za/assets/img/kycdd_logo_v4_black.png",
						"image_height" => 30,
						"image_width" => 100,
						"alt" => "Click here to go to KYCDD"
					]
				],
				"body" => [[
					"colour" => "#000",
					"image" => [
						"src" => "https://kycdd.co.za/assets/img/salt/responsive-email.png",
						"alt" => "Can an email really be responsive?",
						"url" => "https://kycdd.co.za",
					],
					"copy" => [
						"title" => "Yes. Email can be responsive, too.",
						"body" => "Using fluid structures, fluid images, and media queries, we can make email (nearly) as responsive as modern websites.",
					],
					"button" => [
						"title" => "Red button",
						"colour" => "red",
						"url" => "https://kycdd.co.za",
					]
				],[
					"bg_colour" => "#CCC",
					"copy" => [
						"title" => [
							"title" => "Only copy and button.",
							"colour" => "#000",
						],
						"body" => "Order of keys matter, any can be omitted.",
					],
					"columns" => [[
						"title" => "Left title",
						"body" => "Left body."
					],[

						"title" => "Right title",
						"body" => "Right body."
					]],
					"button" => [
						"title" => "Green button",
						"colour" => "green",
						"url" => "https://kycdd.co.za",
					],
				],[
					"bg_colour" => "#FFF",
					"copy" => [
						"title" => [
							"title" => "Many rows",
							"colour" => "#000",
						],
						"body" => "Order of keys matter, any can be omitted.",
					],
					"columns" => [[[
						"title" => "Left title",
						"body" => "Left body."
					],[

						"title" => "Right title",
						"body" => "Right body."
					]],[[
						"title" => "Left title",
						"body" => "Left body.",
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/fluid-images.png",
							"alt" => "Can an email really be responsive?",
							"url" => "https://kycdd.co.za",
						],
					],[

						"title" => "Right title",
						"body" => "Right body.",
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/fluid-images.png",
							"alt" => "Can an email really be responsive?",
							"url" => "https://kycdd.co.za",
						],
					]],[[
						"title" => [
							"title" => "Left title",
							"bg_colour" => "red",
						],
						"body" => [
							"body" => "Left body.",
							"bg_colour" => "red"
						]
					],[

						"title" => "Right title",
						"body" => "Right body."
					]]],
					"button" => [
						"title" => "Green button",
						"colour" => "green",
						"url" => "https://kycdd.co.za",
					],
				],[
					"copy" => [
						"title" => "Image can be at the bottom.",
						"body" => [
							"body" => "All you need to do is shift the order of the keys; image, copy, button.",
							"colour" => "#000",
						],
					],
					"button" => [
						"title" => "Blue button",
						"colour" => "blue",
						"url" => "https://kycdd.co.za",
					],
					"image" => [
						"src" => "https://kycdd.co.za/assets/img/salt/responsive-email.png",
						"alt" => "Can an email really be responsive?",
						"url" => "https://kycdd.co.za",
					],
				],[
					"bg_colour" => "silent",
					"articles" => [[
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
							"alt" => "Litmus",
							"url" => "https://kycdd.co.za",
						],
						"pretitle" => "Pretitle",
						"title" => "Title",
						"body" => "This article has every element available. Pretitle, title, body, button and image.",
						"button" => [
							"title" => "Primary button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					],[
//						"image" => [
//							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
//							"alt" => "Litmus",
//							"url" => "https://kycdd.co.za",
//						],
						"pretitle" => "Pretitle",
						"title" => "No image to my left",
						"body" => "This article has every element available except image. Pretitle, title, body, button but no image.",
						"button" => [
							"title" => "Primary button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					],[
						"image" => [
							"src" => "https://kycdd.co.za/assets/img/salt/litmus-logo.png",
							"alt" => "Litmus",
							"url" => "https://kycdd.co.za",
						],
//						"pretitle" => "Pretitle",
//						"title" => "No text just button",
//						"body" => "This article has every element available except image. Pretitle, title, body, button but no image.",
						"button" => [
							"title" => "Just image and button",
							"colour" => "primary",
							"url" => "https://kycdd.co.za",
						]
					]]
				]],
				"footer" => [
					"footer" => [
						"Footer text",
						"second line"
					],
					"style" => [
						"color" => "grey"
					]
				]
			]
		];

		foreach($examples as $example){
			$grid->set($this->getEmailCard($example));
		}

		$this->output->html($grid->getHTML());
	}

	private function getEmailCard(array $a): string
	{
		extract($a);

		$email_message = new \App\UI\EmailMessage(EmailWrapper::$defaults, $array);

		$card = new \App\UI\Card\Card([
			"header" => $header,
			"body" => ["html" => [[[
				"html" => str::pre($array, [
					"language" => "php"
				]),
				"sm" => 6
			],[
				"html" => $email_message->getHTML(),
				"style" => [
					"box-shadow" => "inset 0 1px 3px rgba(0, 0, 0, 0.3)",
    				"border-radius" => "0.25rem",
					"padding" =>  "1em",
					"margin" => ".5em 0"
				]
			]]]],
			"footer" => $footer
		]);
		return $card->getHTML();
	}
}