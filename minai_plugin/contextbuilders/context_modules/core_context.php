<?php
/**
 * Core Context Builders
 * 
 * This file contains the basic context builders for the system prompt
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");

/**
 * Initialize core context builders
 */
function InitializeCoreContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register personality context builder
    $registry->register('personality', [
        'section' => 'character',
        'header' => 'Personality',
        'description' => 'Core personality description',
        'priority' => 10, // High priority - should be first in character section
        'enabled' => isset($GLOBALS['minai_context']['personality']) ? (bool)$GLOBALS['minai_context']['personality'] : true,
        'builder_callback' => 'BuildPersonalityContext'
    ]);
    
    // Register dynamic state context builder
    $registry->register('dynamic_state', [
        'section' => 'character',
        'header' => 'Current State',
        'description' => 'Dynamic state information for the character',
        'priority' => 15, // Just after personality but before most other attributes
        'enabled' => isset($GLOBALS['minai_context']['dynamic_state']) ? (bool)$GLOBALS['minai_context']['dynamic_state'] : true,
        'builder_callback' => 'BuildDynamicStateContext'
    ]);
    
    // Register basic interaction context builder
    $registry->register('interaction', [
        'section' => 'interaction',
        'description' => 'Basic information about who the character is interacting with',
        'priority' => 10, // High priority - should be first in interaction section
        'enabled' => isset($GLOBALS['minai_context']['interaction']) ? (bool)$GLOBALS['minai_context']['interaction'] : true,
        'builder_callback' => 'BuildInteractionContext'
    ]);
    
    // Register player background context builder
    $registry->register('player_background', [
        'section' => 'interaction',
        'header' => 'Description of #player_name#',
        'description' => 'NPC perspective of the player',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['player_background']) ? (bool)$GLOBALS['minai_context']['player_background'] : true,
        'builder_callback' => 'BuildPlayerBackgroundContext'
    ]);
}

/**
 * Build the personality context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted personality context
 */
function BuildPersonalityContext($params) {
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = isset($params['player_name']) ? $params['player_name'] : "";
    $target = isset($params['target']) ? $params['target'] : "";


    if ($herika_name == $target) {
        return "";
    }
    
    // Get the personality from global variables
    $herika_pers = isset($GLOBALS["HERIKA_PERS"]) ? $GLOBALS["HERIKA_PERS"] : "";
    
    if (empty($herika_pers)) {
        return "";
    }
    
    return trim($herika_pers);
}

/**
 * Build the basic interaction context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted interaction context
 */
function BuildInteractionContext($params) {
    $herika_name = $params['herika_name'];
    // Only display this once.
    if ($GLOBALS["HERIKA_NAME"] != $herika_name) {
        return "";
    }
    $target = $params['target'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    $player_name = isset($params['player_name']) ? $params['player_name'] : "";
    
    if ($is_self_narrator) {
        return "You are {$player_name}'s inner voice, providing thoughts, perspective, and advice directly to them.";
    }
    
    return "You are currently interacting with {$target}.";
}

/**
 * Build the player background context
 * 
 * @param array $params Parameters including herika_name, player_name, target, is_self_narrator
 * @return string Formatted player background context
 */
function BuildPlayerBackgroundContext($params) {
    $player_name = $params['player_name'];
    $herika_name = $params['herika_name'];
    $is_self_narrator = isset($params['is_self_narrator']) ? $params['is_self_narrator'] : false;
    
    // Include player background if interacting with the player or in self_narrator mode
    if ($herika_name != $player_name && !$is_self_narrator) {
        return "";
    }
    
    // Get player bio from global variables
    $player_bio = isset($GLOBALS["PLAYER_BIOS"]) ? $GLOBALS["PLAYER_BIOS"] : "";
    $player_bio = str_replace("#PLAYER_NAME#", $player_name, $player_bio);
    if (empty($player_bio)) {
        if ($is_self_narrator) {
            return "You are the embodiment of {$player_name}'s thoughts, representing their subconscious perspective of the world around them.";
        }
        return "";
    }
    
    // Add additional context for self_narrator mode
    if ($is_self_narrator) {
        return "As {$player_name}'s inner voice, you understand the following about them:\n\n" . trim($player_bio);
    }
    
    return trim($player_bio);
}

/**
 * Build the dynamic state context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted dynamic state context
 */
function BuildDynamicStateContext($params) {
    $herika_name = $params['herika_name'];
    $target = isset($params['target']) ? $params['target'] : "";
    
    // Only show dynamic state for the character speaking
    if ($herika_name == $target) {
        return "";
    }
    
    // Get dynamic state from global variables
    $dynamic_state = isset($GLOBALS["HERIKA_DYNAMIC"]) ? $GLOBALS["HERIKA_DYNAMIC"] : "";
    // Replace "The Narrator" with player name if in self-narrator mode
    if (isset($params['is_self_narrator']) && $params['is_self_narrator']) {
        $player_name = $params['player_name'];
        $dynamic_state = str_replace("The Narrator", $player_name, $dynamic_state);
    }
    
    if (empty($dynamic_state)) {
        return "";
    }
    
    return trim($dynamic_state);
} 