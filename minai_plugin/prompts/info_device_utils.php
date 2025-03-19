<?php

// Utility functions for device descriptions and context

// Get context about devices and arousal for a target
function GetInfoDeviceContext($target) {   
    return GetVibratingDevicesContext($target);
}

// Function to get device-specific description using the specified terms
function GetDeviceDescription($target) {
    // Get vibration sources with accessibility info
    $vibrationData = GetVibrationSources($target);
    $vibrationSources = isset($vibrationData["sources"]) ? $vibrationData["sources"] : [];
    
    if (empty($vibrationSources)) {
        return "hidden devices";
    }
    
    // Organize devices by type for more natural language
    $devicesByType = [
        "anal" => [],
        "vaginal" => [],
        "clitoral" => [],
        "nipple" => [],
        "other" => []
    ];
    
    foreach ($vibrationSources as $source) {
        if (strpos($source, "anal") !== false) {
            $devicesByType["anal"][] = $source;
        } 
        elseif (strpos($source, "vaginal") !== false) {
            $devicesByType["vaginal"][] = $source;
        }
        elseif (strpos($source, "clitoral") !== false || strpos($source, "labia") !== false) {
            $devicesByType["clitoral"][] = $source;
        }
        elseif (strpos($source, "nipple") !== false) {
            $devicesByType["nipple"][] = $source;
        }
        else {
            $devicesByType["other"][] = $source;
        }
    }
    
    // Generate descriptive text for each type
    $descriptions = [];
    
    if (!empty($devicesByType["anal"])) {
        $descriptions[] = FormatDeviceType($devicesByType["anal"], "anal");
    }
    
    if (!empty($devicesByType["vaginal"])) {
        $descriptions[] = FormatDeviceType($devicesByType["vaginal"], "vaginal");
    }
    
    if (!empty($devicesByType["clitoral"])) {
        $descriptions[] = FormatDeviceType($devicesByType["clitoral"], "clitoral");
    }
    
    if (!empty($devicesByType["nipple"])) {
        $descriptions[] = FormatDeviceType($devicesByType["nipple"], "nipple");
    }
    
    if (!empty($devicesByType["other"])) {
        $descriptions[] = FormatDeviceType($devicesByType["other"], "other");
    }
    
    // Combine descriptions with appropriate conjunctions
    if (count($descriptions) == 1) {
        return $descriptions[0];
    } 
    elseif (count($descriptions) == 2) {
        return $descriptions[0] . " and " . $descriptions[1];
    }
    else {
        $last = array_pop($descriptions);
        return implode(", ", $descriptions) . ", and " . $last;
    }
}

// Helper function to format device types into natural language with erotic descriptions
function FormatDeviceType($devices, $type) {
    $count = count($devices);
    $deviceType = isset($devices[0]) ? $devices[0] : "unknown device";
        
    // Processing for a single device - provide erotic descriptions
    switch ($type) {
        case "anal":
            if (strpos($deviceType, "bead") !== false) {
                return "smooth anal beads stretching their tight rear entrance";
            } else {
                return "firm butt plug nestled deep inside their ass";
            }
            
        case "vaginal":
            if (strpos($deviceType, "dildo") !== false) {
                return "thick pleasure plug nestled against their sensitive depths";
            } else {
                return "sleek vaginal plug buried deep inside their wet pussy";
            }
            
        case "clitoral":
            if (strpos($deviceType, "ring") !== false) {
                return "delicate metal ring pierced through their swollen clit";
            } else if (strpos($deviceType, "labia") !== false) {
                return "intimate jewelry pierced through their sensitive labia";
            } else {
                return "small metal piercing through their responsive clit";
            }
            
        case "nipple":
            if (strpos($deviceType, "clamp") !== false) {
                return "tight metal clamps gripping their hardened nipples";
            } else if (strpos($deviceType, "ring") !== false) {
                return "heavy metal rings pierced through their sensitive nipples";
            } else {
                return "steel piercings through their erect nipples";
            }
            
        default:
            // For unknown types, provide a generic but erotic description
            return "intimate device against their sensitive flesh";
    }
}

// Function to get reaction intensity based on arousal level
function GetReactionIntensity($arousal) {
    if ($arousal < 30) {
        return "low";
    } 
    elseif ($arousal < 60) {
        return "medium";
    }
    elseif ($arousal < 80) {
        return "high";
    }
    else {
        return "very_high";
    }
}
