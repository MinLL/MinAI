<?php
/**
 * Relationship Context Builders
 * 
 * This file contains context builders related to relationships between characters
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");
require_once(__DIR__ . "/../../contextbuilders/relationship_context.php");
require_once(__DIR__ . "/../../contextbuilders/deviousfollower_context.php");
require_once(__DIR__ . "/../../contextbuilders/submissivelola_context.php");

/**
 * Initialize relationship context builders
 */
function InitializeRelationshipContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register relationship context builder
    $registry->register('relationship', [
        'section' => 'interaction',
        'header' => 'Relationship',
        'description' => 'Relationship between characters',
        'priority' => 50,
        'enabled' => isset($GLOBALS['minai_context']['relationship']) ? (bool)$GLOBALS['minai_context']['relationship'] : true,
        'builder_callback' => 'BuildRelationshipContext'
    ]);
    
    // Register relative power context builder
    $registry->register('relative_power', [
        'section' => 'interaction',
        'header' => 'Relative Strength',
        'description' => 'Relative power level between characters',
        'priority' => 45,
        'enabled' => isset($GLOBALS['minai_context']['relative_power']) ? (bool)$GLOBALS['minai_context']['relative_power'] : true,
        'builder_callback' => 'BuildRelativePowerContext'
    ]);
    
    // Register devious follower context builder
    $registry->register('devious_follower', [
        'section' => 'status',
        'header' => 'Devious Follower Status',
        'description' => 'Devious follower special status',
        'priority' => 90,
        'is_nsfw' => true,
        'enabled' => isset($GLOBALS['minai_context']['devious_follower']) ? (bool)$GLOBALS['minai_context']['devious_follower'] : true,
        'builder_callback' => 'BuildDeviousFollowerContext'
    ]);
    
    // Register submissive lola context builder
    $registry->register('submissive_lola', [
        'section' => 'status',
        'header' => 'Submissive Status',
        'description' => 'Submissive lola mod status',
        'priority' => 95,
        'is_nsfw' => true,
        'enabled' => isset($GLOBALS['minai_context']['submissive_lola']) ? (bool)$GLOBALS['minai_context']['submissive_lola'] : true,
        'builder_callback' => 'BuildSubmissiveLolaContext'
    ]);
}

/**
 * Build the relationship context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted relationship context
 */
function BuildRelationshipContext($params) {
    // Determine which character's relationship we're describing
    $target = $params['target'];
    $herika_name = $params['herika_name'];
    $player_name = $params['player_name'];
    $force_show_relationship = isset($params['force_relationship']) ? $params['force_relationship'] : false;
    
    // Only show relationship context if the target is the player and not the narrator
    if ($herika_name == "The Narrator" || $target != $player_name) {
        return "";
    }

    // Only show this for the primary actor
    if ($GLOBALS["HERIKA_NAME"] != $herika_name && !$force_show_relationship) {
        return "";
    }
    // Call the existing GetRelationshipContext function
    return GetRelationshipContext($herika_name);
}

/**
 * Build the devious follower context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted devious follower context
 */
function BuildDeviousFollowerContext($params) {
    $character = $params['herika_name'];
    $herika_target = isset($GLOBALS["HERIKA_TARGET"]) ? $GLOBALS["HERIKA_TARGET"] : null;
    
    // Skip if there's a target
    if ($herika_target) {
        return "";
    }
    
    // Call the existing GetDeviousFollowerContext function
    return GetDeviousFollowerContext($character);
}

/**
 * Build the submissive lola context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted submissive lola context
 */
function BuildSubmissiveLolaContext($params) {
    $character = $params['herika_name'];
    $herika_target = isset($GLOBALS["HERIKA_TARGET"]) ? $GLOBALS["HERIKA_TARGET"] : null;
    
    // Skip if there's a target
    if ($herika_target) {
        return "";
    }
    
    // Call the existing GetSubmissiveLolaContext function
    return GetSubmissiveLolaContext($character);
}

/**
 * Get a description of the relative power level between two characters
 *
 * @param int $level1 Level of the first character
 * @param int $level2 Level of the second character
 * @return string Description of the power difference
 */
function GetRelativePowerDescription($level1, $level2) {
    // Calculate the difference in levels
    $diff = $level1 - $level2;
    
    // Define thresholds for different descriptions
    if ($diff >= 30) {
        return "vastly more powerful than";
    } elseif ($diff >= 20) {
        return "much stronger than";
    } elseif ($diff >= 10) {
        return "significantly stronger than";
    } elseif ($diff >= 5) {
        return "stronger than";
    } elseif ($diff > 0) {
        return "slightly stronger than";
    } elseif ($diff == 0) {
        return "evenly matched with";
    } elseif ($diff >= -5) {
        return "slightly weaker than";
    } elseif ($diff >= -10) {
        return "weaker than";
    } elseif ($diff >= -20) {
        return "significantly weaker than";
    } elseif ($diff >= -30) {
        return "much weaker than";
    } else {
        return "vastly outmatched by";
    }
}

/**
 * Build the relative power context between characters
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted relative power context
 */
function BuildRelativePowerContext($params) {
    // Get character information
    $character = $params['herika_name'];
    $player_name = $params['player_name'];
    $target = $params['target'];
    
    if ($character == "The Narrator") {
        $target = $player_name;
    }
    
    // Skip if there's no target or if target is the same as character
    if (empty($target) || $target == $character) {
        return "";
    }
    
    // Get level information for both characters
    $character_level = intval(GetActorValue($character, "level"));
    $target_level = intval(GetActorValue($target, "level"));
    
    // If we can't get level info for either character, return empty string
    if (empty($character_level) || empty($target_level)) {
        return "";
    }
    
    // Get the description of the power difference
    $power_relation = GetRelativePowerDescription($character_level, $target_level);
    
    // Format the output
    return "{$character} is {$power_relation} {$target} in combat ability.";
} 