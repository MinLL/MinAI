<?php

// Proof of concept to recognize safe places and provide hints about special places

function GetLocationDetails($locationName) {
	$s_res = "";
	$s_loc = strtolower(trim($locationName));
	if ($s_loc > "") {
		$location_dictionary = [ // hardwired for now, probably better as an external resource
		// safe places:
		"aemer's refuge"		=> "a private mountain stone fortress that follows the design of the city of Markarth, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"azura's dawn" 			=> "a private home owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"black briar lodge"		=> "a private place in The Rift owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"blackfall estate" 		=> "a private grand estate with multiple levels and a beautiful exterior, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"bloodchill manor"		=> "a private home in Bloodchill Cavern, a small cave southwest of Winterhold, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"bluesky hall"			=> "a private place in Whiterun owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"breaking dawn cottage"	=> "a private home in Dawnstar owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"breezehome" 			=> "a private residence in Whiterun owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"bridge farm" 			=> "a private rustic farm home with a charming bridge entrance, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"caranthir tower reborn"=> "a private majestic tower with various rooms and a powerful magical atmosphere, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"cliffside cottage" 	=> "a private medium sized home located east of Whiterun, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"cliffside manor"		=> "a private home in Amber Guard owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"crossway cottage" 		=> "a private home in Granite Hill obtained after solving the mystery plaguing the town, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"dawnstar sanctuary" 	=> "a private refuge of Dark Brotherhood, safe for rest, unwinding and intimacy",
		"dead man’s dread"		=> "a private place, a ship in Blackbone Isle Grotto rebuilt as home, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"dovahkiin’s vault" 	=> "a private place on Throat of the World owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"dragons keep" 			=> "a private huge castle on a mountain peak west of Whiterun, it has a boarding school for up to 12 orphan children, is owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"drelas’ cottage" 		=> "a private place in Hjaalmarch owned by The Dragonborn after sudden demise of Drelas, the previous owner - safe for rest, unwinding and intimacy",
		"ebongrove" 			=> "a private home located inside a cave-like area that is illuminated by a dwemer sun and features interiors in the dunmer style, has a small indoor fish pond, is owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"elysium" 				=> "a high-fantasy private home with a large open space and beautiful decorations owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"fort valus" 			=> "an abandoned imperial fort, restored and owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"frostview hall"		=> "a private home in Winterhold owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"gallows hall"			=> "a private place Northern shore of Mara’s Eye Pond, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"goldenhills plantation"=> "a private farm near Rorikstead owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"graystone lodge"		=> "a private place in Windhelm owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"guardian's overlook"	=> "a picturesque home with stunning views of the surrounding landscape, private place owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"heljarchen hall" 		=> "a private home in The Pale owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"hendraheim" 			=> "a traditional home in The Reach near Granite Hill built in the mountains, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"hjerim"				=> "a private home in Windhelm, a benefit of the title of Thane of Eastmarch  owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"honeyside" 			=> "a small private home in Riften with access to the lake, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"iceheart mill" 		=> "a private place owned by Iceheart twin sisters, safe for rest, unwinding and intimacy",
		"kynesby" 				=> "a private place in Kynesgrove owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"lakeview manor"		=> "a private home in Falkreath with a beautiful lake view, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"myrwatch" 				=> "a wizard tower in Hjaalmarch near Morthal transformed in a private cozy home, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"nchuanthumz" 			=> "a large dwemer style private home in The Rift owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"new moon cottage"		=> "a private home in Morthal owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"proudspire manor"		=> "a private home in Solitude, a benefit of the title of Thane of Haafingar, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"rayek's end" 			=> "a private mid-sized atmospheric hideout between Riverwood and Whiterun near waterfalls, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"redwater keep"			=> "a private fortress on Lake Geir in the Rift with hired guards, steward and blacksmith, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"riverside lodge" 		=> "a private lodge near Riverwood built as a multi-tiered structure with a gorgeous view, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"sapphire castle" 		=> "a huge private castle owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"severin manor" 		=> "a private home in Raven Rock, Solstheim island, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"solstice castle"		=> "a private grand castle-style home owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"shadowfoot sanctum"	=> "a private place in Riften Ratway owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"texarium" 				=> "a private unique and fantastical home with a whimsical design, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"the carpathian inn" 	=> "a cozy inn with a rustic interior and a welcoming atmosphere, safe for rest and unwinding",
		"the ebony keep"		=> "a private place in Last Vigil owned by The Dragonborn after defeating the Ebony Warrior, safe for rest, unwinding and intimacy",
		"thirsk mead hall"		=> "a private place owned by friendly Rieklings, safe for rest, unwinding and intimacy",
		"tundra homestead" 		=> "a small and cozy private home, East of Whiterun, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"vintrhus" 				=> "a private home in Skaal Village owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"vlindrel hall"			=> "a private home in Markarth, carved in stone, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"white pine lodge"		=> "a private home in Bruma owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"whitepeak tower" 		=> "a private Dawnguard style home near Whiterun owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"wind path"				=> "a private small comfortable rustic house near Ivarstead, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"windstad manor" 		=> "a private home in Hjaalmarch built and owned by The Dragonborn, safe for rest, unwinding and intimacy",
		// towns:
		"rorikstead" 			=> "a farming town in western Whiterun Hold. The residents claim not to have had a bad harvest in years, which is too good to be true, maybe dark magic is involved",
		// puzzles:
		"arkngthamz" 			=> "a Dwarven ruin southeast of Dushnikh Yal containing Dwarven automatons, Falmer, and chaurus. Tonal lock puzzle solution: shoot the five kinetic resonators in the correct order - lower left, lower right, upper left, upper right, lower middle",
		"bleak falls barrow" 	=> "a Nordic ruin west of Riverwood containing bandits, draugr, and skeevers. Has a Word Wall with Unrelenting Force dragon shout. Three pillars puzzle solution: Snake—Snake—Whale. Puzzle door solution: get the Golden Claw from the Riverwood Trader then dial Bear—Moth—Owl",
		"dead men's respite" 	=> "a Nordic ruin southwest of Morthal containing draugr and frostbite spiders. Has a Word Wall for Whirlwind Sprint dragon shout. Puzzle door solution: use Ruby Dragon Claw and dial Wolf—Hawk—Wolf",

		"geirmund's hall" 		=> "a Nordic ruin east of Ivarstead containing draugr, frostbite spiders, skeevers, and the draugr Sigdis Gauldurson. Four rotating pillars puzzle solution - look at the symbols on the walls and rotate pillars to Eagle—Whale—Snake—Whale",

		"fahlbtharz"			=> "a Dwarven ruin east of the Water Stone containing rieklings, albino spiders, oil spiders, and Dwarven automatons. Ten button control panel puzzle solution: activate button number 9, the second last button on second row. Six resonators boiler puzzle solution: shoot resonators one, two and five",
		"shroud hearth barrow"	=> "a dangerous Nordic ruin east of Ivarstead containing draugr and skeletons. Four lever puzzle solution: activate the levers closest to the door. Puzzle door solution: get Sapphire Dragon Claw from  Wilhelm the innkeeper in Ivarstead, then dial Moth—Owl—Wolf. Four rotating pillars puzzle solution: Whale—Eagle—Snake—Whale",
		"sleeping tree camp" 	=> "a public place, source of a powerful drug, Sleeping Tree Sap, somebody should check the glowing tree in the middle, it has a spigot to collect sap",

		// taverns:
		"vilemyr inn" 			=> "an inn in Ivarstead, safe for rest and to get drunk. Pay attention to the bard, Lynly Star-Sung, she sings beautifully but seems to have something to hide",


		//"" => "",
		"player home" => "a safe place for rest, unwinding and intimacy"
		];
		if (isset($location_dictionary[$s_loc])) {
			$s_res = $location_dictionary[$s_loc];
			//Logger::debug(" location found : $s_loc - $s_res");
		}
	}
	return $s_res;
}

?>