<?php


namespace \App\UI\Examples;


class Card {
	function get_html(){
		$card = new \App\UI\Card\Card([
			"body" => "This is the body"
		]);

		return $card->get_html();
	}
}