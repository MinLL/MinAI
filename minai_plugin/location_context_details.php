<?php

// Proof of concept to recognize safe places and provide hints about special places

function GetLocationDetails($locationName) {
	$s_res = "";
	$s_loc = strtolower(trim($locationName));
	if ($s_loc > "") {
		$location_dictionary = [ // hardwired for now, probably better as an external resource
		// safe places:
		"aemer's refuge"		=> "a private mountain stone fortress that follows the design of the city of Markarth, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"arch mage's quarters"	=> "a private home at the top of the main tower in the College of Winterhold. Owned by The Dragonborn as Arch-Mage. A large space with a garden in center, luxurious furniture and large beds. Safe for rest, unwinding and intimacy",
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
		"tundra homestead" 		=> "a small and cozy private home, East of Whiterun, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"vintrhus" 				=> "a private home in Skaal Village owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"vlindrel hall"			=> "a private home in Markarth, carved in stone, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"white pine lodge"		=> "a private home in Bruma owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"whitepeak tower" 		=> "a private Dawnguard style home near Whiterun owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"wind path"				=> "a private small comfortable rustic house near Ivarstead, owned by The Dragonborn, safe for rest, unwinding and intimacy",
		"windstad manor" 		=> "a private home in Hjaalmarch built and owned by The Dragonborn, safe for rest, unwinding and intimacy",

		// places (some need to be cleared)
		"ancestor glade"		=> "a small glade in a cavern near Falkreath. It is the home to swarms of ancestor moths, and is the only location where such moths can be found. The springs are ideal for bathing and cleansing. A safe place for rest, unwinding and intimacy",
		"bard's leap summit"	=> "a walkway and a wooden plank extending over the edge of a waterfall at the very top of Lost Valley Redoubt. Those who jump and stay alive will be rewarded with the great gift of persuasion. Hint: a secret that few know, ghosts don't die",
		"eldergleam sanctuary" 	=> "a small underground grove and worship site of the followers of Kynareth. The sanctuary is renowned for its remarkable beauty and the giant tree Eldergleam. The thermal springs and cool water are ideal for bathing and cleansing. A safe place for rest, unwinding and intimacy",
		"fort dawnguard"		=> "a large fort southeast of Riften which serves as the Dawnguard's base of operations. This is a safe place for rest, unwinding and intimacy",
		"high hrothgar"			=> "a serene place near the top of the world, home of the Greybeards. A safe place for rest and unwinding",
		"riften warehouse"		=> "a warehouse owned by the Jarl of Riften and the base of operations of Riften's skooma dealer until cleared. After cleared this is a safe place for rest, unwinding and intimacy",
		"shadowgreen cavern" 	=> "a small beautiful cave near Solitude containing spriggans and animals. It has a waterfall, clean, flowing water, perfect for bathing and cleansig. Safe for rest, unwinding and intimacy",
		"sleeping tree camp" 	=> "a public place, source of a powerful drug, Sleeping Tree Sap, somebody should check the glowing tree in the middle, it has a spigot to collect sap",
		"soljund's sinkhole" 	=> "a small moonstone mine east of Markarth containing draugr. Room with three levers puzzle solution: DO NOT activate the center lever it's a TRAP, it's safe to activate any lever on the walls, left or right",
		"the arcanaeum" 		=> "the library located within the College of Winterhold. Collated over hundreds of years, the College's library contains a wealth of information. A safe place for rest, unwinding and intimacy",
		"thirsk mead hall"		=> "a private place owned by friendly Rieklings, safe for rest, unwinding and intimacy",
		// towns:
		"rorikstead" 			=> "a farming town in western Whiterun Hold. The residents claim not to have had a bad harvest in years, which is too good to be true, maybe dark magic is involved",

		// puzzles:
		"alftand" 				=> "a Dwarven ruin southwest of Winterhold containing Dwarven automatons, Falmer, frostbite spiders, and skeevers. Could be used to access the Blackreach with a dedicated Dwemer device",
		"arkngthamz" 			=> "a Dwarven ruin southeast of Dushnikh Yal containing Dwarven automatons, Falmer, and chaurus. Tonal lock puzzle solution: shoot the five kinetic resonators in the correct order - lower left, lower right, upper left, upper right, lower middle",
		"bleak falls barrow" 	=> "a Nordic ruin west of Riverwood containing bandits, draugr, and skeevers. Has a Word Wall with Unrelenting Force dragon shout. Three rotating pillars puzzle solution: Snake—Snake—Whale. Puzzle door solution: get the Golden Claw from the Riverwood Trader then dial Bear—Moth—Owl",
		"dead men's respite" 	=> "a Nordic ruin southwest of Morthal containing draugr and frostbite spiders. Has a Word Wall for Whirlwind Sprint dragon shout. Puzzle door solution: use Ruby Dragon Claw and dial Wolf—Hawk—Wolf",
		"duskglow crevice"		=> "a very cold cave south of Dawnstar containing Falmer, chaurus, bandits, and skeevers. The Falmer and chaurus are battling a few bandits. A switch just to the left of the staircase on the second level opens the barred gate",
		"fahlbtharz"			=> "a Dwarven ruin east of the Water Stone containing rieklings, albino spiders, oil spiders, and Dwarven automatons. In this ruin are two puzzles. Ten button control panel puzzle solution: activate button number NINE, the second last button on second row. Boiler tonal lock puzzle with six kinetic resonators solution: you need to power all twenty steam meters of the boiler and for this shoot resonators ONE, TWO and FIVE",
		"forelhost"				=> "a Nordic ruin southeast of Riften containing Dragon Cultists, draugr, skeevers, and the dragon priest Rahgot. Its Word Wall teaches the Storm Call dragon shout. Puzzle door solution: use the Glass Claw and dial Fox—Owl—Snake",	
		"geirmund's hall" 		=> "a Nordic ruin east of Ivarstead containing draugr, frostbite spiders, skeevers, and the draugr Sigdis Gauldurson. Four rotating pillars puzzle solution - look at the symbols on the walls and rotate pillars to Eagle—Whale—Snake—Whale",
		"high gate ruins"		=> "a Nordic ruin west of Dawnstar containing draugr and the dragon priest Vokun. Has a Word Wall with Storm Call dragon shout. The puzzle room solution: pull the levers in the correct order Eagle—Whale—Fox—Snake. The solution is displayed on the walls of the room",
		"mzulft"				=> "a Dwarven ruin south-southeast of Windhelm containing Dwarven automatons, Falmer, and chaurus. Oculory puzzle solution: insert a Focusing Crystal and then use fire and ice  spells to direct the light beams onto the lenses in the correct order", 
		"shroud hearth barrow"	=> "a dangerous Nordic ruin east of Ivarstead containing draugr and skeletons. Four lever puzzle solution: activate the levers closest to the door. Puzzle door solution: get Sapphire Dragon Claw from  Wilhelm the innkeeper in Ivarstead, then dial Moth—Owl—Wolf. Four rotating pillars puzzle solution: Whale—Eagle—Snake—Whale",
		"silverdrift lair"		=> "a Nordic ruin south-southeast of Dawnstar containing draugr. Has s Word Wall with Disarm dragon shout. To open the concealed door pull the whale handle (switch) as shown in the sign up on the wall. The snake handle is a trap, it will ignite the oil slick",
		"tower of mzark"		=> "a Dwarven ruin accesible from Blackreach containing a Dwarven contraption with Elder Scrolls. Buttons puzzle solution: the rightmost button is the reset, push the second from the right until another button lights, then the third from the right until the fourth lights, and push the forth",
		"ustengrav"				=> "a Nordic ruin northeast of Morthal containing bandits, warlocks, draugr, skeletons, and frostbite spiders. The three stone pillars puzzle solution: use the Whirlwind Sprint Shout to pass through gates. Ustengrav is the burial site of Jurgen Windcaller, the mortal progenitor of the Way of the Voice. Its word wall teaches the Become Ethereal dragon shout. Bandits and warlocks are fighting inside",
		"yngol barrow"			=> "a small Nordic ruin northeast of Windhelm containing the shade of an ancient Nord warrior. Rotating pillars puzzle solution: rotate bottom left pillar with grass grown around to Snake, rotate top left pillar with light from outside beaming in to Hawk, rotate bottom right Pillar next to the throne with the waterfall to Whale then sit on the throne. Puzzle door solution: use Coral Dragon Claw and dial Snake-Wolf-Moth",
			// untested:
		"ansilvund" 			=> "a Nordic ruin. Rotating pillars puzzle solution: Eagle-Snake-Whale-Snake. Canm be found in the book 'Of Fjori and Holgeir'",
		"folgunthur" 			=> "a Nordic ruin. Rotating pillars puzzle solution: Eagle-Whale-Snake. You have to mirror these symbols found in nearby room",
		"saarthal" 				=> "a Nordic ruin. A) For the room with two sets of rotating pillars puzzle, solution is : Eagle-Snake-Whale for first set and Whale-Eagle-Eagle for second set. Each pillar must be rotated until the symbol on the pillar matches the symbol on the wall behind the pillar. B) Room with four rotating pillars puzzle: Rotate South-East pillar to: Whale, North-East to Snake, South-West to Eagle and North-West to Whale",
		"shroud hearth barrow" 	=> "a Nordic ruin. Four rotating pillars puzzle solution: Whale-Eagle-Snake-Whale",
		"skuldafn temple" 		=> "a Nordic ruin. Rotating pillars puzzle solution: Snake-Eagle-Whale for first puzzle, Snake-Snake-Whale for second puzzle",
		"arkngthamz" 			=> "a Dwarven ruin. Five resonators puzzle solution is to shoot the resonators in the following order: bottom left, bottom right, top left, top right and bottom middle",
		"volskygge" 			=> "a Nordic ruin. Four levers puzzle solution: Snake-Bear-Fox-Wolf. Read the book The Four Totems of Volskygge",

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