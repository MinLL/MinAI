<?php
/**
 * Character Context Builders
 * 
 * This file contains context builders related to character status and attributes
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");
require_once(__DIR__ . "/../../dirtandblood.php");
require_once(__DIR__ . "/../../exposure.php");
require_once(__DIR__ . "/../../fertilitymode.php");

/**
 * Helper function to validate and sanitize parameters for context builders
 * 
 * @param array $params Parameters to validate
 * @param array $required List of required parameter keys
 * @return array Validated and sanitized parameters with fallbacks if needed
 */
function ValidateContextParams($params, $required = ['herika_name']) {
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
 * Initialize character context builders
 */
function InitializeCharacterContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register physical description context builder
    $registry->register('physical_description', [
        'section' => 'status',
        'header' => 'Physical Appearance',
        'description' => 'Physical description of the character',
        'priority' => 10,
        'builder_callback' => 'BuildPhysicalDescriptionContext'
    ]);
    
    // Register equipment context builder
    $registry->register('equipment', [
        'section' => 'status',
        'header' => 'Equipment',
        'description' => 'Equipment and worn items',
        'priority' => 20,
        'builder_callback' => 'BuildEquipmentContext'
    ]);
    
    // Register tattoos context builder
    $registry->register('tattoos', [
        'section' => 'status',
        'header' => 'Tattoos',
        'description' => 'Character tattoos',
        'priority' => 30,
        'builder_callback' => 'BuildTattooContext'
    ]);
    
    // Register arousal context builder
    $registry->register('arousal', [
        'section' => 'status',
        'header' => 'Arousal Status',
        'description' => 'Character arousal level',
        'priority' => 40,
        'is_nsfw' => true,
        'builder_callback' => 'BuildArousalContext'
    ]);
    
    // Register fertility context builder
    $registry->register('fertility', [
        'section' => 'status',
        'header' => 'Fertility Status',
        'description' => 'Character fertility status',
        'priority' => 50,
        'is_nsfw' => true,
        'builder_callback' => 'BuildFertilityContext'
    ]);
    
    // Register following context builder
    $registry->register('following', [
        'section' => 'status',
        'header' => 'Following Status',
        'description' => 'Character following status',
        'priority' => 60,
        'builder_callback' => 'BuildFollowingContext'
    ]);
    
    // Register survival context builder
    $registry->register('survival', [
        'section' => 'status',
        'header' => 'Survival Status',
        'description' => 'Character survival needs',
        'priority' => 70,
        'builder_callback' => 'BuildSurvivalContext'
    ]);
    
    // Register player status context builder
    $registry->register('player_status', [
        'section' => 'interaction',
        'header' => 'Current Status',
        'description' => 'Current status of the player character',
        'priority' => 30,
        'builder_callback' => 'BuildPlayerStatusContext'
    ]);
    
    // Register bounty context builder
    $registry->register('bounty', [
        'section' => 'interaction',
        'header' => 'Bounty Status',
        'description' => 'Player bounty status',
        'priority' => 40,
        'builder_callback' => 'BuildBountyContext'
    ]);
    
    // Register mind influence context builder
    $registry->register('mind_influence', [
        'section' => 'status',
        'header' => 'Mind State',
        'description' => 'Character mind influence state',
        'priority' => 80,
        'builder_callback' => 'BuildMindInfluenceContext'
    ]);
}

/**
 * Build the physical description context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted physical description context
 */
function BuildPhysicalDescriptionContext($params) {
    // Determine which character we're building context for
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    $gender = GetActorValue($character, "gender");
    $race = GetActorValue($character, "race");
    $beautyScore = GetActorValue($character, "beautyScore");
    $breastsScore = GetActorValue($character, "breastsScore");
    $buttScore = GetActorValue($character, "buttScore");
    $isexposed = GetActorValue($character, "isexposed");
    
    $ret = "";
    $isWerewolf = false;
    
    if ($gender != "" && $race != "") {
        $ret .= "{$character} is a {$gender} {$race}. ";
        if ($race == "werewolf") {
            $isWerewolf = true;
            $ret .= "{$character} is currently transformed into a terrifying werewolf! ";
        }
    }
    
    // Don't add beauty/physical attributes for NPCs unless specified
    $isPlayer = IsPlayer($character);
    $addPhysicalDetails = $isPlayer || isset($params['add_npc_physical_details']);
    
    if (!$addPhysicalDetails) {
        return $ret;
    }
    
    if (!empty($beautyScore) && $beautyScore != "0" && !$isWerewolf) {
        $beautyScore = ceil(intval($beautyScore)/10);
        $ret .= ($gender == "female" ? "She" : "He") . " is a {$beautyScore}/10 in terms of beauty ";
    }
    
    if((!empty($breastsScore) && $breastsScore != "0") && (!empty($buttScore) && $buttScore != "0") && !$isWerewolf) {
        $breastsScore = ceil(intval($breastsScore)/10);
        $buttScore = ceil(intval($buttScore)/10);
        $ret .= "with {$breastsScore}/10 tits and a {$buttScore}/10 ass. ";
    }
    
    if (IsEnabled($character, "isexposed")) {
        $ret .= GetPenisSize($character);
    }
    
    return $ret;
}

/**
 * Get the description of a character's penis size
 * 
 * @param string $name Character name
 * @return string Formatted penis size description
 */
function GetPenisSize($name) {
    $tngsize = GetActorValue($name, "tngsize");
    $sizeDescription = "";
    
    if (HasKeyword($name, "TNG_XL") || ($tngsize == 4)) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_L") || ($tngsize == 3)) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize") || ($tngsize == 2)) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_S") || ($tngsize == 1)) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_XS") || ($tngsize == 0)) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
    
    if ($sizeDescription != "") {
        return "{$name} has {$sizeDescription}. ";
    }
    
    return "";
}

/**
 * Build the equipment context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted equipment context
 */
function BuildEquipmentContext($params) {
    // Determine which character we're building context for
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    // This function calls the existing GetUnifiedEquipmentContext function
    return GetUnifiedEquipmentContext($character);
}

/**
 * Build the tattoo context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted tattoo context
 */
function BuildTattooContext($params) {
    // Determine which character we're building context for
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    $tattoo = GetActorValue($character, "tattoo");
    
    $ret = "";
    if (!empty($tattoo)) {
        $ret .= "{$character} has the following visible tattoos: {$tattoo}.";
    }
    
    return $ret;
}

/**
 * Build the arousal context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted arousal context
 */
function BuildArousalContext($params) {
    // Determine which character we're building context for
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    $arousal = GetActorValue($character, "arousal");
    
    $ret = "";
    if (!empty($arousal)) {
        $ret .= "{$character}'s sexual arousal level is {$arousal}/100, where 0 is not aroused at all, and 100 is desperate for sex.";
    }
    
    return $ret;
}

/**
 * Build the fertility context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted fertility context
 */
function BuildFertilityContext($params) {
    $character = $params['herika_name'];
    
    // This function would call the existing GetFertilityContext function
    return GetFertilityContext($character);
}

/**
 * Build the following context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted following context
 */
function BuildFollowingContext($params) {
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    
    if (IsFollowing($character)) {
        return "{$character} is following, walking, or escorting {$player_name}";
    }
    
    return "";
}

/**
 * Build the survival context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted survival context
 */
function BuildSurvivalContext($params) {
    // Determine which character we're building context for
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    $hunger = GetActorValue($character, "hunger");
    $thirst = GetActorValue($character, "thirst");
    $fatigue = GetActorValue($character, "fatigue");
    $cold = GetActorValue($character, "cold");
    
    $ret = "";
    
    if (!empty($hunger)) {
        $ret .= "{$character}'s hunger level is at {$hunger}%, where 0 is not hungry at all, and 100 is starving. ";
    }
    
    if (!empty($thirst)) {
        $ret .= "{$character}'s thirst level is at {$thirst}%, where 0 is not thirsty at all, and 100 is dying of thirst. ";
    }
    
    if (!empty($fatigue)) {
        $ret .= "{$character}'s fatigue level is at {$fatigue}%, where 0 is not tired at all, and 100 is exhausted. ";
    }
    
    if (!empty($cold)) {
        $ret .= "{$character}'s cold level is at {$cold}%, where 0 is not cold at all, and 100 is freezing to death.";
    }
    
    return $ret;
}

/**
 * Build the player status context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted player status context
 */
function BuildPlayerStatusContext($params) {
    $player_name = $params['player_name'];
    $character = isset($params['is_target']) && $params['is_target'] 
                ? $params['target'] 
                : $params['herika_name'];
    
    // Only include player status when we're building context for the player specifically
    if ($character != $player_name) {
        return "";
    }
    
    // Get the player background
    $player_background = isset($GLOBALS["PLAYER_BACKGROUND"]) ? $GLOBALS["PLAYER_BACKGROUND"] : "";
    
    // Replace placeholder with actual player name if needed
    $player_background = str_replace("#PLAYER_NAME#", $player_name, $player_background);
    
    if (empty($player_background)) {
        $player_background = "I'm {$player_name}, the Dragonborn.";
    }
    
    return $player_background;
}

/**
 * Build the bounty context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted bounty context
 */
function BuildBountyContext($params) {
    // Check if required parameters exist
    if (!isset($params['herika_name']) || !isset($params['player_name']) || !isset($params['target'])) {
        // Try to use globals as fallback
        $herika_name = isset($params['herika_name']) ? $params['herika_name'] : 
                      (isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "");
        $player_name = isset($params['player_name']) ? $params['player_name'] : 
                      (isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "");
        $target = isset($params['target']) ? $params['target'] : 
                (isset($GLOBALS["HERIKA_TARGET"]) ? $GLOBALS["HERIKA_TARGET"] : $player_name);
        
        // If we still don't have the required parameters, return empty string
        if (empty($herika_name) || empty($player_name)) {
            return "";
        }
    } else {
        $herika_name = $params['herika_name'];
        $player_name = $params['player_name'];
        $target = $params['target'];
    }
    
    // Only include bounty context if talking to a guard or the narrator
    if ($target != $player_name || !($herika_name == "The Narrator" || 
        IsInFaction($herika_name, "GuardFaction") || 
        IsInFaction($herika_name, "Guard Faction"))) {
        return "";
    }
    
    // This function would call the existing GetBountyContext function
    return GetBountyContext($player_name);
}

/**
 * Build the mind influence context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted mind influence context
 */
function BuildMindInfluenceContext($params) {
    // Check if herika_name is set in params, otherwise use a fallback
    if (!isset($params['herika_name'])) {
        // Fallback to GLOBALS if available
        if (isset($GLOBALS["HERIKA_NAME"])) {
            $herika_name = $GLOBALS["HERIKA_NAME"];
            $character = $GLOBALS["HERIKA_NAME"];
        } else {
            // If we can't determine the character, return empty string
            return "";
        }
    } else {
        $herika_name = $params['herika_name'];
        $character = $params['herika_name'];
    }
    
    // Only include mind influence context for the narrator
    if ($herika_name != "The Narrator") {
        return "";
    }
    
    $mindState = GetMindInfluenceState($character);
    if ($mindState == "normal") {
        return "";
    }
    
    // This function would call the existing GetMindInfluenceContext function
    return GetMindInfluenceContext($mindState);
} 