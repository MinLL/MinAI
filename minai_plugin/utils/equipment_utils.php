<?php
// Static cache for equipment data to avoid repeated parsing
$GLOBALS['equipment_cache'] = array();

/**
 * Equipment Layer Constants
 */
define('LAYER_BODY', 0);      // Piercings, plugs, body modifications
define('LAYER_UNDERWEAR', 1); // Underwear, small garments
define('LAYER_CLOTHING', 2);  // Main clothing layer
define('LAYER_OUTERWEAR', 3); // Armor, cloaks, etc.

/**
 * Equipment Category Constants
 */
define('CAT_CLOTHING', 'clothing');
define('CAT_RESTRAINT', 'restraint');
define('CAT_PIERCING', 'piercing');
define('CAT_VIBRATOR', 'vibrator');
define('CAT_PLUG', 'plug');
define('CAT_ACCESSORY', 'accessory');
define('CAT_OTHER', 'other');

/**
 * Body Area Constants
 */
define('AREA_HEAD', 'head');
define('AREA_NECK', 'neck');
define('AREA_CHEST', 'chest');
define('AREA_TORSO', 'torso');
define('AREA_ARMS', 'arms');
define('AREA_HANDS', 'hands');
define('AREA_WAIST', 'waist');
define('AREA_GROIN', 'groin');
define('AREA_ANUS', 'anus');
define('AREA_LEGS', 'legs');
define('AREA_FEET', 'feet');
define('AREA_BODY', 'body');  // Full body

/**
 * Equipment Cache System
 */
if (!isset($GLOBALS['equipment_cache'])) {
    $GLOBALS['equipment_cache'] = [];
}

// Legacy function that wraps the consolidated function to maintain compatibility
Function GetVibratingDevicesContext($name) {
    // Use the new equipment context system
    $parsedEquipment = ProcessEquipment($name);
    
    // Filter for only vibrating devices
    $visibleItems = [];
    $hiddenItems = [];
    
    // Process visible items
    if (isset($parsedEquipment['visibleItems']) && is_array($parsedEquipment['visibleItems'])) {
        foreach ($parsedEquipment['visibleItems'] as $item) {
            // Check if item is vibrating
            if (isset($item['isVibrating']) && $item['isVibrating']) {
                $visibleItems[] = $item;
            } elseif (isset($item['category']) && $item['category'] == 'vibrator') {
                // All vibrators are considered to be vibrating
                $item['isVibrating'] = true;
                $visibleItems[] = $item;
            }
        }
    }
    
    // Process hidden items
    if (isset($parsedEquipment['hiddenItems']) && is_array($parsedEquipment['hiddenItems'])) {
        foreach ($parsedEquipment['hiddenItems'] as $item) {
            // Check if item is vibrating
            if (isset($item['isVibrating']) && $item['isVibrating']) {
                $hiddenItems[] = $item;
            } elseif (isset($item['category']) && $item['category'] == 'vibrator') {
                // All vibrators are considered to be vibrating
                $item['isVibrating'] = true;
                $hiddenItems[] = $item;
            }
        }
    }
    $arousal = intval(GetActorValue($name, "arousal"));
    return [
        'visibleItems' => $visibleItems,
        'hiddenItems' => $hiddenItems,
        'arousal' => $arousal
    ];
}

// Function to identify vibration sources for a character
Function GetVibrationSources($name) {
    // Get equipment data using new context system
    $parsedEquipment = ProcessEquipment($name);
    $vibratingDevices = GetVibratingDevicesContext($name);
    
    $sources = [];
    $accessibilityInfo = [];
    
    // Check for restraint-related keywords for accessibility information
    $hasChastityBelt = HasEquipmentKeyword($name, "zad_DeviousBelt");
    $hasChastityBra = HasEquipmentKeyword($name, "zad_DeviousBra");
    
    // Process both visible and hidden vibrating items
    foreach (['visibleItems', 'hiddenItems'] as $visibility) {
        foreach ($vibratingDevices[$visibility] as $device) {
            // Check for explicit vibrating devices
            if (isset($device['category']) && $device['category'] == 'vibrator') {
                $deviceType = isset($device['type']) ? $device['type'] : 'unknown device';
                $sources[] = $deviceType;
                
                // Check accessibility based on device type
                if (strpos($deviceType, 'vaginal') !== false || strpos($deviceType, 'clitoral') !== false) {
                    $accessibilityInfo[$deviceType] = $hasChastityBelt ? 'inaccessible behind the chastity belt' : 'accessible';
                } elseif (strpos($deviceType, 'anal') !== false) {
                    $accessibilityInfo[$deviceType] = $hasChastityBelt ? 'inaccessible behind the chastity belt' : 'accessible';
                } else {
                    $accessibilityInfo[$deviceType] = 'accessible';
                }
            }
            
            // Piercings can also vibrate
            if (isset($device['category']) && $device['category'] == 'piercing') {
                $piercingType = isset($device['type']) ? $device['type'] : 'piercing';
                $sources[] = $piercingType;
                
                // Check accessibility based on piercing type
                if (strpos($piercingType, 'nipple') !== false) {
                    $accessibilityInfo[$piercingType] = $hasChastityBra ? 'inaccessible behind the chastity bra' : 'accessible';
                } elseif (strpos($piercingType, 'clitoral') !== false || strpos($piercingType, 'labia') !== false) {
                    $accessibilityInfo[$piercingType] = $hasChastityBelt ? 'inaccessible behind the chastity belt' : 'accessible';
                } else {
                    $accessibilityInfo[$piercingType] = 'accessible';
                }
            }
        }
    }
    
    // If no specific sources found but vibration is occurring, check for potential sources
    if (empty($sources) && CanVibrate($name) && (IsInFaction($name, "Vibrator Effect Faction") || IsEnabled($name, "isVibratorActive"))) {
        // Check for potential vibration sources based on known device types
        if (HasEquipmentKeyword($name, "zad_DeviousPiercingsNipple")) {
            $sources[] = "nipple piercings";
            $accessibilityInfo["nipple piercings"] = $hasChastityBra ? "inaccessible behind the chastity bra" : "accessible";
        }
        if (HasEquipmentKeyword($name, "zad_DeviousPiercingsVaginal")) {
            $sources[] = "clitoral ring";
            $accessibilityInfo["clitoral ring"] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
        }
        if (HasEquipmentKeyword($name, "zad_DeviousPlugVaginal")) {
            $sources[] = "vaginal plug";
            $accessibilityInfo["vaginal plug"] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
        }
        if (HasEquipmentKeyword($name, "zad_DeviousPlugAnal")) {
            $sources[] = "anal plug";
            $accessibilityInfo["anal plug"] = $hasChastityBelt ? "partially inaccessible behind the chastity belt" : "accessible";
        }
        
        // If still no sources identified, use a generic source
        if (empty($sources)) {
            $sources[] = "hidden device";
            $accessibilityInfo["hidden device"] = "unknown accessibility";
        }
    }
    
    return [
        "sources" => $sources,
        "accessibility" => $accessibilityInfo
    ];
}

// Helper function to determine the perspective for prompts
Function GetPromptPerspective($target = null) {
    // If target is not specified, use the global target
    if ($target === null) {
        $target = isset($GLOBALS["target"]) ? $GLOBALS["target"] : "";
    }
    
    // If the narrator is the speaking character, use narrator perspective (omniscient view)
    if (isset($GLOBALS["HERIKA_NAME"]) && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
        return "narrator";
    } 
    // If the target is the player, use other perspective (observer view)
    elseif ($target == $GLOBALS["PLAYER_NAME"]) {
        return "other";
    } 
    // For all other cases, use self perspective (wearer's own experience)
    else {
        return "self";
    }
}
