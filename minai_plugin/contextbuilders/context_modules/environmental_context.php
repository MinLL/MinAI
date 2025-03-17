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
 * Initialize environmental context builders
 */
function InitializeEnvironmentalContextBuilders() {
    $registry = ContextBuilderRegistry::getInstance();
    
    // Register location context builder
    $registry->register('location', [
        'section' => 'environment',
        'header' => 'Current Location',
        'description' => 'Current location information',
        'priority' => 10,
        'enabled' => isset($GLOBALS['minai_context']['location']) ? (bool)$GLOBALS['minai_context']['location'] : true,
        'builder_callback' => 'BuildLocationContext'
    ]);
    
    // Register weather context builder
    $registry->register('weather', [
        'section' => 'environment',
        'header' => 'Weather',
        'description' => 'Current weather conditions',
        'priority' => 20,
        'enabled' => isset($GLOBALS['minai_context']['weather']) ? (bool)$GLOBALS['minai_context']['weather'] : true,
        'builder_callback' => 'BuildWeatherContext'
    ]);
    
    // Register third-party context builder
    $registry->register('third_party', [
        'section' => 'environment',
        'header' => 'Third Party',
        'description' => 'Third party information',
        'priority' => 30,
        'enabled' => isset($GLOBALS['minai_context']['third_party']) ? (bool)$GLOBALS['minai_context']['third_party'] : true,
        'builder_callback' => 'BuildThirdPartyContext'
    ]);
    
    // Register nearby characters context builder
    $registry->register('nearby_characters', [
        'section' => 'environment',
        'header' => 'Nearby Characters',
        'description' => 'Characters in close proximity',
        'priority' => 40,
        'enabled' => isset($GLOBALS['minai_context']['nearby_characters']) ? (bool)$GLOBALS['minai_context']['nearby_characters'] : true,
        'builder_callback' => 'BuildNearbyCharactersContext'
    ]);
}

/**
 * Build the location context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted location context
 */
function BuildLocationContext($params) {
    $herika_name = $params['herika_name'];
    $target = $params['target'];
    
    // Get environmental context for both characters
    $herikaContext = GetEnvironmentalContext($herika_name);
    $targetContext = "";
    
    // Only get target context if it's a different character
    if ($target != $herika_name) {
        $targetContext = GetEnvironmentalContext($target);
    }
    
    // Combine the contexts
    if (!empty($herikaContext) && !empty($targetContext)) {
        return $targetContext . "\n" . $herikaContext;
    } else if (!empty($herikaContext)) {
        return $herikaContext;
    } else if (!empty($targetContext)) {
        return $targetContext;
    }
    
    return "";
}

/**
 * Build the weather context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted weather context
 */
function BuildWeatherContext($params) {
    // Call the existing GetWeatherContext function
    return GetWeatherContext();
}

/**
 * Build the third party context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted third party context
 */
function BuildThirdPartyContext($params) {
    // Call the existing GetThirdPartyContext function
    return GetThirdPartyContext();
}

/**
 * Build the nearby characters context
 * 
 * @param array $params Parameters including herika_name, player_name, target
 * @return string Formatted nearby characters context
 */
function BuildNearbyCharactersContext($params) {
    $utilities = new Utilities();
    $localActors = $utilities->beingsInCloseRange();
    
    if (empty($localActors) || !is_array($localActors)) {
        return "";
    }
    
    $context = implode(", ", $localActors) . "\n";
    
    // Add dirt and blood information
    $context .= GetDirtAndBloodContext($localActors);
    
    // Add exposure information
    $context .= GetExposureContext($localActors);
    
    return $context;
} 