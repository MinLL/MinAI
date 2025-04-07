<?php
/**
 * Environmental Context Builders
 * 
 * This file contains context builders related to the environment and surroundings
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");
require_once(__DIR__ . "/../../environmentalContext.php");
require_once(__DIR__ . "/../../contextbuilders/weather_context.php");
require_once(__DIR__ . "/../../contextbuilders/dirtandblood_context.php");
require_once(__DIR__ . "/../../contextbuilders/exposure_context.php");

/**
 * Helper function to validate and sanitize parameters for context builders
 * 
 * @param array $params Parameters to validate
 * @param array $required List of required parameter keys
 * @return array Validated and sanitized parameters with fallbacks if needed
 */
function ValidateEnvironmentParams($params, $required = ['herika_name', 'player_name', 'target']) {
    $validated = [];
    
    // Check for required parameters
    foreach ($required as $key) {
        if (isset($params[$key])) {
            $validated[$key] = $params[$key];
        } else {
            // Try to use globals as fallback
            switch ($key) {
                case 'herika_name':
                    $validated[$key] = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
                    break;
                case 'player_name':
                    $validated[$key] = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "";
                    break;
                case 'target':
                    $validated[$key] = isset($GLOBALS["HERIKA_TARGET"]) ? 
                                      $GLOBALS["HERIKA_TARGET"] : 
                                      (isset($validated['player_name']) ? $validated['player_name'] : "");
                    break;
                default:
                    $validated[$key] = "";
            }
        }
    }
    
    // Add any other parameters that were in the original params
    foreach ($params as $key => $value) {
        if (!isset($validated[$key])) {
            $validated[$key] = $value;
        }
    }
    
    return $validated;
}

/**
 * Initialize environmental context builders
 */
function InitializeEnvironmentalContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register day/night state context builder
    $registry->register('day_night_state', [
        'section' => 'environment',
        'header' => 'Time of Day',
        'description' => 'Current time and day/night cycle information',
        'priority' => 5,
        'enabled' => isset($GLOBALS['minai_context']['day_night_state']) ? (bool)$GLOBALS['minai_context']['day_night_state'] : true,
        'builder_callback' => 'BuildDayNightStateContext'
    ]);
    
    // Register weather context builder
    $registry->register('weather', [
        'section' => 'environment',
        'header' => 'Weather',
        'description' => 'Current weather conditions',
        'priority' => 10,
        'enabled' => isset($GLOBALS['minai_context']['weather']) ? (bool)$GLOBALS['minai_context']['weather'] : true,
        'builder_callback' => 'BuildWeatherContext'
    ]);
    
    // Register moon phase context builder
    $registry->register('moon_phase', [
        'section' => 'environment',
        'header' => 'Moon Phase',
        'description' => 'Current phase of the moons',
        'priority' => 15,
        'enabled' => isset($GLOBALS['minai_context']['moon_phase']) ? (bool)$GLOBALS['minai_context']['moon_phase'] : true,
        'builder_callback' => 'BuildMoonPhaseContext'
    ]);
    
    // Register location context builder
    $registry->register('location', [
        'section' => 'environment',
        'header' => 'Location',
        'description' => 'Interior/exterior location information',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['location']) ? (bool)$GLOBALS['minai_context']['location'] : true,
        'builder_callback' => 'BuildLocationContext'
    ]);
    
    // Register nearby buildings context builder
    $registry->register('nearby_buildings', [
        'section' => 'environment',
        'header' => 'Nearby Buildings and Passages',
        'description' => 'Doors and passages in the vicinity',
        'priority' => 22,
        'enabled' => isset($GLOBALS['minai_context']['nearby_buildings']) ? (bool)$GLOBALS['minai_context']['nearby_buildings'] : true,
        'builder_callback' => 'BuildNearbyBuildingsContext'
    ]);
    
    // Register frostfall context builder (if the mod is enabled)
    $registry->register('frostfall', [
        'section' => 'environment',
        'header' => 'Temperature & Exposure',
        'description' => 'Temperature and exposure information from Frostfall',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['frostfall']) ? (bool)$GLOBALS['minai_context']['frostfall'] : true,
        'builder_callback' => 'BuildFrostfallContext'
    ]);
    
    // Register nearby characters context builder
    $registry->register('nearby_characters', [
        'section' => 'environment',
        'header' => 'Nearby Characters',
        'description' => 'Characters in close proximity',
        'priority' => 35,
        'enabled' => isset($GLOBALS['minai_context']['nearby_characters']) ? (bool)$GLOBALS['minai_context']['nearby_characters'] : true,
        'builder_callback' => 'BuildNearbyCharactersContext'
    ]);
    
    // Register NPC relationships context builder
    $registry->register('npc_relationships', [
        'section' => 'environment',
        'header' => 'NPC Relationships',
        'description' => 'NPC relationship to the player',
        'priority' => 40,
        'enabled' => isset($GLOBALS['minai_context']['npc_relationships']) ? (bool)$GLOBALS['minai_context']['npc_relationships'] : true,
        'builder_callback' => 'BuildNPCRelationshipsContext'
    ]);
}

/**
 * Build the day/night state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted day/night context
 */
function BuildDayNightStateContext($params) {
    $character = $params['player_name'];
    
    // Try to get detailed date information first
    $locationContext = GetCurrentLocationContext($character);
    if (!empty($locationContext['date'])) {
        return "The current time in Skyrim is " . $locationContext['date'] . ".";
    }
    
    // Fall back to basic day state if detailed date isn't available
    $dayState = GetActorValue($character, "dayState");
    if (empty($dayState)) {
        return "";
    }
    
    return "It is " . $dayState . ".";
}

/**
 * Build the weather context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted weather context
 */
function BuildWeatherContext($params) {
    $player_name = $params['player_name'];
    
    // Get the weather context
    $weatherContext = GetWeatherContext();
    
    // Check if character is indoors
    if (IsEnabled($player_name, "isInterior") && !empty($weatherContext)) {
        return "Outside, the weather is: " . $weatherContext;
    }
    
    return $weatherContext;
}

/**
 * Build the moon phase context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted moon phase context
 */
function BuildMoonPhaseContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['target'];
    $utilities = new Utilities();
    
    // Get raw moon data
    $moonPhase = GetActorValue($character, "moonPhase");
    $isNight = IsEnabled($character, "isNight");
    $moonCount = GetActorValue($character, "moonCount");
    
    // Check if we have all required data
    if (empty($moonPhase) || empty($moonCount)) {
        return "";
    }
    
    // Build the formatted string
    $text = "The " . $moonCount;
    
    // Are/will be based on time of day
    if ($isNight) {
        $text .= " are";
    } else {
        $text .= " will be";
    }
    
    // Moon phase description
    switch (intval($moonPhase)) {
        case 0:
            $text .= " full";
            break;
        case 1:
            $text .= " wanning gibbious";
            break;
        case 2:
            $text .= " third quarter";
            break;
        case 3:
            $text .= " wanning crescent";
            break;
        case 4:
            $text .= " in new moon";
            break;
        case 5:
            $text .= " waxing crescent";
            break;
        case 6:
            $text .= " first quarter";
            break;
        case 7:
            $text .= " waxing gibbious";
            break;
        default:
            return ""; // Invalid phase, don't return anything
    }
    
    $text .= " tonight.";
    
    return $text;
}

/**
 * Get description for a location keyword
 * 
 * @param string $keyword The location keyword
 * @return string Description of the location type
 */
function GetLocationKeywordDescription($keyword) {
    $descriptions = [
        // Settlement Types
        'city' => 'a major urban center',
        'town' => 'a smaller urban settlement',
        'village' => 'a small rural settlement',
        'settlement' => 'a populated area',
        
        // Military & Defensive
        'fort' => 'a fortified military structure',
        'castle' => 'a large fortified residence',
        'keep' => 'a fortified tower or stronghold',
        'tower' => 'a tall defensive structure',
        'barracks' => 'military housing quarters',
        'palace' => 'a grand residence of nobility',
        
        // Religious
        'temple' => 'a place of worship',
        'temple_of_kynareth' => 'a temple dedicated to Kynareth',
        'temple_of_talos' => 'a temple dedicated to Talos',
        'temple_of_arkay' => 'a temple dedicated to Arkay',
        'temple_of_dibella' => 'a temple dedicated to Dibella',
        'temple_of_mara' => 'a temple dedicated to Mara',
        'temple_of_zenithar' => 'a temple dedicated to Zenithar',
        'temple_of_stendarr' => 'a temple dedicated to Stendarr',
        'temple_of_julianos' => 'a temple dedicated to Julianos',
        'temple_of_akatosh' => 'a temple dedicated to Akatosh',
        
        // Commercial
        'tavern' => 'a drinking establishment',
        'shop' => 'a general store',
        'general_store' => 'a general goods store',
        'clothing_store' => 'a clothing and apparel shop',
        'jewelry_store' => 'a jewelry and gems shop',
        'book_store' => 'a bookstore',
        'potion_store' => 'an alchemy shop',
        'scroll_store' => 'a scroll and spell shop',
        'weapon_store' => 'a weapons shop',
        'armor_store' => 'an armor shop',
        'food_store' => 'a food and provisions shop',
        'furniture_store' => 'a furniture shop',
        'black_market' => 'an illicit trading location',
        
        // Residential
        'house' => 'a residential dwelling',
        'farm' => 'an agricultural property',
        'mill' => 'a grain processing facility',
        'stable' => 'a horse stable',
        'smithy' => 'a blacksmith workshop',
        'forge' => 'a metalworking facility',
        'smelter' => 'a metal smelting facility',
        'tannery' => 'a leather working facility',
        'fishery' => 'a fishing facility',
        'brewery' => 'a beer brewing facility',
        'winery' => 'a wine production facility',
        'meadery' => 'a mead brewing facility',
        'bakery' => 'a bread baking facility',
        'butcher_shop' => 'a meat processing shop',
        
        // Guild & Faction
        'thieves_guild' => 'the Thieves Guild headquarters',
        'dark_brotherhood' => 'the Dark Brotherhood sanctuary',
        'companions_guild' => 'the Companions headquarters',
        'college_of_winterhold' => 'the College of Winterhold',
        
        // Dungeon Types
        'dungeon' => 'an underground complex',
        'ruin' => 'an ancient ruined structure',
        'nordic_ruin' => 'an ancient Nordic ruin',
        'dwemer_ruin' => 'an ancient Dwemer ruin',
        'cave' => 'a natural cave system',
        'tomb' => 'an ancient burial site',
        'crypt' => 'an underground burial chamber',
        'barrow' => 'an ancient Nordic burial mound',
        
        // Enemy Lairs
        'dragon_lair' => 'a dragon\'s territory',
        'giant_camp' => 'a giant\'s encampment',
        'vampire_lair' => 'a vampire\'s lair',
        'werewolf_lair' => 'a werewolf\'s lair',
        'falmer_lair' => 'a Falmer settlement',
        'bandit_camp' => 'a bandit encampment',
        'military_camp' => 'a military encampment',
        
        // Industrial
        'mine' => 'a mining facility',
        
        // Law Enforcement
        'prison' => 'a prison facility',
        'jail' => 'a local jail',
        
        // Navigation
        'lighthouse' => 'a coastal navigation aid'
    ];
    
    return isset($descriptions[$keyword]) ? $descriptions[$keyword] : 'a location';
}

/**
 * Build the location context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted location context
 */
function BuildLocationContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['player_name'];
    $utilities = new Utilities();
    
    $context = "";
    
    // Get hold information
    $currentHold = ucwords(GetActorValue($character, "currentHold"));
    $hasHold = false;
    if (!empty($currentHold)) {
        $context .= "Current Hold: " . $currentHold . ".\n";
        $hasHold = true;
    }
    
    // Get location information - prefer location over cell
    $currentLocation = ucwords(GetActorValue($character, "currentLocation"));
    $hasLocation = false;
    if (!empty($currentLocation)) {
        $context .= "Current Location: " . $currentLocation . ".\n";
        $hasLocation = true;
    } else {
        // Try cell if no location
        $currentCell = ucwords(GetActorValue($character, "CurrentCell"));
        if (!empty($currentCell)) {
            $context .= "Current Location: " . $currentCell . ".\n";
            $hasLocation = true;
        } else {
            // Last resort: try GetCurrentLocationContext
            $locationContext = GetCurrentLocationContext($character);
            if (!empty($locationContext['current'])) {
                $context .= "Current Location: " . ucwords($locationContext['current']) . ".\n";
                $hasLocation = true;
                
                // Add hold from locationContext if we don't have it
                if (!$hasHold && !empty($locationContext['hold'])) {
                    $context = "Current Hold: " . ucwords($locationContext['hold']) . ".\n" . $context;
                }
            }
        }
    }
    
    // Get location keywords and format them
    $locationKeywords = GetActorValue($character, "locationKeywords");
    if (!empty($locationKeywords)) {
        $keywords = explode("~", $locationKeywords);
        $keywords = array_filter($keywords); // Remove empty entries
        if (count($keywords) > 0) {
            $descriptions = array_map(function($keyword) {
                return GetLocationKeywordDescription($keyword);
            }, $keywords);
            $context .= "This is " . implode(", ", $descriptions) . ".\n";
        }
    }
    
    // Check if interior
    if (IsEnabled($character, "isInterior")) {
        $context .= "We are indoors, out of the weather and elements.";
    }
    
    return $context;
}

/**
 * Build the frostfall context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted frostfall context
 */
function BuildFrostfallContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['target'];
    $utilities = new Utilities();
    
    if (!IsEnabled($character, "hasFrostfall")) {
        return "";
    }
    
    $context = "";
    
    // Temperature
    $temperature = GetActorValue($character, "temperature");
    if (!empty($temperature)) {
        $context .= "The temperature is " . $temperature . ". ";
    }
    
    // Weather severity
    $weatherSeverity = GetActorValue($character, "weatherSeverity");
    if (!empty($weatherSeverity)) {
        $context .= $weatherSeverity . " ";
    }
    
    // Shelter status
    if (IsEnabled($character, "isSheltered")) {
        $context .= $character . " is sheltered by things overhead. ";
    }
    
    // Wetness level
    $wetnessLevel = GetActorValue($character, "wetnessLevel");
    if (!empty($wetnessLevel)) {
        $context .= $character . " is " . $wetnessLevel . ". ";
    }
    
    // Exposure level
    $exposureLevel = GetActorValue($character, "exposureLevel");
    if (!empty($exposureLevel)) {
        $context .= $exposureLevel . " ";
    }
    
    // Baseline exposure
    $baselineExposure = GetActorValue($character, "baselineExposure");
    if (!empty($baselineExposure)) {
        $context .= $baselineExposure . " ";
    }
    
    // Warmth rating
    $warmthRating = GetActorValue($character, "warmthRating");
    if (!empty($warmthRating)) {
        $context .= $character . " is dressed " . $warmthRating . ". ";
    }
    
    // Coverage rating
    $coverageRating = GetActorValue($character, "coverageRating");
    if (!empty($coverageRating)) {
        $context .= $character . " " . $coverageRating . " ";
    }
    
    return $context;
}

/**
 * Build the nearby characters context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted nearby characters context
 */
function BuildNearbyCharactersContext($params) {
    $params = ValidateEnvironmentParams($params);
    $herika_name = $params['herika_name'];
    $player_name = $params['player_name'];
    $target = $params['target'];
    if ($herika_name == "The Narrator") {
        $herika_name = $player_name;
    }
    $localActors = DataBeingsInRange();
    
    if (empty($localActors)) {
        return "";
    }
    
    $characters = explode("|", $localActors);
    // Remove any empty entries and trim each character name
    $characters = array_filter(array_map('trim', $characters), function($item) {
        return !empty($item);
    });
    // Ensure herika_name, player_name and target are included without duplicates
    $characters = array_unique(array_merge($characters, array_filter([$herika_name, $player_name, $target])));
    // Remove parentheses from character names
    $characters = array_map(function($name) {
        return trim(trim($name, '()'));
    }, $characters);

    // If we have characters after cleaning, create the formatted list
    if (count($characters) > 0) {
        // Define attributes to fetch in batch - use lowercase for array keys
        $attributes = ['race', 'gender', 'faction', 'sitstate', 'sleepstate', 'dirtandblood'];
        $flags = ['issneaking', 'isswimming', 'isonmount', 'isincombat', 'isencumbered', 'hostiletoplayer'];
        
        // Correctly call the batch functions from global scope
        $actorValues = \BatchGetActorValues($characters, $attributes);
        $actorFlags = \BatchIsEnabled($characters, $flags);
        
        $contextLines = [];
        
        foreach ($characters as $character) {
            $charKey = strtolower($character);
            $line = $character;
            
            // Get race if available
            if (isset($actorValues[$charKey]['race']) && !empty($actorValues[$charKey]['race'])) {
                $line .= " (" . $actorValues[$charKey]['race'];
                
                // Get gender if available
                if (isset($actorValues[$charKey]['gender']) && !empty($actorValues[$charKey]['gender'])) {
                    $line .= " " . $actorValues[$charKey]['gender'];
                }
                
                $line .= ")";
            }
            
            // Add faction info if available
            if (isset($actorValues[$charKey]['faction']) && !empty($actorValues[$charKey]['faction'])) {
                $line .= " - " . $actorValues[$charKey]['faction'];
            }
            
            // Add character state
            if (isset($actorValues[$charKey]['sitstate']) && !empty($actorValues[$charKey]['sitstate']) && intval($actorValues[$charKey]['sitstate']) == 3) {
                $line .= " - sitting";
            }
            
            // Check if character is sleeping
            if (isset($actorValues[$charKey]['sleepstate']) && !empty($actorValues[$charKey]['sleepstate']) && $actorValues[$charKey]['sleepstate'] != "awake") {
                $line .= " - " . $actorValues[$charKey]['sleepstate'];
            }
            
            // Movement and combat states
            if (isset($actorFlags[$charKey]['issneaking']) && $actorFlags[$charKey]['issneaking']) {
                $line .= " - sneaking";
            }
            
            if (isset($actorFlags[$charKey]['isswimming']) && $actorFlags[$charKey]['isswimming']) {
                $line .= " - swimming";
            }
            
            if (isset($actorFlags[$charKey]['isonmount']) && $actorFlags[$charKey]['isonmount']) {
                $line .= " - on horseback";
            }
            
            if (isset($actorFlags[$charKey]['isincombat']) && $actorFlags[$charKey]['isincombat']) {
                $line .= " - in combat";
            }
            
            if (isset($actorFlags[$charKey]['isencumbered']) && $actorFlags[$charKey]['isencumbered']) {
                $line .= " - encumbered";
            }
            
            // Add hostility flag
            if (isset($actorFlags[$charKey]['hostiletoplayer']) && $actorFlags[$charKey]['hostiletoplayer']) {
                $line .= " - hostile to outsiders";
            }
            
            // Add hygiene information
            if (isset($actorValues[$charKey]['dirtandblood']) && !empty($actorValues[$charKey]['dirtandblood'])) {
                $hygiene = $actorValues[$charKey]['dirtandblood'];
                
                if (stripos($hygiene, "Clean") !== false) {
                    $line .= " - clean";
                } elseif (stripos($hygiene, "Dirt4") !== false) {
                    $line .= " - filthy";
                } elseif (stripos($hygiene, "Dirt3") !== false) {
                    $line .= " - very dirty";
                } elseif (stripos($hygiene, "Dirt2") !== false) {
                    $line .= " - dirty";
                } elseif (stripos($hygiene, "Dirt1") !== false) {
                    $line .= " - slightly dirty";
                } elseif (stripos($hygiene, "Blood4") !== false) {
                    $line .= " - covered in blood";
                } elseif (stripos($hygiene, "Blood3") !== false) {
                    $line .= " - bloody";
                } elseif (stripos($hygiene, "Blood2") !== false) {
                    $line .= " - blood-spattered";
                } elseif (stripos($hygiene, "Blood1") !== false) {
                    $line .= " - blood-stained";
                } elseif (stripos($hygiene, "Bathing") !== false) {
                    $line .= " - bathing";
                }
                
                // Add scent information
                if (stripos($hygiene, "Lavender") !== false) {
                    $line .= " - lavender scented";
                } elseif (stripos($hygiene, "Blue") !== false) {
                    $line .= " - fresh scented";
                } elseif (stripos($hygiene, "Red") !== false) {
                    $line .= " - rose scented";
                } elseif (stripos($hygiene, "DragonsTongue") !== false) {
                    $line .= " - spicy scented";
                } elseif (stripos($hygiene, "Purple") !== false) {
                    $line .= " - jazbay scented";
                } elseif (stripos($hygiene, "Superior") !== false) {
                    $line .= " - luxuriously scented";
                }
            }
            
            $contextLines[] = $line;
        }
        
        return implode("\n", $contextLines);
    }
    
    return "";
}

/**
 * Build the NPC relationships context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted NPC relationships context
 */
function BuildNPCRelationshipsContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['target'];
    $player_name = $params['player_name'];
    $utilities = new Utilities();
    
    // If the target is the player, no need for this context
    if ($character == $player_name) {
        return "";
    }
    
    $context = "";
    
    // Check if NPC is bribed
    if (IsEnabled($character, "isBribed")) {
        $context .= $character . " seems smug around " . $player_name . ". ";
    }
    
    // Check if NPC is intimidated
    if (IsEnabled($character, "isIntimidated")) {
        $context .= $character . " seems anxious and a little frightened around " . $player_name . ". ";
    }
    
    return $context;
}

/**
 * Build the nearby buildings context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted nearby buildings context
 */
function BuildNearbyBuildingsContext($params) {
    $player_name = $params['player_name'];
    $locationContext = GetCurrentLocationContext($player_name);
    
    // Check if we have buildings information
    if (empty($locationContext['buildings']) || count($locationContext['buildings']) == 0) {
        return "";
    }
    
    $context = "";
    
    foreach ($locationContext['buildings'] as $building) {
        if (!empty($building['name'])) {
            $context .= "- " . $building['name'];
            
            if (!empty($building['destination'])) {
                $context .= " (" . $building['destination'] . ")";
            }
            
            $context .= "\n";
        }
    }
    
    return $context;
}