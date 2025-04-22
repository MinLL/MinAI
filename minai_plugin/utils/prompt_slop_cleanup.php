<?php

/**
 * Cleans up various text patterns in the context data
 * @param array $contextData Array of context entries to clean
 * @return array Cleaned context data
 */
function cleanupSlop($contextData) {
    if (!is_array($contextData)) {
        return $contextData;
    }
    $playerPronouns = $GLOBALS["player_pronouns"];
    $pronounSelf = "{$playerPronouns['object']}self";
    $cleaned = [];
    $lastTimePassIndex = -1; // Track index of the last "Time passes" message
    
    // Arrays to keep track of "uses" messages
    $characterUsesPositions = []; // Store positions of all use messages by character
    $characterLatestUses = [];    // Store only the latest formatted message by character
    
    // Arrays to keep track of ethereal arrow messages
    $etherealArrowPositions = []; // Store positions of all ethereal arrow messages by character
    $etherealArrowLatest = [];    // Store only the latest formatted message by character
    
    // Define object categories for contextual descriptions
    $objectCategories = [
        'sitting' => ['stool', 'chair', 'bench', 'throne', 'seat'],
        'lying' => ['bed', 'bedroll', 'cot', 'hay pile', 'fur', 'animal hide', 'pelts', 'sleeping bag'],
        'sitting_at' => ['table', 'desk', 'counter', 'workbench'],
        'opening' => ['door', 'gate', 'chest', 'box', 'container', 'drawer', 'wardrobe', 'cabinet', 'satchel', 'barrel', 'urn', 'sack'],
        'alchemy' => ['alchemy lab', 'alchemy table', 'potion', 'ingredient', 'mortar', 'pestle'],
        'enchanting' => ['arcane enchanter', 'enchanting table', 'soul gem'],
        'smithing' => ['forge', 'anvil', 'grindstone', 'workbench', 'tanning rack', 'smelter'],
        'cooking' => ['cooking pot', 'spit', 'oven', 'cauldron', 'kettle'],
        'reading' => ['book', 'journal', 'note', 'letter', 'tome', 'scroll', 'elder scroll', 'black book'],
        'drinking' => ['mead', 'ale', 'wine', 'beer', 'water', 'potion', 'milk', 'cup', 'tankard', 'mug', 'goblet', 'chalice'],
        'eating' => ['bread', 'meat', 'cheese', 'vegetable', 'fruit', 'sweet roll', 'apple', 'potato', 'cabbage', 'leek', 'tomato', 'stew', 'soup'],
        'worship' => ['shrine', 'altar', 'standing stone', 'wayshrine', 'statue', 'offering'],
        'mining' => ['ore vein', 'ore', 'pickaxe', 'mine', 'quarried stone', 'corundum', 'iron', 'silver', 'gold', 'ebony', 'malachite', 'moonstone', 'orichalcum', 'quicksilver'],
        'harvesting' => ['plant', 'flower', 'nirnroot', 'mountain flower', 'mushroom', 'deathbell', 'thistle', 'lavender', 'snowberry', 'wheat', 'herb'],
        'activating' => ['lever', 'button', 'pull chain', 'handle', 'switch', 'mechanism', 'trap', 'puzzle'],
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
        'crafting' => ['staff enchanter', 'atronach forge', 'spell making altar'],
        'meditating' => ['cancel meditation']
    ];
    
    foreach ($contextData as $entry) {
        if (!isset($entry['content'])) {
            continue;
        }
        
        $originalContent = $entry['content'];
        
        // Split content into lines to process each line individually
        $contentLines = explode("\n", $originalContent);
        $processedLines = [];
        
        foreach ($contentLines as $lineIndex => $line) {
            // Skip empty lines
            if (trim($line) === '') {
                continue;
            }
            
            $content = $line;
            
            // Remove any existing double parentheses that might interfere with our patterns
            $content = str_replace('))', ')', $content);
            $content = str_replace('((', '(', $content);

            // Pattern: Handle Waterskin messages
            $content = preg_replace_callback('/([^\n]+?) found 1 Waterskin (\d)\/3/', function($matches) use ($playerPronouns) {
                $name = $matches[1];
                $level = $matches[2];
                
                if ($level == 3) {
                    return "$name refilled {$playerPronouns['possessive']} Waterskin to full";
                } else {
                    return "$name took a drink from {$playerPronouns['possessive']} Waterskin";
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

            // Pattern: Remove "talks to player for first time" message when IsRadiant is true
            if (IsRadiant() && preg_match('/talks to ' . preg_quote($GLOBALS["PLAYER_NAME"], '/') . ' for the first time\. They haven\'t yet been acquainted/i', $content)) {
                continue;
            }
            
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
                                case 'sitting':
                                    $formattedMessage = "$character sat on a $object";
                                    break;
                                case 'lying':
                                    $formattedMessage = "$character laid down in a $object";
                                    break;
                                case 'sitting_at':
                                    $formattedMessage = "$character sat at a $object";
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
                                case 'alchemy':
                                    $formattedMessage = "$character uses the $object to mix potions";
                                    break;
                                case 'enchanting':
                                    $formattedMessage = "$character uses the $object to enchant items";
                                    break;
                                case 'smithing':
                                    $formattedMessage = "$character works at the $object crafting items";
                                    break;
                                case 'cooking':
                                    $formattedMessage = "$character prepares food at the $object";
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
            $content = str_replace('))', ')', $content);
            $content = str_replace('((', '(', $content);

            // Remove leading and trailing whitespace
            $content = trim($content);
            
            // Check if this is a "Time passes" message
            $isTimePassMessage = (stripos($content, 'Time passes without anyone in the group talking') !== false);

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
            
            // Check if this is a "Time passes" message
            $isTimePassMessage = (stripos($joinedContent, 'Time passes without anyone in the group talking') !== false);
            
            // Skip consecutive "Time passes" messages and only keep the first one
            if ($isTimePassMessage && $lastTimePassIndex >= 0) {
                continue; // Skip adding this as a new entry
            }
            // error_log("DEBUG: Original content: " . $originalContent);
            // error_log("DEBUG: Joined content: " . $joinedContent);
            $entry['content'] = $joinedContent;
            $cleaned[] = $entry;
            
            // Update the index if this was a time pass message
            if ($isTimePassMessage) {
                $lastTimePassIndex = count($cleaned) - 1;
            } else {
                // Reset the index if we've added a non-time pass message
                $lastTimePassIndex = -1;
            }
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
    
    // Reindex the array to ensure sequential keys
    $cleaned = array_values($cleaned);
    
    // Only prune if original context history was set
    if (isset($GLOBALS["ORIGINAL_CONTEXT_HISTORY"])) {
        // error_log("DEBUG: Pruning context history to " . $GLOBALS["ORIGINAL_CONTEXT_HISTORY"]);
        // error_log( "Returning context history: " . json_encode($cleaned));
        $n = $GLOBALS["ORIGINAL_CONTEXT_HISTORY"];
        return array_slice($cleaned, -$n);
    }
    // error_log( "Returning context history: " . json_encode($cleaned));
    return $cleaned;
} 