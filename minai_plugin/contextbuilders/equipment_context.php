<?php
require_once("wornequipment_context.php");

/**
 * Helper function to get a formatted device name
 * 
 * @param array $device The device data array
 * @param string $name The actor's name
 * @param bool $isNaked Whether the actor is naked
 * @return string The formatted device name
 */
function GetDeviceName($device, $name, $isNaked) {
    // If it's a naked state
    if (isset($device['type']) && $device['type'] === 'naked') {
        return "completely naked and exposed";
    }
    
    // Use full description if available
    if (isset($device['fullDescription']) && !empty($device['fullDescription'])) {
        return $device['fullDescription'];
    }
    
    // Return the base device type if no description is available
    return $device['type'];
}

/**
 * Helper function to get restraint info text
 * 
 * @param string $name The actor's name
 * @param array $device The device data array
 * @param bool $isWeaponDrawn Whether a weapon is drawn
 * @return string The restraint info text
 */
function GetRestraintInfo($name, $device, $isWeaponDrawn) {
    $pronouns = GetActorPronouns($name);
    $their = $pronouns["possessive"];
    
    if (isset($device['limitsMovement']) && $device['limitsMovement']) {
        return " which severely restricts $their movement";
    } else {
        return " which restrains $their " . (isset($device['bodyArea']) ? $device['bodyArea'] : "body");
    }
}

/**
 * Function to provide a unified view of both equipment and devices an actor is wearing.
 * This combines custom equipment descriptions and keyword-based device detection.
 * 
 * @param string $name The actor's name
 * @param bool $forceNarrator Whether to force narrator-perspective (sees hidden items)
 * @return string A formatted description of all the actor's worn items
 */
Function GetUnifiedEquipmentContext($name, $forceNarrator = false) {
    $ret = "";
    $isNarrator = $forceNarrator || ($GLOBALS["HERIKA_NAME"] == "The Narrator");
    
    // Get equipment context - this gives us database-stored custom descriptions
    $eqContext = GetAllEquipmentContext($name);
    
    // Get device context - this gives us keyword-based detection of devices and clothing
    $deviceContext = GetAllDevicesContext($name);
    
    // Check if vibration effects are active
    $isVibratorActive = IsInFaction($name, "Vibrator Effect Faction") || IsEnabled($name, "isVibratorActive");
    
    // Choose which device array to use based on perspective
    $devices = [];
    
    // If the narrator is the target or forced narrator perspective
    if ($isNarrator) {
        $devices = $deviceContext["narratorDevices"];
    } 
    // If the target is the player, use others' perspective
    elseif ($name == $GLOBALS["PLAYER_NAME"]) {
        $devices = $deviceContext["otherDevices"];
    } 
    // For all other cases, use wearer's perspective
    else {
        $devices = $deviceContext["wearerDevices"];
    }
    
    // Get basic info
    $wearingTop = $deviceContext["wearingTop"];
    $wearingBottom = $deviceContext["wearingBottom"];
    $helplessness = $deviceContext["helplessness"];
    $pronouns = $deviceContext["pronouns"];
    $their = $pronouns["possessive"];
    
    // Get specific equipment slots directly
    $boots = GetActorValue($name, "boots", false, true);
    $gloves = GetActorValue($name, "gloves", false, true);
    $helmet = GetActorValue($name, "helmet", false, true);
    $cuirass = GetActorValue($name, "cuirass", false, true);
    
    // Check if the actor is restrained
    $isRestrained = !empty($deviceContext["constraintDevices"]);
    
    // Check if weapon is drawn
    $isWeaponDrawn = IsEnabled($name, "weaponDrawn");
    
    // Check naked state but don't return immediately
    $isNaked = IsEnabled($name, "isNaked") && !$wearingTop && !$wearingBottom;
    
    // Initialize arrays for categorizing items
    $restraints = [];
    $fullbodyClothing = [];
    $topClothing = [];
    $bottomClothing = [];
    $accessories = [];
    $innerWear = [];
    $otherHidden = [];
    $vibratingDevices = ["visible" => [], "hidden" => []]; // New array for tracking vibrating devices
    
    // Define categories for organizing items with visibility already handled
    $categories = [
        "clothing" => ["visible" => [], "hidden" => []],
        "armor" => ["visible" => [], "hidden" => []],
        "restraints" => ["visible" => [], "hidden" => []],
        "piercings" => ["visible" => [], "hidden" => []],
        "plugs" => ["visible" => [], "hidden" => []],
        "other" => ["visible" => [], "hidden" => []]
    ];
    
    // Define keywords for categorization
    $categoryKeywords = [
        "clothing" => ['outfit', 'clothing', 'armor', 'bra', 'panties', 'thong', 'pants', 'skirt', 'leotard', 'curtain', 'bikini', 'attire', 'shorts', 'bikini', 'underwear', 'top', 'bottom'],
        "armor" => ['armor', 'cuirass', 'helmet', 'gloves', 'boots', 'gauntlets', 'shield'],
        "restraints" => ['gag', 'cuffs', 'collar', 'bind', 'restrain', 'restrict', 'chastity', 'yoke', 'armbinder', 'shackles'],
        "piercings" => ['piercing'],
        "plugs" => ['plug', 'vibrator']
    ];
    
    // Process custom equipment descriptions from database first
    $customItems = [];
    if (!empty($eqContext["context"])) {
        // Split the context by commas and periods to get individual items
        $items = preg_split('/[,\.]\s*/', trim($eqContext["context"]));
        foreach ($items as $item) {
            if (!empty(trim($item))) {
                $customItems[] = trim($item);
            }
        }
    }
    
    // Add custom items to appropriate categories
    foreach ($customItems as $item) {
        $added = false;
        $isHidden = (strpos(strtolower($item), 'hidden beneath') !== false);
        $visibilityKey = $isHidden ? "hidden" : "visible";
        
        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos(strtolower($item), $keyword) !== false) {
                    $categories[$category][$visibilityKey][] = $item;
                    $added = true;
                    break;
                }
            }
            if ($added) break;
        }
        
        // If not categorized, add to other
        if (!$added) {
            $categories["other"][$visibilityKey][] = $item;
        }
    }
    
    // Format a device for output
    $formatDevice = function ($name, $device, $isNaked) use ($isRestrained, $isWeaponDrawn) {
        // Format a device for output
        $deviceName = GetDeviceName($device, $name, $isNaked);
        
        if ($isRestrained && isset($device["isRestraint"]) && $device["isRestraint"]) {
            return $deviceName . GetRestraintInfo($name, $device, $isWeaponDrawn);
        } else {
            return $deviceName;
        }
    };
    
    // Process visible devices for all perspectives
    foreach ($devices["visible"] as $device) {
        $deviceName = $formatDevice($name, $device, $isNaked);
        $isRestraint = isset($device["isRestraint"]) && $device["isRestraint"];
        
        // For non-narrator views, check if this should be hidden based on layer
        $shouldBeHidden = false;
        $layer = isset($device["layer"]) ? (int)$device["layer"] : 2;
        
        // Layer 0 and 1 items (body layer, underwear) should be hidden if wearing clothing
        if ($layer <= 1) {
            if (($wearingTop && isset($device["coversTop"])) || 
                ($wearingBottom && isset($device["coversBottom"]))) {
                $shouldBeHidden = true;
            }
        }

        // For non-narrator views, move covered items to hidden
        // For narrator views, respect the original hidden state
        if ($shouldBeHidden || (isset($device["hidden"]) && $device["hidden"])) {
            if (isset($device["category"])) {
                $category = $device["category"];
                if (isset($categories[$category])) {
                    $categories[$category]["hidden"][] = $deviceName;
                } else {
                    $categories["other"]["hidden"][] = $deviceName;
                }
            }
            continue;
        }
        
        // Check if this is a device that can vibrate
        $canVibrate = false;
        if (isset($device["type"])) {
            $deviceType = strtolower($device["type"]);
            $canVibrate = (strpos($deviceType, "vaginal piercing") !== false) ||
                         (strpos($deviceType, "nipple piercing") !== false) ||
                         (strpos($deviceType, "vaginal plug") !== false) ||
                         (strpos($deviceType, "anal plug") !== false);
        }
        
        // Track vibrating devices if they can vibrate and vibration is active
        if ($canVibrate && $isVibratorActive) {
            $vibratingDevices["visible"][] = $deviceName;
        }
        
        // Categorize visible devices
        if ($isRestraint) {
            $restraints[] = $deviceName;
        } elseif (isset($device["coversTop"]) && isset($device["coversBottom"]) && 
                 $device["coversTop"] && $device["coversBottom"]) {
            $fullbodyClothing[] = $deviceName;
        } elseif (isset($device["coversTop"]) && $device["coversTop"]) {
            $topClothing[] = $deviceName;
        } elseif (isset($device["coversBottom"]) && $device["coversBottom"]) {
            $bottomClothing[] = $deviceName;
        } else {
            $accessories[] = $deviceName;
        }
    }
    
    // Process hidden devices differently based on perspective
    if (isset($devices["hidden"]) && !empty($devices["hidden"])) {
        // Narrator sees all hidden items
        if ($isNarrator) {
            foreach ($devices["hidden"] as $device) {
                $deviceName = $formatDevice($name, $device, $isNaked);
                $isRestraint = isset($device["isRestraint"]) && $device["isRestraint"];
                
                // Check if this is a device that can vibrate
                $canVibrate = false;
                if (isset($device["type"])) {
                    $deviceType = strtolower($device["type"]);
                    $canVibrate = (strpos($deviceType, "vaginal piercing") !== false) ||
                                 (strpos($deviceType, "nipple piercing") !== false) ||
                                 (strpos($deviceType, "vaginal plug") !== false) ||
                                 (strpos($deviceType, "anal plug") !== false);
                }
                
                // Track vibrating devices if they can vibrate and vibration is active
                if ($canVibrate && $isVibratorActive) {
                    $vibratingDevices["hidden"][] = $deviceName;
                }
                
                $layer = isset($device["layer"]) ? (int)$device["layer"] : 2;
                
                // Categorize hidden items for narrator view
                if ($isRestraint) {
                    $categories["restraints"]["hidden"][] = $deviceName;
                } elseif (isset($device["category"])) {
                    $category = $device["category"];
                    if (isset($categories[$category])) {
                        $categories[$category]["hidden"][] = $deviceName;
                    } else {
                        $categories["other"]["hidden"][] = $deviceName;
                    }
                } else {
                    // Default categorization based on layer
                    if ($layer <= 1) {
                        $innerWear[] = $deviceName;
                    } else {
                        $otherHidden[] = $deviceName;
                    }
                }
            }
        } 
        // Wearer can see their own restraints and feel devices
        elseif ($name != $GLOBALS["PLAYER_NAME"]) {
            foreach ($devices["hidden"] as $device) {
                $isRestraint = isset($device["isRestraint"]) && $device["isRestraint"];
                
                // Wearer can always see or feel their own restraints
                if ($isRestraint) {
                    $deviceName = $formatDevice($name, $device, $isNaked);
                    $categories["restraints"]["hidden"][] = $deviceName;
                }
                // Wearer can feel certain devices even if hidden
                elseif (isset($device["category"]) && 
                        ($device["category"] == "plugs" || $device["category"] == "vibrator")) {
                    $deviceName = $formatDevice($name, $device, $isNaked);
                    $categories["plugs"]["hidden"][] = $deviceName;
                }
            }
        }
        // Others (including player) might see suggestive cues for hidden devices
        else {
            // This is handled separately with inferredDevices below
        }
    }
    
    // Build the output string with each category grouped by visibility
    
    // Handle nakedness first
    if ($isNaked) {
        $hasVisibleItems = false;
        foreach ($categories as $category => $visibilityGroups) {
            if (!empty($visibilityGroups["visible"])) {
                $hasVisibleItems = true;
                break;
            }
        }
        
        if (!$hasVisibleItems && empty($boots) && empty($gloves) && empty($helmet)) {
            // If truly naked with nothing visible
            $ret .= "{$name} is completely naked and exposed.\n";
        } else {
            // Naked but wearing some visible items
            $ret .= "{$name} is naked";
            
            // Add exceptions for footwear, gloves, helmet
            $exceptions = [];
            if (!empty($boots)) $exceptions[] = $boots;
            if (!empty($gloves)) $exceptions[] = $gloves;
            if (!empty($helmet)) $exceptions[] = $helmet;
            
            if (!empty($exceptions)) {
                $ret .= " except for " . implode(" and ", $exceptions);
            }
            
            $ret .= ".\n";
        }
    }
    
    // Handle visible clothing and armor
    $visibleClothing = array_merge(
        $fullbodyClothing,
        $topClothing,
        $bottomClothing,
        $categories["clothing"]["visible"],
        $categories["armor"]["visible"]
    );
    
    // Remove duplicates and any items that are also in hidden categories
    $visibleClothing = array_unique($visibleClothing);
    $hiddenClothing = array_merge(
        $categories["clothing"]["hidden"],
        $categories["armor"]["hidden"]
    );
    $visibleClothing = array_diff($visibleClothing, $hiddenClothing);
    
    if (!empty($visibleClothing)) {
        $ret .= "{$name} is wearing " . implode(", ", $visibleClothing) . ".\n";
    } elseif (!$isNaked && !empty($cuirass)) {
        // If no other clothing but cuirass is set
        $ret .= "{$name} is wearing {$cuirass}.\n";
    }
    
    // Handle hidden clothing and armor for narrator
    if ($isNarrator) {
        $hiddenClothing = array_merge(
            $categories["clothing"]["hidden"],
            $categories["armor"]["hidden"]
        );
        
        if (!empty($hiddenClothing) || !empty($innerWear)) {
            if (!empty($innerWear)) {
                $ret .= "Beneath " . ($isNaked ? "these items" : "their outer clothing") . ", {$name} is wearing " . implode(", ", $innerWear) . ".\n";
            }
            
            if (!empty($hiddenClothing)) {
                $ret .= "Also hidden from view, {$name} has " . implode(", ", $hiddenClothing) . ".\n";
            }
        }
    }
    
    // Handle restraints grouped by visibility
    $visibleRestraints = array_merge($restraints, $categories["restraints"]["visible"]);
    $hiddenRestraints = $categories["restraints"]["hidden"];
    
    if (!empty($visibleRestraints)) {
        $ret .= "{$name} " . ((!$isNaked && !empty($visibleClothing)) ? "also " : "") . 
                "has " . implode(", ", $visibleRestraints);
      
        // Add helplessness description if relevant
        if (!empty($helplessness)) {
            $ret .= ", and is {$helplessness}";
        }
        $ret .= ".\n";
    } elseif (!empty($helplessness)) {
        // If no visible restraints but helplessness is defined
        $ret .= "{$name} is {$helplessness}.\n";
    }
      
    // Add hidden restraints based on perspective
    if (!empty($hiddenRestraints)) {
        if ($isNarrator) {
            $ret .= "Hidden beneath clothing, {$name} also has " . implode(", ", $hiddenRestraints) . ".\n";
        } elseif ($name != $GLOBALS["PLAYER_NAME"]) {
            // For the wearer's perspective - they know about their restraints even if hidden
            $ret .= "{$name} can feel " . implode(", ", $hiddenRestraints) . " beneath " . $their . " clothing.\n";
        }
    }
    
    // Handle piercings grouped by visibility
    $visiblePiercings = $categories["piercings"]["visible"];
    $hiddenPiercings = $categories["piercings"]["hidden"];
    
    // For non-narrator views, all piercings should be hidden if covered by clothing
    if (!$isNarrator) {
        $hiddenPiercings = array_merge($visiblePiercings, $hiddenPiercings);
        $visiblePiercings = [];
    }
    
    if (!empty($visiblePiercings)) {
        $ret .= "{$name}'s body is adorned with " . implode(", ", $visiblePiercings) . ".\n";
    }
    
    // Add hidden piercings based on perspective
    if (!empty($hiddenPiercings)) {
        if ($isNarrator) {
            $ret .= "Beneath clothing, {$name}'s body is also adorned with " . implode(", ", $hiddenPiercings) . ".\n";
        } elseif ($name != $GLOBALS["PLAYER_NAME"]) {
            // Wearer knows about their own piercings
            $ret .= "{$name} has " . implode(", ", $hiddenPiercings) . " hidden beneath " . $their . " clothing.\n";
        }
    }
    
    // Handle plugs/vibrators grouped by visibility
    $visiblePlugs = $categories["plugs"]["visible"];
    $hiddenPlugs = $categories["plugs"]["hidden"];
    
    // For non-narrator views, all plugs should be hidden
    if (!$isNarrator) {
        $hiddenPlugs = array_merge($visiblePlugs, $hiddenPlugs);
        $visiblePlugs = [];
    }
    
    if (!empty($visiblePlugs)) {
        $ret .= "{$name} has " . implode(" and ", $visiblePlugs) . " inserted.\n";
    }
    
    // Add hidden plugs based on perspective
    if (!empty($hiddenPlugs)) {
        if ($isNarrator) {
            $ret .= "Additionally, {$name} has " . implode(" and ", $hiddenPlugs) . " inserted, hidden beneath clothing.\n";
        } elseif ($name != $GLOBALS["PLAYER_NAME"]) {
            // Wearer feels their own plugs/vibrators
            $ret .= "{$name} can feel " . implode(" and ", $hiddenPlugs) . " inserted inside " . $their . " body.\n";
        }
    }
    
    // Handle other items grouped by visibility
    $visibleOther = array_merge($accessories, $categories["other"]["visible"]);
    $hiddenOther = $categories["other"]["hidden"];
    
    if (!empty($visibleOther)) {
        $ret .= "{$name} is also equipped with " . implode(", ", $visibleOther) . ".\n";
    }
    
    // Add hidden other items for narrator
    if ($isNarrator && !empty($hiddenOther)) {
        $ret .= "{$name} has " . implode(", ", $hiddenOther) . " hidden from view.\n";
    }
    
    // Handle inferred items for non-narrator perspectives observing others
    if (!$isNarrator && $name != $GLOBALS["PLAYER_NAME"]) {
        $inferredDevices = [];
        
        // Look for devices with inferred presence in the hidden category
        if (isset($deviceContext["otherDevices"]["hidden"])) {
            foreach ($deviceContext["otherDevices"]["hidden"] as $device) {
                if (isset($device['inferredPresence']) && $device['inferredPresence']) {
                    $inferredDevices[] = isset($device['inferredType']) ? $device['inferredType'] : 'hidden device';
                }
            }
        }
        
        if (!empty($inferredDevices)) {
            // Remove duplicates and format nicely
            $inferredDevices = array_unique($inferredDevices);
            
            // If we detected unexplained reactions
            if (in_array('unexplained reaction', $inferredDevices)) {
                $ret .= "You notice " . $name . " occasionally " . 
                       (count($inferredDevices) > 1 ? "flinches and shifts uncomfortably" : "shifts uncomfortably") . 
                       ", suggesting the presence of hidden devices.\n";
            }
            
            // If we detected rigid objects under clothing
            if (in_array('rigid object', $inferredDevices)) {
                $ret .= "The outline of something rigid is visible beneath " . $their . " clothing.\n";
            }
        }
    }
    
    // Handle vibrating devices status
    $hasVibratingDevices = false;
    foreach ($devices as $visibility => $deviceList) {
        foreach ($deviceList as $device) {
            if (isset($device["type"])) {
                $deviceType = strtolower($device["type"]);
                if (strpos($deviceType, "vaginal piercing") !== false ||
                    strpos($deviceType, "nipple piercing") !== false ||
                    strpos($deviceType, "vaginal plug") !== false ||
                    strpos($deviceType, "anal plug") !== false) {
                    $hasVibratingDevices = true;
                    break 2;
                }
            }
        }
    }

    if ($hasVibratingDevices) {
        if ($isVibratorActive) {
            // Handle visible vibrating devices
            if (!empty($vibratingDevices["visible"])) {
                $ret .= implode(" and ", $vibratingDevices["visible"]) . " " . 
                        (count($vibratingDevices["visible"]) > 1 ? "are" : "is") . " actively vibrating.\n";
            }
            
            // Handle hidden vibrating devices based on perspective
            if (!empty($vibratingDevices["hidden"])) {
                if ($isNarrator) {
                    $ret .= "Hidden from view, " . implode(" and ", $vibratingDevices["hidden"]) . " " . 
                            (count($vibratingDevices["hidden"]) > 1 ? "are" : "is") . " also vibrating, stimulating their body.\n";
                } elseif ($name != $GLOBALS["PLAYER_NAME"]) {
                    // Wearer can feel their own vibrating devices
                    $ret .= "{$name} can feel " . implode(" and ", $vibratingDevices["hidden"]) . " vibrating beneath " . 
                            $their . " clothing.\n";
                } else {
                    // For others observing, add subtle hints about vibrating devices
                    $ret .= $name . " occasionally " . 
                           (count($vibratingDevices["hidden"]) > 1 ? 
                            "shudders and squirms" : "shudders slightly") . 
                           ", suggesting the presence of something hidden that is stimulating their body.\n";
                }
            }
        } else {
            // Devices present but not active
            if ($isNarrator) {
                $ret .= "The vibrating devices are currently inactive.\n";
            } elseif ($name != $GLOBALS["PLAYER_NAME"]) {
                $ret .= "{$name} can feel " . $their . " vibrating devices, though they are currently inactive.\n";
            }
        }
    }
    
    if ($ret != "")
      $ret .= "\n";
    return $ret;
}
  
  