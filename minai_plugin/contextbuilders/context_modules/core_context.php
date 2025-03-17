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
        'header' => 'Background',
        'description' => 'Background information about the player',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['player_background']) ? (bool)$GLOBALS['minai_context']['player_background'] : true,
        'builder_callback' => 'BuildPlayerBackgroundContext'
    ]);
}

/**
 * Build the personality context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted personality context
 */
function BuildPersonalityContext($params) {
    $herika_name = $params['herika_name'];
    if ($herika_name == "The Narrator" || $herika_name == $GLOBALS["PLAYER_NAME"]) {
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
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted interaction context
 */
function BuildInteractionContext($params) {
    $herika_name = $params['herika_name'];
    $target = $params['target'];
    
    return "You are currently interacting with {$target}.";
}

/**
 * Build the player background context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted player background context
 */
function BuildPlayerBackgroundContext($params) {
    $player_name = $params['player_name'];
    $herika_name = $params['herika_name'];
    
    // Only include player background if interacting with the player
    if ($herika_name != $player_name) {
        return "";
    }
    
    // Get player bio from global variables
    $player_bio = isset($GLOBALS["PLAYER_BIOS"]) ? $GLOBALS["PLAYER_BIOS"] : "Missing Player Bio";
    
    if (empty($player_bio)) {
        return "";
    }
    
    return trim($player_bio);
} 