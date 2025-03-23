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
    
    // Register frostfall context builder (if the mod is enabled)
    $registry->register('frostfall', [
        'section' => 'environment',
        'header' => 'Temperature & Exposure',
        'description' => 'Temperature and exposure information from Frostfall',
        'priority' => 25,
        'enabled' => isset($GLOBALS['minai_context']['frostfall']) ? (bool)$GLOBALS['minai_context']['frostfall'] : true,
        'builder_callback' => 'BuildFrostfallContext'
    ]);
    
    // Register character state context builder
    $registry->register('character_state', [
        'section' => 'environment',
        'header' => 'Character State',
        'description' => 'Sitting, sleeping, swimming, etc.',
        'priority' => 30,
        'enabled' => isset($GLOBALS['minai_context']['character_state']) ? (bool)$GLOBALS['minai_context']['character_state'] : true,
        'builder_callback' => 'BuildCharacterStateContext'
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
    $params = ValidateEnvironmentParams($params);
    $character = $params['target'];
    $utilities = new Utilities();
    
    $dayState = $utilities->GetActorValue($character, "dayState");
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
    $moonPhase = $utilities->GetActorValue($character, "moonPhase");
    $isNight = IsEnabled($character, "isNight");
    $moonCount = $utilities->GetActorValue($character, "moonCount");
    
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
 * Build the location context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted location context
 */
function BuildLocationContext($params) {
    $player_name = $params['player_name'];
    $utilities = new Utilities();
    
    if (IsEnabled($player_name, "isInterior")) {
        return "We are indoors, out of the weather and elements.";
    }
    
    return "";
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
    $temperature = $utilities->GetActorValue($character, "temperature");
    if (!empty($temperature)) {
        $context .= "The temperature is " . $temperature . ". ";
    }
    
    // Weather severity
    $weatherSeverity = $utilities->GetActorValue($character, "weatherSeverity");
    if (!empty($weatherSeverity)) {
        $context .= $weatherSeverity . " ";
    }
    
    // Shelter status
    if (IsEnabled($character, "isSheltered")) {
        $context .= $character . " is sheltered by things overhead. ";
    }
    
    // Wetness level
    $wetnessLevel = $utilities->GetActorValue($character, "wetnessLevel");
    if (!empty($wetnessLevel)) {
        $context .= $character . " is " . $wetnessLevel . ". ";
    }
    
    // Exposure level
    $exposureLevel = $utilities->GetActorValue($character, "exposureLevel");
    if (!empty($exposureLevel)) {
        $context .= $exposureLevel . " ";
    }
    
    // Baseline exposure
    $baselineExposure = $utilities->GetActorValue($character, "baselineExposure");
    if (!empty($baselineExposure)) {
        $context .= $baselineExposure . " ";
    }
    
    // Warmth rating
    $warmthRating = $utilities->GetActorValue($character, "warmthRating");
    if (!empty($warmthRating)) {
        $context .= $character . " is dressed " . $warmthRating . ". ";
    }
    
    // Coverage rating
    $coverageRating = $utilities->GetActorValue($character, "coverageRating");
    if (!empty($coverageRating)) {
        $context .= $character . " " . $coverageRating . " ";
    }
    
    return $context;
}

/**
 * Build the character state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted character state context
 */
function BuildCharacterStateContext($params) {
    $params = ValidateEnvironmentParams($params);
    $character = $params['target'];
    $utilities = new Utilities();
    
    $context = "";
    
    // Sitting state - interpret the raw sit state value
    $sitStateValue = $utilities->GetActorValue($character, "sitState");
    if (!empty($sitStateValue)) {
        $sitStateDesc = "";
        switch (intval($sitStateValue)) {
            case 4: 
                $sitStateDesc = "sitting but wants to stand";
                break;
            case 3: 
                $sitStateDesc = "sitting";
                break;
            case 2: 
                $sitStateDesc = "wants to sit";
                break;
            case 0: 
            default:
                $sitStateDesc = "";
                break;
        }
        
        if (!empty($sitStateDesc)) {
            $context .= $character . " is " . $sitStateDesc . ". ";
        }
    }
    
    // Sleep state
    $sleepState = $utilities->GetActorValue($character, "sleepState");
    if (!empty($sleepState)) {
        $context .= $character . " is " . $sleepState . ". ";
    }
    
    // Encumbrance
    if (IsEnabled($character, "isEncumbered")) {
        $context .= $character . " is overly encumbered and slow to move, carrying exhausting weight. ";
    }
    
    // Mount status
    if (IsEnabled($character, "isOnMount")) {
        $context .= $character . " is riding a horse. ";
    }
    
    // Swimming status
    if (IsEnabled($character, "isSwimming")) {
        $context .= $character . " is swimming. ";
    }
    
    // Sneaking status
    if (IsEnabled($character, "isSneaking")) {
        $context .= $character . " is sneaking. ";
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
    
    // Clean and format the list of characters
    $localActors = ltrim($localActors, "(");
    $localActors = rtrim($localActors, ")");
    $characters = explode("|", $localActors);
    // Remove any empty entries and trim each character name
    $characters = array_filter(array_map('trim', $characters), function($item) {
        return !empty($item);
    });
    // Ensure herika_name, player_name and target are included without duplicates
    $characters = array_unique(array_merge($characters, array_filter([$herika_name, $player_name, $target])));


    // If we have characters after cleaning, create the formatted list
    if (count($characters) > 0) {
        $context = "";
        $utilities = new Utilities();
        
        foreach ($characters as $character) {
            // Basic character info
            $context .= $character;
            
            // Get race if available
            $race = $utilities->GetActorValue($character, "race");
            if (!empty($race)) {
                $context .= " (" . $race;
                
                // Get gender if available
                $gender = $utilities->GetActorValue($character, "gender");
                if (!empty($gender)) {
                    $context .= " " . $gender;
                }
                
                $context .= ")";
            }
            
            // Add faction info if available
            $faction = $utilities->GetActorValue($character, "faction");
            if (!empty($faction)) {
                $context .= " - " . $faction;
            }
            
            
            // Add character state
            $sitStateValue = $utilities->GetActorValue($character, "sitState");
            if (!empty($sitStateValue) && intval($sitStateValue) == 3) {
                $context .= " - sitting";
            }
            
            // Check if character is sleeping
            $sleepState = $utilities->GetActorValue($character, "sleepState");
            if (!empty($sleepState) && $sleepState != "awake") {
                $context .= " - " . $sleepState;
            }
            
            // Movement and combat states
            if (IsEnabled($character, "isSneaking")) {
                $context .= " - sneaking";
            }
            
            if (IsEnabled($character, "isSwimming")) {
                $context .= " - swimming";
            }
            
            if (IsEnabled($character, "isOnMount")) {
                $context .= " - on horseback";
            }
            
            if (IsEnabled($character, "isInCombat")) {
                $context .= " - in combat";
            }
            
            if (IsEnabled($character, "isEncumbered")) {
                $context .= " - encumbered";
            }
            
            // Add hostility flag
            if (IsEnabled($character, "hostileToPlayer")) {
                $context .= " - hostile to outsiders";
            }
            
            // Add hygiene information
            $hygiene = $utilities->GetActorValue($character, "dirtAndBlood");
            if (!empty($hygiene)) {
                if (stripos($hygiene, "Clean") !== false) {
                    $context .= " - clean";
                } elseif (stripos($hygiene, "Dirt4") !== false) {
                    $context .= " - filthy";
                } elseif (stripos($hygiene, "Dirt3") !== false) {
                    $context .= " - very dirty";
                } elseif (stripos($hygiene, "Dirt2") !== false) {
                    $context .= " - dirty";
                } elseif (stripos($hygiene, "Dirt1") !== false) {
                    $context .= " - slightly dirty";
                } elseif (stripos($hygiene, "Blood4") !== false) {
                    $context .= " - covered in blood";
                } elseif (stripos($hygiene, "Blood3") !== false) {
                    $context .= " - bloody";
                } elseif (stripos($hygiene, "Blood2") !== false) {
                    $context .= " - blood-spattered";
                } elseif (stripos($hygiene, "Blood1") !== false) {
                    $context .= " - blood-stained";
                } elseif (stripos($hygiene, "Bathing") !== false) {
                    $context .= " - bathing";
                }
                
                // Add scent information
                if (stripos($hygiene, "Lavender") !== false) {
                    $context .= " - lavender scented";
                } elseif (stripos($hygiene, "Blue") !== false) {
                    $context .= " - fresh scented";
                } elseif (stripos($hygiene, "Red") !== false) {
                    $context .= " - rose scented";
                } elseif (stripos($hygiene, "DragonsTongue") !== false) {
                    $context .= " - spicy scented";
                } elseif (stripos($hygiene, "Purple") !== false) {
                    $context .= " - jazbay scented";
                } elseif (stripos($hygiene, "Superior") !== false) {
                    $context .= " - luxuriously scented";
                }
            }
            $context .= "\n";
        }
        
        return $context;
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