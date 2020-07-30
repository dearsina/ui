<?php


namespace App\UI\Examples;

class Gridstack {
	function getHTML(){
		for($i = 0;$i < 10; $i++){
			$buttons[] = [
				"title" => "Title {$i}",
				"icon" => "user",
				"children" => $buttons,
				"hash" => [
					"rel_table" => "rel_table"
				]
			];
		}

		$card = new \App\UI\Card\Card\Card([
			"header" => [
				"title" => "Header",
				"buttons" => $buttons
			],
			"body" => "Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. Lorem ipsum dolor amet, consectetur adipiscing elit. Etiam consectetur aliquet aliquet. Interdum et malesuada fames ac ante ipsum primis in faucibus. ",
			"footer" => [
				"html" => "This is the footer.",
				"buttons" => $buttons
			]
		]);

		$html = /** @lang HTML */<<<EOF
<div class="grid-stack">
  <div class="grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="4" data-gs-height="2" data-gs-id="card_id_1">
    <div class="grid-stack-item-content">{$card->getHTML()}</div>
  </div>
  <div class="grid-stack-item" data-gs-x="8" data-gs-y="3" data-gs-width="4" data-gs-height="4" data-gs-id="card_id_2">
    <div class="grid-stack-item-content">{$card->getHTML()}</div>
  </div>
</div>
<button onclick="lockgrid();">Lock</button>
<button onclick="unlockgrid();">Unlock</button>
<script type="text/javascript">

var grid = GridStack.init();

grid.on('added removed change', function(e, items) {
  var str = '';
  items.forEach(function(item) {
	  str += 'Item ID: ' + item.id + ' (x,y)=' + item.x + ',' + item.y + ' (width,height)=' + item.width + ',' + item.height;
	  
  });
  console.log(e.type + ' ' + items.length + ' items:' + str );
  // var_dump(items);
});

function lockgrid(){
	// $(".grid-stack-item").each(function (idx, gsEl) {
	// 	grid.locked($(gsEl), true);
	// });
	grid.disable();
}

function unlockgrid(){
    grid.enable();
	// $(".grid-stack-item").each(function (idx, gsEl) {
	//     grid.locked($(gsEl), false);
	// });
}
 
</script>
EOF;

		return $html;
	}
}