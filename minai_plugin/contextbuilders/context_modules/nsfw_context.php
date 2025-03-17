<?php
/**
 * NSFW Context Builders
 * 
 * This file contains context builders for NSFW content
 */

require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../util.php");
require_once(__DIR__ . "/../system_prompt_context.php");
// require_once(__DIR__ . "/../../reputation.php");

/**
 * Initialize NSFW context builders
 */
function InitializeNSFWContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register NSFW reputation context builder
    $registry->register('nsfw_reputation', [
        'section' => 'status',
        'header' => 'Sexual Reputation',
        'description' => 'Character sexual reputation',
        'priority' => 100,
        'is_nsfw' => true,
        'enabled' => isset($GLOBALS['minai_context']['nsfw_reputation']) ? (bool)$GLOBALS['minai_context']['nsfw_reputation'] : true,
        'builder_callback' => 'BuildNSFWReputationContext'
    ]);
    
    // Register other NSFW builders here as needed
}

/**
 * Build the NSFW reputation context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted NSFW reputation context
 */
function BuildNSFWReputationContext($params) {
    $character = $params['herika_name'];
    
    // Call the existing BuildNSFWReputationContext function
    // Renamed here to avoid conflict
    return GetNSFWReputationContext($character);
}

/**
 * Helper function to call the original BuildNSFWReputationContext
 * 
 * @param string $character Character name
 * @return string Formatted NSFW reputation context
 */
function GetNSFWReputationContext($character) {
    return "";
} 