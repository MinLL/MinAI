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
        'builder_callback' => 'BuildRelationshipContext'
    ]);
    
    // Register devious follower context builder
    $registry->register('devious_follower', [
        'section' => 'status',
        'header' => 'Devious Follower Status',
        'description' => 'Devious follower special status',
        'priority' => 90,
        'is_nsfw' => true,
        'builder_callback' => 'BuildDeviousFollowerContext'
    ]);
    
    // Register submissive lola context builder
    $registry->register('submissive_lola', [
        'section' => 'status',
        'header' => 'Submissive Status',
        'description' => 'Submissive lola mod status',
        'priority' => 95,
        'is_nsfw' => true,
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
    $source = isset($params['is_target']) && $params['is_target'] 
              ? $params['target'] 
              : $params['herika_name'];
    
    // The other character in the relationship
    $other = isset($params['is_target']) && $params['is_target'] 
             ? $params['herika_name'] 
             : $params['target'];
    
    // Skip if narrator involved or if source and other are the same person
    if ($source == "The Narrator" || $other == "The Narrator" || $source == $other) {
        return "";
    }
    // Call the existing GetRelationshipContext function
    return GetRelationshipContext($other);
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