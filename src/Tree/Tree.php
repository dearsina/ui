<?php

namespace App\UI\Tree;

use App\Common\str;

/**
 * Inspired by:
 * @link https://codepen.io/sazzad/pen/gEEKQb
 */
class Tree {
	public static function build(array $a): ?string
	{
		extract($a);
		$class = str::getAttrTag("class", "tree");
		$data = str::getDataAttr($a);
		return "<div{$class}{$data}></div>";
	}
}