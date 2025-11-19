<?php

/**
 * Cleans up various text patterns in the context data
 * @param array $contextData Array of context entries to clean
 * @return array Cleaned context data
 */
function cleanupSlop($contextData) {
    if (!is_array($contextData)) {
        error_log("cleanupSlop: warning no data!"); // debug
        return $contextData;
    }
    $player = ($GLOBALS["PLAYER_NAME"] ?? "");
    $playerPronouns = $GLOBALS["player_pronouns"];
    $pronounSelf = "{$playerPronouns['object']}self";
    $cleaned = [];
    //$lastTimePassIndex = -1; // Track index of the last "Time passes" message
    
    // Arrays to keep track of "uses" messages
    $characterUsesPositions = []; // Store positions of all use messages by character
    $characterLatestUses = [];    // Store only the latest formatted message by character
    
    // Arrays to keep track of ethereal arrow messages
    $etherealArrowPositions = []; // Store positions of all ethereal arrow messages by character
    $etherealArrowLatest = [];    // Store only the latest formatted message by character

    $prev_spell_cast = "";
    $last_spell_cast = "";

    $i_sat_on_chair = 0;
    $i_sat_on_bench = 0;
    $i_combat_count = 0;
    $i_defetead_count = 0;
    $i_killed_count = 0;
    $i_looted_count = 0;
    $i_found_count = 0;
    $i_activate_count = 0;
    $i_cast_on_count = 0;
    $i_gave_count = 0;
    
    // Define object categories for contextual descriptions
    $objectCategories = [
        'smithing' => ['forge', 'anvil', 'grindstone', 'workbench', 'tanning rack', 'smelter'],
        'alchemy' => ['alchemy lab', 'alchemy table', 'potion', 'ingredient', 'mortar', 'pestle'],
        'enchanting' => ['arcane enchanter', 'enchanting table', 'soul gem'],
        'crafting' => ['staff enchanter', 'atronach forge', 'spell making altar'],
        'opening' => ['door', 'gate', 'chest', 'box', 'container', 'drawer', 'wardrobe', 'cabinet', 'satchel', 'barrel', 'urn', 'sack'],
        'cooking' => ['cooking pot', 'spit', 'oven', 'cauldron', 'kettle'],
        'activating' => ['lever', 'button', 'pull chain', 'handle', 'switch', 'mechanism', 'trap', 'puzzle'],
        'harvesting' => ['plant', 'flower', 'nirnroot', 'mountain flower', 'mushroom', 'deathbell', 'thistle', 'lavender', 'snowberry', 'wheat', 'herb'],
        'reading' => ['book', 'journal', 'note', 'letter', 'tome', 'scroll', 'elder scroll', 'black book'],
        'drinking' => ['mead', 'ale', 'wine', 'beer', 'water', 'potion', 'milk', 'cup', 'tankard', 'mug', 'goblet', 'chalice'],
        'eating' => ['bread', 'meat', 'cheese', 'vegetable', 'fruit', 'sweet roll', 'apple', 'potato', 'cabbage', 'leek', 'tomato', 'stew', 'soup'],
        'worship' => ['shrine', 'altar', 'standing stone', 'wayshrine', 'statue', 'offering'],
        'mining' => ['ore vein', 'ore', 'pickaxe', 'mine', 'quarried stone', 'corundum', 'iron', 'silver', 'gold', 'ebony', 'malachite', 'moonstone', 'orichalcum', 'quicksilver'],
        'bathing' => ['bath', 'pool', 'hot spring', 'basin', 'fountain', 'waterfall'],
        'water_source' => ['well', 'pump', 'stream', 'river', 'lake', 'pond', 'spring'],
        'music' => ['lute', 'drum', 'flute', 'bard instrument', 'horn'],
        'trading' => ['merchant stall', 'vendor', 'shop counter', 'display case', 'gold', 'septim', 'coin', 'purse'],
        'lockpicking' => ['lock', 'lockpick', 'key'],
        'soul_trapping' => ['soul gem', 'black soul gem', 'azura\'s star', 'black star'],
        'woodcutting' => ['woodcutting block', 'woodpile', 'chopping block', 'axe'],
        'storage' => ['safe', 'strongbox', 'chest', 'shelf', 'bookcase', 'cupboard', 'display case'],
        'throne' => ['throne', 'jarl seat'],
        'practicing' => ['training dummy', 'archery target', 'practice target'],
        'praying' => ['prayer mat', 'kneeler', 'meditation spot'],
        'sitting' => ['stool', 'chair', 'bench', 'throne', 'seat'],
        'lying' => ['bed', 'bedroll', 'cot', 'hay pile', 'fur', 'animal hide', 'pelts', 'sleeping bag'],
        'sitting_at' => ['table', 'desk', 'counter', 'workbench'],
        'meditating' => ['cancel meditation']
    ];
    
    $lastrole = "";
    $arr_x = $contextData;
    $contextData = array_unique_multi($arr_x, 'content');
    $n_context = count($contextData);

    //$n_x = count($arr_x);
    //error_log("DUP: $n_x - $n_context "); // debug
    
    foreach ($contextData as $n_entry => $entry) {
        if (!isset($entry['content'])) {
            continue;
        }
        
        $originalContent = $entry['content'];
        $s_content = strtolower(trim($originalContent));
        
        if (strlen($s_content) < 1)
            continue;

        //# NEARBY CHARACTERS IN THE SCENE
        if (!(strpos($s_content, '# nearby characters in the scene') === false)) {
                continue;
        } 

        //# POIs - Points of Interest nearby
        if (!(strpos($s_content, '# pois - points of interest nearby') === false)) {
                continue;
        } 

        if (isset($entry['role'])) {
            if ($entry['role'] == 'assistant') {
                if (strlen(trim($originalContent)) > 0) {
                    if ((strpos($originalContent,"{") >= 0) && (strpos($originalContent,"}") > 0)) {
                        $s_json = $originalContent;
                        $arr_extract = json_decode($s_json, true);
                        //error_log(" json found: $s_json - exec trace " . print_r($arr_extract, true) ); //debug
                        if (isset($arr_extract)) {
                            $msg = trim($arr_extract['message'] ?? "");
                            $speaker = $arr_extract['character'] ?? "";
                            $action = $arr_extract['action'] ?? "";
                            $target = $arr_extract['target'] ?? "";
                            if (strlen($msg) > 0 ) {
                                $entry['role'] == 'user';
                                $originalContent = $speaker . ": " . $msg;
                            } else {
                                if (strlen($action) > 0) {
                                    $entry['role'] == 'user';
                                    $originalContent = $speaker . " perform " . $action ;
                                    if (strlen($target) > 0) {
                                        $originalContent .= " on " . $target ;
                                    }
                                }
                            }
                        }
                    } else {
                        $entry['role'] == 'user';
                        //error_log(" assistant changed: $originalContent - exec trace "); //debug
                    }
                }
            }
        }

        // Split content into lines to process each line individually
        $contentLines = explode("\n", $originalContent);
        $processedLines = [];
        $n_lines = count($contentLines);
        
        /*
        $b_multiline = ($n_lines > 5);
        if ($b_multiline) {
            error_log("SLOP DUP multi-line: $n_lines at $n_entry/$n_context  "); // debug
            continue;
        }
        */
        
        foreach ($contentLines as $lineIndex => $line) {
                        
            $content = $line;

            $sl_line = strtolower(trim($line));

            $line_length = strlen($sl_line);
            //$line_length2 = $line_length >> 1;

            // Skip empty lines
            if ($line_length < 1) 
                continue;

            if (!(strpos($sl_line, 't hear you, can you repeat?') === false)) {
                continue;
            } 
            
            if (!(strpos($sl_line, ' about the ongoing conversation') === false)) { // (... responds to ... about the ongoing conversation)
                continue;
            }

            //(nnn tried to take an item from mmm, but couldn\'t find it. (Talking to nnn))', 
            if ((!(strpos($sl_line, " tried to take an item from ") === false)) && (!(strpos($sl_line, ", but couldn't find it.") === false)))  {
                continue;
            }
            
            if ((!(strpos($sl_line, ' starts a dialogue with ') === false)) && (!(strpos($sl_line, ' about a relevant topic') === false)))  {
                continue;
            }

            //'content' => 'Quest Updated "My Pet Nix-Hound" new objetive: Read the Notice of Sale 
            if ((!(strpos($sl_line, 'quest updated ') === false)) && (!(strpos($sl_line, ' new objetive: ') === false)))  { // this spamming with entire quests list; there are better options to see only current quests
                continue;
            }

            if ($line_length < 50) {
            
                if (!(strpos($sl_line, ': ... (talking to ') === false)) { //: ... (Talking to
                    continue;
                }

                if (!(strpos($sl_line, ':... (talking to ') === false)) { //:... (Talking to
                    continue;
                }

                if (!(strpos($sl_line, ': . (talking to ') === false)) { //: . (Talking to
                    continue;
                }
                if (!(strpos($sl_line, ':. (talking to ') === false)) { //:. (Talking to
                    continue;
                }
                if (!(strpos($sl_line, ': (talking to ') === false)) { //: (Talking to
                    continue;
                }

                if (!(strpos($sl_line, '.use tool calling.') === false)) { //.USE TOOL CALLING.
                    continue;
                }

                if (!(strpos($sl_line, ': huh? ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': huh! ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': agh... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': agh! ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': ugh... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': ugh! ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': nuh... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hunh... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hmm? ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hmm hmm. ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': gah... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hey! ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': fus... ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': ro...da! ...)') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': (clears throat) ...)') === false)) {
                    continue;
                }
            }

            if ($line_length < 36) {
                if (!(strpos($sl_line, ': huh?') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': agh...') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': ugh...') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': nuh...') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hunh...') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hmm?') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': hmm...') === false)) {
                    continue;
                }
                if (!(strpos($sl_line, ': gah...') === false)) {
                    continue;
                }
                
                if ($sl_line == '(...') {
                    continue;
                }
                
                if ($sl_line == '...)') {
                    continue;
                }

                if ($sl_line == '...') {
                    continue;
                }
                if ($sl_line == '.') {
                    continue;
                }

                // Sheep died
                if ($sl_line == 'sheep died') {
                    continue;
                }

                if ($sl_line == 'goat died') {
                    continue;
                }
                //Rabbit died
                if ($sl_line == 'rabbit died') {
                    continue;
                }

                if ($sl_line == 'elk died') {
                    continue;
                }

                if ($sl_line == 'deer died') {
                    continue;
                }

                if ($sl_line == 'cow died') {
                    continue;
                }

                if ($sl_line == 'bear has killed deer') {
                    continue;
                }
                if ($sl_line == 'bear has killed elk') {
                    continue;
                }
                if ($sl_line == 'bear has killed goat') {
                    continue;
                }
                if ($sl_line == 'bear has killed rabbit') {
                    continue;
                }

                if ($sl_line == 'cave bear has killed deer') {
                    continue;
                }
                if ($sl_line == 'cave bear has killed elk') {
                    continue;
                }
                if ($sl_line == 'cave bear has killed goat') {
                    continue;
                }
                if ($sl_line == 'cave bear has killed rabbit') {
                    continue;
                }

                if ($sl_line == 'wolf has killed deer') {
                    continue;
                }
                if ($sl_line == 'wolf has killed elk') {
                    continue;
                }
                if ($sl_line == 'wolf has killed goat') {
                    continue;
                }
                if ($sl_line == 'wolf has killed rabbit') {
                    continue;
                }

                if ($sl_line == 'mammoth has killed deer') {
                    continue;
                }
                if ($sl_line == 'mammoth has killed elk') {
                    continue;
                }
                if ($sl_line == 'mammoth has killed goat') {
                    continue;
                }
                if ($sl_line == 'mammoth has killed rabbit') {
                    continue;
                }

            }


            // Check if this is a "Time passes" message
            if ((!(strpos($sl_line, '(time passes without anyone in the group talking)') === false)) && ($line_length < 99)) {
                continue;
            }
            
            //Mandatory OoC instruction:
            if (!(strpos($sl_line, 'mandatory ooc instruction:') === false)) {
                continue;
            }
            
            if ((!(strpos($sl_line, ' uses chair') === false)) && ($line_length < 40)) { // uses chair
                $i_sat_on_chair++;
                if ($i_sat_on_chair > 2)
                    continue;
            }

            if ((!(strpos($sl_line, ' uses bench') === false)) && ($line_length < 40)) { // uses bench
                $i_sat_on_bench++;
                if ($i_sat_on_bench > 2)
                    continue;
            }
            
            if ((!(strpos($sl_line, ' engages combat with ') === false)) && ($line_length < 75)) {// engages combat with  
                $i_combat_count++;
                if ($i_combat_count > 3)
                    continue;
            }


            //Herika issued ACTION, but Error: target not found
            if (!(strpos($sl_line, ' issued action, but error: ') === false)) {
                continue;
            }
            
            //Player rejected the transaction of
            if (!(strpos($sl_line, " rejected the transaction of ") === false)) {
                continue;
            }

            //LOCATION CHANGE to 
            if (strpos($sl_line, "location change to ") === 0) {
                continue;
            }
            
            if (stripos($content, ' has defeated ') !== false) { // has defeated 
                if (stripos($content, "{$player} has defeated ") === false) { //is not player 
                    if (stripos($content, "the narrator has defeated ") === 0) { //The Narrator has defeated 
                        $content = str_ireplace("the narrator has defeated", "{$player} HAS DEFEATED ", $line);
                        $line = $content;
                        $sl_line = strtolower(trim($line));
                        //error_log("SLOP warn: $content "); //debug
                    } elseif ($line_length < 55) { // has defeated 
                        $i_defetead_count++;
                        if ($i_defetead_count > 4)
                            continue;
                    }
                }
            }
            
            //Travelling Apothecary has killed Bandit
            if ((!(strpos($sl_line, ' has killed ') === false)) && ($line_length < 50)) { // has killed 
                $i_killed_count++;
                if ($i_killed_count > 3)
                    continue;
            }

            if ((strlen($player) > 0) && ($line_length < 80)) {
                if (stripos($sl_line, "{$player} found ") === 0) { // {$player} found N ...
                        $i_found_count++;
                        if ($i_found_count > 4) {
                            //error_log("SLOP F removed at $n_entry/$n_context - $lineIndex/$n_lines cnt=$i_found_count - $sl_line"); // debug
                            continue;
                        //} else {
                            //error_log("SLOP F found at $n_entry/$n_context - $lineIndex/$n_lines - cnt=$i_found_count - $sl_line"); // debug
                        }
                }

                if (stripos($sl_line, "{$player} looted ") === 0)  { // {$player} looted N ...
                    $i_looted_count++;
                    if ($i_looted_count > 3)
                        continue;
                }
                
                if (stripos($sl_line, "{$player} activates ") === 0)  { // {$player} activates N ...
                    $i_activate_count++;
                    if ($i_activate_count > 3)
                        continue;
                } 

                if (stripos($sl_line, "{$player} casts Mass Match Maker") === 0)  { // {$player} casts Mass Match Maker
                            continue;
                } 

                if (stripos($sl_line, "{$player} casts ") === 0)  { // {$player} casts ... //on ...
                    //if (!(strpos($sl_line, ' on ') === false)) {
                        $i_cast_on_count++;
                        if ($i_cast_on_count > 3)
                            continue;
                    //}
                } 
                
                if (stripos($sl_line, "{$player} gave ") === 0)  { // {$player} gave 60 Septim to Hercules the Dog,(value 60 gold)
                    if (!(strpos($sl_line, '(value ') === false)) {
                        $i_gave_count++;
                        if ($i_gave_count > 3)
                            continue;
                    }
                } 
                
            }
            
            // Remove any existing double parentheses that might interfere with our patterns
            $content = str_replace('))', ')', $line);
            $content = str_replace('((', '(', $content);

            // Pattern: Handle Waterskin messages
            $content = preg_replace_callback('/([^\n]+?) found 1 Waterskin (\d)\/3/', function($matches) use ($playerPronouns) {
                $name = $matches[1];
                $level = $matches[2];
                
                if ($level == 3) {
                    return "{$name} refilled {$playerPronouns['possessive']} Waterskin to full";
                } else {
                    return "{$name} took a drink from {$playerPronouns['possessive']} Waterskin";
                }
            }, $content);
           
            // Remove form[] indicator messages
            if (preg_match('/[^\n]+? found 1 used in a form\[\] to indicate true\n?/', $content)) {
                continue; // Skip this line entirely
            }

            // Pattern 1: Handle "thinking to self" pattern
            if (preg_match('/The Narrator:\s*(.*?)\s*\(talking to (.*?) is thinking to (him|her|them)self\)/', $content, $matches)) {
                $thought = $matches[1];
                $character = $matches[2];
                $pronoun = $matches[3];
                $content = "$character: $thought ($character is thinking to $pronoun" . "self)";
            }
            // Pattern 1b: Handle "reacting to physical sensations" pattern
            else if (preg_match('/The Narrator:\s*(.*?)\s*\(talking to (.*?) is reacting to physical sensations\)/', $content, $matches)) {
                $thought = $matches[1];
                $character = $matches[2];
                $content = "$character: $thought ($character is thinking to $pronounSelf, reacting to physical sensations)";
            }
            // Pattern 1c: Handle "Narrator talking to player" pattern
            else if (preg_match('/The Narrator:\s*(.*?)\s*\(talking to (.*?)\)/', $content, $matches)) {
                $thought = $matches[1];
                $playerName = $matches[2];
                $content = "$playerName thinks to $pronounSelf: $thought";
            }

            // Pattern 2: Remove "(Talking to The Narrator)" from any character dialogue
            $content = preg_replace('/\s*\(Talking to The Narrator\)/', '', $content);

            // Pattern 3: Remove "(Talking to everyone)" only from self-thinking lines
            if (preg_match('/^[^:]+\s+thinks to (?:him|her|she|them)self:/', $content)) {
                $content = preg_replace('/\s*\(Talking to everyone\)/', '', $content);
            }

            // Post-processing: Skip lines containing only character names
            if (preg_match('/^[^:]+:\s*$/', $content)) {
                continue;
            }

            // Post-processing: Skip character stat lines
            if (preg_match('/^level:\d+,name:"[^"]+",race:"[^"]+"/', $content)) {
                continue;
            }

            // Post-processing: Skip "Current followers" line
            if (preg_match('/^Current followers:/', $content)) {
                continue;
            }

            // Post-processing: Skip "beings in range" lines
            if (stripos($content, 'beings in range:') !== false) {
                continue;
            }
            
            // Pattern: Skip "$name uses" with missing object (both plain and parenthesized formats)
            if (preg_match('/^\(?(.+?)\s+uses\s*\)?$/i', $content)) {
                continue;
            }

            // Remove ' found N' message at end of line 
            if (preg_match('/ found \d+$/', $sl_line)) {
                continue; // Skip this line entirely
            }
            
            // Remove 'Some_name:@Other name@' message 
            if (preg_match('/^[a-z\s_\'-]+:@[a-z\s_\'-]+@$/', $sl_line)) {
                continue; // Skip this line entirely
            }

            // Remove 'NPC Name casts Spell Name' - '/^([A-Z][a-zA-Z]+)(?: ([A-Z][a-zA-Z]+))? casts ([A-Z][a-zA-Z ]+)$/'
            if (preg_match('/^([A-Z][a-z_\'A-Z]+)(?: ([A-Z][a-z_\'A-Z]+))? casts ([A-Z][a-zA-Z ]+)$/', $content)) {
                //error_log(" casts: $content - dbg ");
                if (($content == $last_spell_cast) || ($content == $prev_spell_cast)) {
                    continue; // Skip this line entirely
                } else {
                    $i_cast_on_count++;
                    if ($i_cast_on_count > 4)
                        continue;
                    if (($last_spell_cast > "") && ($prev_spell_cast != $last_spell_cast))
                        $prev_spell_cast = $last_spell_cast;
                    $last_spell_cast = $content;
                }
            }
            /*
            if ((!(strpos($sl_line, ' casts ') === false)) && ($line_length < 75)) {// engages combat with  
                $i_cast_on_count++;
                if ($i_cast_on_count > 4)
                    continue;
            }*/
            
            // {"target":""} 
            $content = str_replace('{"target":""}', ' ', $content); 
            // TODO: repeated memory
            
            // Pattern: Handle "$name uses $object" messages (both plain and parenthesized formats)
            if (preg_match('/^\(?(.+?)\s+uses\s+(.+?)\)?$/i', $content, $matches)) {
                $character = trim($matches[1]);
                // Strip "The Narrator:" prefix if present
                $character = preg_replace('/^The Narrator:\s*/', '', $character);
                $object = trim($matches[2]);
                
                // Skip incomplete messages with no object
                if (empty($object)) {
                    continue;
                }
                
                // Format message contextually based on the object
                $formattedMessage = "$character uses $object";
                
                // Add contextual descriptions based on object categories
                $lowerObject = strtolower($object);
                
                // Check each category
                $categorized = false;
                foreach ($objectCategories as $category => $keywords) {
                    foreach ($keywords as $keyword) {
                        if (strpos($lowerObject, $keyword) !== false) {
                            switch ($category) {
                                case 'enchanting':
                                    $formattedMessage = "$character uses the $object to enchant items";
                                    break;
                                case 'smithing':
                                    $formattedMessage = "$character works at the $object crafting items";
                                    break;
                                case 'alchemy':
                                    $formattedMessage = "$character uses the $object to mix potions";
                                    break;
                                case 'cooking':
                                    $formattedMessage = "$character prepares food at the $object";
                                    break;
                                case 'opening':
                                    $formattedMessage = "$character opened a $object";
                                    break;
                                case 'reading':
                                    $formattedMessage = "$character reads a $object";
                                    break;
                                case 'drinking':
                                    $formattedMessage = "$character drinks from a $object";
                                    break;
                                case 'eating':
                                    $formattedMessage = "$character eats some $object";
                                    break;
                                case 'worship':
                                    $formattedMessage = "$character prays at the $object";
                                    break;
                                case 'mining':
                                    $formattedMessage = "$character mines the $object";
                                    break;
                                case 'harvesting':
                                    $formattedMessage = "$character harvests the $object";
                                    break;
                                case 'activating':
                                    $formattedMessage = "$character activates the $object";
                                    break;
                                case 'bathing':
                                    $formattedMessage = "$character bathes in the $object";
                                    break;
                                case 'water_source':
                                    $formattedMessage = "$character draws water from the $object";
                                    break;
                                case 'music':
                                    $formattedMessage = "$character plays the $object";
                                    break;
                                case 'trading':
                                    $formattedMessage = "$character trades with the $object";
                                    break;
                                case 'lockpicking':
                                    $formattedMessage = "$character picks the $object";
                                    break;
                                case 'soul_trapping':
                                    $formattedMessage = "$character uses the $object to trap souls";
                                    break;
                                case 'woodcutting':
                                    $formattedMessage = "$character chops wood at the $object";
                                    break;
                                case 'storage':
                                    $formattedMessage = "$character searches through the $object";
                                    break;
                                case 'throne':
                                    $formattedMessage = "$character sits upon the $object";
                                    break;
                                case 'practicing':
                                    $formattedMessage = "$character practices on the $object";
                                    break;
                                case 'praying':
                                    $formattedMessage = "$character kneels at the $object in prayer";
                                    break;
                                case 'crafting':
                                    $formattedMessage = "$character uses the $object to craft magical items";
                                    break;
                                case 'meditating':
                                    $formattedMessage = "$character meditates and regains focus, restoring their magicka";
                                    break;
                                case 'sitting':
                                    $formattedMessage = "$character sat on a $object";
                                    break;
                                case 'lying':
                                    $formattedMessage = "$character laid down in a $object";
                                    break;
                                case 'sitting_at':
                                    $formattedMessage = "$character sat at a $object";
                                    break;
                            }
                            $categorized = true;
                            $formattedMessage = "($formattedMessage)";
                            break 2; // Break out of both loops once categorized
                        }
                    }
                }
                
                // Handle uses messages separately by creating a new entry
                $usesEntry = $entry;
                $usesEntry['content'] = "PLACEHOLDER_USES_" . $character;
                
                // Record the position of this "uses" message
                if (!isset($characterUsesPositions[$character])) {
                    $characterUsesPositions[$character] = [];
                }
                $characterUsesPositions[$character][] = count($cleaned);
                
                // Store this as the latest use message for this character
                $characterLatestUses[$character] = $formattedMessage;
                
                // Add the uses entry to cleaned array
                $cleaned[] = $usesEntry;
                
                // Skip adding this line to the processed lines
                continue;
            }

            // Pattern: Handle "found Ethereal Arrow" messages
            if (preg_match('/^\(?(.+?)\s+found\s+(\d+)\s+Ethereal\s+Arrow\)?$/i', $content, $matches)) {
                $character = trim($matches[1]);
                $count = intval($matches[2]);
                
                // Format the message
                $formattedMessage = "($character channeled their magic to conjure some Ethereal Arrows)";
                
                // Handle ethereal arrow messages separately by creating a new entry
                $arrowEntry = $entry;
                $arrowEntry['content'] = "PLACEHOLDER_ARROW_" . $character;
                
                // Record the position of this arrow message
                if (!isset($etherealArrowPositions[$character])) {
                    $etherealArrowPositions[$character] = [];
                }
                $etherealArrowPositions[$character][] = count($cleaned);
                
                // Store this as the latest arrow message for this character
                $etherealArrowLatest[$character] = $formattedMessage;
                
                // Add the arrow entry to cleaned array
                $cleaned[] = $arrowEntry;
                
                // Skip adding this line to the processed lines
                continue;
            }

            // Pattern: Handle combat defeat messages
            $content = preg_replace_callback('/\(Context location: ([^)]+)\)(.+?) has defeated (.+?) with (.+?)$/i', function($matches) {
                $location = $matches[1];
                $character = $matches[2];
                $enemy = $matches[3];
                $weapon = $matches[4];
                return "((Context location: $location) $character has defeated $enemy with $weapon)";
            }, $content);

            // Pattern: Replace combat engagement messages
            $content = preg_replace_callback('/The party engages combat with\s+(.+?)(?:\s|$)/i', function($matches) {
                $characterName = trim($matches[1]);
                
                if (strtolower($characterName) === strtolower($GLOBALS["PLAYER_NAME"])) {
                    return "Combat breaks out and a battle begins";
                } else {
                    return "Combat breaks out and a battle begins with $characterName";
                }
            }, $content);

            // Pattern 5: Handle context messages that already have parentheses
            if (preg_match('/^The Narrator:\((.*?)\)$/', $content, $matches)) {
                $content = "($matches[1])";
            }
            // Pattern 6: Handle context messages without parentheses
            else if (strpos($content, 'The Narrator:') === 0) {
                // Handle specific "The Narrator: someText (Talking to Eldawyn)" pattern
                if (preg_match('/^The Narrator:\s*(.*?)\s*\(Talking to Eldawyn\)$/', $content, $matches)) {
                    $content = "($matches[1])";
                } else {
                    $content = preg_replace('/^The Narrator:\s*(.*)$/', '($1)', $content);
                }
            }

            // Clean up any remaining double parentheses that might have been created
            //$content = str_replace('))', ')', $content);
            //$content = str_replace('((', '(', $content);

            // Remove leading and trailing whitespace
            $content = trim($content);
            
            // Only add non-empty lines to processed lines
            if (!empty($content)) {
                $processedLines[] = $content;
            }
        }
        
        // If we have processed lines, join them and add to cleaned array
        if (!empty($processedLines)) {
            $joinedContent = implode("\n", $processedLines);
            
            // Skip messages containing specific headers
            if (stripos($joinedContent, '# PARTY STATUS') !== false || 
                stripos($joinedContent, '# LOCATIONS OF INTEREST') !== false ||
                stripos($joinedContent, '# NEARBY ACTOR') !== false) {
                continue; // Skip this entry entirely
            }
            
            // error_log("DEBUG: Original content: " . $originalContent);
            // error_log("DEBUG: Joined content: " . $joinedContent);
            $entry['content'] = $joinedContent;
            $cleaned[] = $entry;
            
        }
    }

    // Replace placeholders with the latest "uses" message for each character
    foreach ($cleaned as $index => $entry) {
        $content = $entry['content'];
        
        // Check if this is a "uses" placeholder
        if (preg_match('/^PLACEHOLDER_USES_(.+)$/', $content, $matches)) {
            $character = $matches[1];
            
            // Find the position of this placeholder in the character's positions list
            $positions = $characterUsesPositions[$character];
            $positionIndex = array_search($index, $positions);
            
            // Only keep the latest "uses" message for each character
            if ($positionIndex === count($positions) - 1) {
                // This is the latest message for this character
                $cleaned[$index]['content'] = $characterLatestUses[$character];
            } else {
                // This is not the latest message, remove it
                unset($cleaned[$index]);
            }
        }
        
        // Check if this is an "arrow" placeholder
        if (preg_match('/^PLACEHOLDER_ARROW_(.+)$/', $content, $matches)) {
            $character = $matches[1];
            
            // Find the position of this placeholder in the character's positions list
            $positions = $etherealArrowPositions[$character];
            $positionIndex = array_search($index, $positions);
            
            // Only keep the latest arrow message for each character
            if ($positionIndex === count($positions) - 1) {
                // This is the latest message for this character
                $cleaned[$index]['content'] = $etherealArrowLatest[$character];
            } else {
                // This is not the latest message, remove it
                unset($cleaned[$index]);
            }
        }
    }

    //error_log(" DEBUG: context history is " . count($cleaned) );
    
    // Reindex the array to ensure sequential keys
    $cleaned = array_values($cleaned);  

    // Only prune if original context history was set
    if (isset($GLOBALS["ORIGINAL_CONTEXT_HISTORY"])) {
        // error_log("slop DEBUG: Pruning context history to " . $GLOBALS["ORIGINAL_CONTEXT_HISTORY"]);
        // error_log( "Returning context history: " . json_encode($cleaned));
        $n = $GLOBALS["ORIGINAL_CONTEXT_HISTORY"];
        //error_log("slop debug pruning context history from " . count($contextData) . " to " . count($cleaned) );
        return array_slice($cleaned, -$n);
    }
 
    //error_log("slop debug pruning context history from " . count($contextData) . " to " . count($cleaned) );

    // error_log( "Returning context history: " . json_encode($cleaned));
    return $cleaned;
} 