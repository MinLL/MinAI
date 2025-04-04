<?php
// Static cache for equipment data to avoid repeated parsing
$GLOBALS['equipment_cache'] = array();

// Add a helper function to check for keywords in equipment data
function HasEquipmentKeyword($actorName, $keyword) {
    // Use cached equipment data if available
    if (!isset($GLOBALS['equipment_cache'][$actorName])) {
        // Get the equipment data for this actor
        $encodedString = GetActorValue($actorName, "AllWornEquipment");
        
        // If empty or not available, fall back to the old method
        if (empty($encodedString)) {
            $GLOBALS['equipment_cache'][$actorName] = false;
            return HasKeyword($actorName, $keyword);
        }
        
        try {
            $parsedEquipment = ParseEncodedEquipmentData($encodedString);
            
            // Build a simple lookup table for fast keyword checking
            $keywordLookup = array();
            foreach ($parsedEquipment as $equipment) {
                if (isset($equipment['keywords']) && is_array($equipment['keywords'])) {
                    foreach ($equipment['keywords'] as $kw) {
                        $keywordLookup[strtolower($kw)] = true;
                    }
                }
            }
            
            // Cache both the full parsed data and the keyword lookup
            $GLOBALS['equipment_cache'][$actorName] = [
                'parsed' => $parsedEquipment,
                'keywordLookup' => $keywordLookup
            ];
        } catch (Exception $e) {
            // If there's an error parsing, fall back to the old method
            minai_log("info", "Error checking equipment keyword: " . $e->getMessage());
            $GLOBALS['equipment_cache'][$actorName] = false;
            return HasKeyword($actorName, $keyword);
        }
    }
    
    // Use cached data for keyword lookup
    if ($GLOBALS['equipment_cache'][$actorName] === false) {
        return HasKeyword($actorName, $keyword);
    }
    
    // Fast keyword lookup using the cached table
    return isset($GLOBALS['equipment_cache'][$actorName]['keywordLookup'][strtolower($keyword)]);
}

// Clear equipment cache for a specific actor or all actors
function ClearEquipmentCache($actorName = null) {
    if ($actorName === null) {
        $GLOBALS['equipment_cache'] = array();
    } else {
        unset($GLOBALS['equipment_cache'][$actorName]);
    }
}

// Function to get erotic device descriptions
function GetEroticDeviceDescription($deviceType, $bodyArea, $category) {
    // Mapping of basic device types to their erotic descriptions
    $descriptions = [
        // Piercings
        "nipple piercings" => "delicate metal rings pierced through the sensitive nipples, each adorned with a pulsing soulgem fragment that seems to throb with inner light",
        "clitoral ring" => "an exquisitely crafted metal ring pierced through the swollen clitoris, its embedded soulgem fragment sending waves of pleasure with each movement",
        "clitoral piercing" => "a delicate metal piercing through the engorged clitoris, its soulgem fragment pulsing with an inner warmth that radiates through the sensitive flesh",
        "labia piercings" => "ornate metal jewelry pierced through the sensitive labia, each piece adorned with soulgem fragments that seem to pulse in time with the wearer's heartbeat",
        "navel piercing" => "a decorative jeweled piercing that draws attention to the belly button, catching the light with each movement",
        
        // Plugs and vibrators
        "vaginal plug" => "a sleek, perfectly sized plug nestled deep within her pussy, its embedded soulgems pulsing with an inner warmth that radiates through the sensitive walls",
        "anal plug" => "a firm plug seated snugly within her tight asshole, its embedded soulgems sending waves of pleasure that ripple through the surrounding flesh",
        
        // Restraints - Head
        "hood" => "a supple leather hood that encases the head completely, its tight embrace restricting vision, hearing, and speech while heightening other sensations",
        "mouth gag" => "a mouth-filling gag that forces the jaw wide, preventing speech while allowing drool to escape in a constant stream",
        "panel gag" => "a panel gag that seals the mouth shut, its smooth surface pressing against the tongue while preventing any sound from escaping",
        "large gag" => "an oversized ball gag that stretches the mouth wide, forcing the jaw to remain open while drool drips freely from the corners",
        "blindfold" => "a soft blindfold that plunges the wearer into darkness, heightening their awareness of every touch and sound",
        
        // Restraints - Arms and upper body
        "arm cuffs" => "decorative arm cuffs that encircle the delicate wrists, their rings ready for the attachment of bindings that will restrict movement",
        "armbinder" => "a leather armbinder that pulls both arms together behind the back, forcing the chest forward while completely restricting upper body movement",
        "restraining yoke" => "a metal yoke that locks around the neck, its rigid frame extending to hold the arms out to the sides, leaving the body completely exposed",
        "elbow tie" => "a tight binding that pulls the elbows together behind the back, forcing the chest forward while restricting arm movement",
        "locking gloves" => "gloves that lock onto the hands, preventing the fingers from grasping or touching anything",
        "elbow armbinder" => "a specialized armbinder that secures the arms at the elbows, forcing them into a more restrictive position that emphasizes the curves of the body",
        "front cuffs" => "cuffs that secure the wrists in front of the body, keeping the hands visible and accessible while still restricting movement",
        "breast yoke" => "a yoke designed to frame and display the breasts while keeping the arms restrained, drawing attention to the sensitive flesh",
        "bondage mittens" => "mittens that completely encase the hands, rendering them useless while emphasizing the helplessness of the wearer",
        
        // Restraints - Lower body
        "leg cuffs" => "decorative leg cuffs that encircle the delicate ankles, their rings ready for the attachment of bindings that will restrict movement",
        "ankle shackles" => "heavy metal ankle shackles connected by a short chain, forcing the wearer to take small, mincing steps that emphasize their vulnerability",
        "hobble skirt" => "a tight hobble skirt that keeps the legs pressed together, allowing only tiny steps that emphasize the sway of the hips",
        "relaxed hobble skirt" => "a slightly less restrictive version of a hobble skirt that allows slightly more movement while still emphasizing the curves of the body",
        "shackles" => "heavy metal restraints that bind the wrists or ankles together, their weight a constant reminder of the wearer's helplessness",
        
        // Full body restraints
        "strait jacket" => "a canvas strait jacket that binds the arms tightly against the body, its multiple straps and buckles ensuring complete immobility",
        "harness" => "a leather body harness with straps that wrap sensually around the curves of the torso, its rings providing anchor points for additional restraints",
        "bodysuit" => "a tight-fitting latex bodysuit that clings to every curve, its smooth surface gleaming in the light while restricting movement",
        "corset" => "a rigid, tightly-laced corset that cinches the waist, forcing the breasts up and out while restricting breathing to shallow gasps",
        "heavy bondage" => "multiple layers of restrictive bondage that encase the body, each layer adding to the feeling of complete helplessness",
        "pony gear" => "a complete set of restraints designed to transform the wearer into a pony, including a bit that fills the mouth, a bridle that controls the head, and a harness that emphasizes the curves of the body",
        
        // Chastity devices
        "chastity belt" => "a locked metal chastity belt that covers the genital area with impenetrable shields, its smooth surface a constant reminder of denied pleasure",
        "chastity bra" => "a locked metal chastity bra that encases the breasts in a rigid cage, preventing any touch or stimulation of the sensitive flesh",
        
        // Clothing - Tops
        "bra" => "a supportive bra that lifts and frames the breasts, drawing attention to their curves",
        
        // Clothing - Bottoms
        "thong" => "a narrow strip of fabric that disappears between the cheeks, leaving the buttocks completely exposed",
        "panties" => "silk panties that cling to the curves of the body, their smooth surface catching the light",
        "pants" => "form-fitting pants that hug every curve, emphasizing the shape of the legs and hips",
        "pelvic curtain" => "a hanging fabric that covers only the front of the pelvis, swaying with each movement to reveal glimpses of what lies beneath",
        "full skirt" => "a long, flowing skirt that swishes around the legs, its fabric catching the light with each movement",
        "mini-skirt" => "an extremely short skirt that barely covers the buttocks, constantly threatening to reveal what lies beneath",
        "hot pants" => "very short, tight shorts that leave most of the legs bare, emphasizing the curves of the hips and thighs",
        
        // Full outfits
        "body harness" => "a body harness with thin leather straps that wrap sensually around the curves of the torso, drawing attention to every curve while leaving most of the skin bare",
        "bikini armor" => "bikini-style armor pieces that cover only the most intimate areas, leaving the rest of the body exposed to view",
        "revealing attire" => "clothing intentionally designed with strategic openings that expose significant portions of the body, teasing the viewer with glimpses of bare skin",
        "form-fitting outfit" => "an extremely form-fitting outfit that clings to every curve, leaving nothing to the imagination",
        "transparent outfit" => "a see-through garment that provides the illusion of coverage while revealing every detail of the body beneath",
        "leotard" => "a one-piece garment that hugs every curve, with high-cut leg openings that emphasize the length of the legs",
        
        // Accessories
        "slave boots" => "a pair of locked metal high-heels with a chain between the ankles, forcing the wearer to walk on the balls of their feet while emphasizing the sway of their hips",
        "collar" => "a lockable collar worn tightly around the neck, its smooth metal surface catching the light while the attached ring serves as a constant reminder of ownership",
        "high heels" => "tall, narrow heels that force the wearer to walk on the balls of their feet, emphasizing the sway of their hips with each step",
        
        // Permissions
        "oral permission" => "Her gag allows her to take a cock in her mouth.",
        "anal permission" => "Her chastity belt leaves her ass available for use.",
        // "vaginal permission" => "Her pussy is open and ready for use."
    ];
    
    // Return the description if available, otherwise return the basic device type
    if (isset($descriptions[$deviceType])) {
        return $descriptions[$deviceType];
    }
    
    // Generate a generic description based on category and body area
    if ($category == "restraint") {
        return "a device that restricts movement of the " . $bodyArea;
    } elseif ($category == "piercing") {
        return "a piercing in the " . $bodyArea;
    } elseif ($category == "vibrator") {
        return "a vibrating device for the " . $bodyArea;
    } elseif ($category == "clothing") {
        return "a garment covering the " . $bodyArea;
    }
    
    return $deviceType;
}

Function GetDevicesContext($name, $vibratingOnly = false) {
    $revealed = GetRevealedStatus($name);
    $wearingTop = $revealed["wearingTop"];
    $wearingBottom = $revealed["wearingBottom"];
    $eqContext = $vibratingOnly ? [] : GetAllEquipmentContext($name);
    $cuirass = $vibratingOnly ? "" : GetActorValue($name, "cuirass", false, true);
    $helmet = $vibratingOnly ? "" : GetActorValue($name, "helmet", false, true);
    $gloves = $vibratingOnly ? "" : GetActorValue($name, "gloves", false, true);
    $boots = $vibratingOnly ? "" : GetActorValue($name, "boots", false, true);
    
    // For vibrating devices, we need arousal information
    $arousal = $vibratingOnly ? GetActorArousal($name) : 0;
    
    // Use the new function to check for equipment keywords
    $hasChastityBelt = HasEquipmentKeyword($name, "zad_DeviousBelt");
    $hasChastityBra = HasEquipmentKeyword($name, "zad_DeviousBra");
    $hasNipplePiercing = HasEquipmentKeyword($name, "zad_DeviousPiercingsNipple") || HasEquipmentKeyword($name, "SLA_PiercingNipple");
    $hasVaginalPiercing = HasEquipmentKeyword($name, "zad_DeviousPiercingsVaginal") || HasEquipmentKeyword($name, "SLA_PiercingClit");
    $hasHarness = HasEquipmentKeyword($name, "zad_DeviousHarness");
    $hasGag = HasEquipmentKeyword($name, "zad_DeviousGag");
    $hasVaginalPlug = HasEquipmentKeyword($name, "zad_DeviousPlugVaginal");
    $hasAnalPlug = HasEquipmentKeyword($name, "zad_DeviousPlugAnal");

    // Get the correct pronouns for this actor
    $pronouns = GetActorPronouns($name);
    $their = $pronouns["possessive"];
    $them = $pronouns["object"];
    $they = $pronouns["subject"];
    
    // Create structured data arrays for different observer perspectives
    $narratorDevices = [
        "visible" => [],
        "hidden" => []
    ];
    $wearerDevices = [
        "visible" => [],
        "hidden" => []
    ];
    $otherDevices = [
        "visible" => [],
        "hidden" => []
    ];
    
    // Keep track of device data by category and body area for consistent organization
    $devicesByCategory = [
        "restraint" => [],
        "clothing" => [],
        "vibrator" => [],
        "piercing" => [],
        "other" => []
    ];
    
    $constraintDevices = []; // All devices that constrain or restrict
    $movementLimitingRestraints = []; // Restraints that specifically limit movement
    
    // Keep track of what body areas are restrained (only needed for non-vibrating mode)
    $restrainedAreas = [
        "head" => false,
        "arms" => false,
        "hands" => false,
        "chest" => false,
        "waist" => false,
        "groin" => false,
        "legs" => false,
        "feet" => false
    ];
    
    // Determine arousal description for vibrating mode
    $arousalDesc = "";
    if ($vibratingOnly) {
        if ($arousal > 80) {
            $arousalDesc = "highly aroused";
        } elseif ($arousal > 60) {
            $arousalDesc = "aroused";
        } elseif ($arousal > 40) {
            $arousalDesc = "somewhat aroused";
        } elseif ($arousal > 20) {
            $arousalDesc = "mildly aroused";
        } else {
            $arousalDesc = "not very aroused";
        }
    }
    
    // Temporary storage for devices by layer to ensure correct processing order
    $devicesByLayer = [
        0 => [], // Body layer (piercings, plugs)
        1 => [], // Underwear layer
        2 => [], // Main clothing layer
        3 => []  // Outerwear layer
    ];
    
    // Helper function to add a device to the layer-based storage
    $addToLayer = function($deviceType, $coversTop = false, $coversBottom = false, $isRestraint = false, $layer = 2) 
        use (&$devicesByLayer, &$devicesByCategory, &$restrainedAreas, $name) {
        
        // Create device data structure
        $device = [
            "type" => $deviceType,
            "coversTop" => $coversTop,
            "coversBottom" => $coversBottom,
            "isRestraint" => $isRestraint,
            "layer" => $layer
        ];
        
        // Additional attributes for categorization
        $bodyArea = "";
        if ($coversTop && !$coversBottom) {
            $bodyArea = "chest";
            $category = $isRestraint ? "restraint" : "clothing";
        } elseif (!$coversTop && $coversBottom) {
            $bodyArea = "groin";
            $category = $isRestraint ? "restraint" : "clothing";
        } elseif ($coversTop && $coversBottom) {
            $bodyArea = "body";
            $category = $isRestraint ? "restraint" : "clothing";
        } else {
            // Try to infer from the device type
            if (strpos($deviceType, "gag") !== false || strpos($deviceType, "hood") !== false || 
                strpos($deviceType, "blindfold") !== false) {
                $bodyArea = "head";
                $category = "restraint";
            } elseif (strpos($deviceType, "cuff") !== false && strpos($deviceType, "arm") !== false || 
                    strpos($deviceType, "armbinder") !== false || strpos($deviceType, "yoke") !== false ||
                    strpos($deviceType, "elbow") !== false) {
                $bodyArea = "arms";
                $category = "restraint";
            } elseif (strpos($deviceType, "glove") !== false) {
                $bodyArea = "hands";
                $category = "restraint";
            } elseif (strpos($deviceType, "collar") !== false) {
                $bodyArea = "neck";
                $category = "restraint";
            } elseif (strpos($deviceType, "leg") !== false || strpos($deviceType, "ankle") !== false ||
                    strpos($deviceType, "hobble") !== false) {
                $bodyArea = "legs";
                $category = "restraint";
            } elseif (strpos($deviceType, "piercing") !== false) {
                $category = "piercing";
                if (strpos($deviceType, "nipple") !== false) {
                    $bodyArea = "chest";
                } elseif (strpos($deviceType, "clit") !== false || strpos($deviceType, "labia") !== false || 
                        strpos($deviceType, "vaginal") !== false) {
                    $bodyArea = "groin";
                } elseif (strpos($deviceType, "navel") !== false || strpos($deviceType, "belly") !== false) {
                    $bodyArea = "waist";
                }
            } elseif (strpos($deviceType, "plug") !== false || strpos($deviceType, "vibrator") !== false) {
                $category = "vibrator";
                if (strpos($deviceType, "anal") !== false) {
                    $bodyArea = "anus";
                } elseif (strpos($deviceType, "vaginal") !== false) {
                    $bodyArea = "groin";
                }
            } else {
                $bodyArea = "body";
                $category = "other";
            }
        }
        
        $device["bodyArea"] = $bodyArea;
        $device["category"] = $category;
        
        // Track movement limitation for restraints
        //if ($isRestraint) {
            // These restraints significantly limit movement
            $limitsMovement = strpos($deviceType, "armbinder") !== false || 
                               strpos($deviceType, "yoke") !== false || 
                               strpos($deviceType, "hobble") !== false ||
                               strpos($deviceType, "strait") !== false ||
                               strpos($deviceType, "pet suit") !== false ||
                               strpos($deviceType, "elbow tie") !== false ||
                               strpos($deviceType, "elbow armbinder") !== false ||
                               strpos($deviceType, "heavy bondage") !== false ||
                               strpos($deviceType, "pony gear") !== false ||
                               strpos($deviceType, "ankle shackles") !== false ||
                               strpos($deviceType, "slave boots") !== false ||
                               strpos($deviceType, "bondage mittens") !== false ||
                               strpos($deviceType, "hood") !== false ||
                               strpos($deviceType, "front cuffs") !== false;
                               
            $device["limitsMovement"] = $limitsMovement;
            
            // Update restrained areas
            if (isset($restrainedAreas[$bodyArea])) {
                $restrainedAreas[$bodyArea] = true;
            }
        //}
        
        // Get an erotic description for this device
        $device["description"] = GetEroticDeviceDescription($deviceType, $bodyArea, $category);
        
        // Add to layer-based array
        $devicesByLayer[$layer][] = $device;
    };
    
    // Helper function to add a device with consistent logic - now processes and adds to result arrays
    $addDevice = function($device) 
        use (&$narratorDevices, &$wearerDevices, &$otherDevices, &$constraintDevices, 
             &$movementLimitingRestraints, &$devicesByCategory,
             $wearingTop, $wearingBottom, $hasChastityBelt, $hasChastityBra, $hasHarness, $name) {
        
        // Extract key properties
        $deviceType = $device["type"];
        $coversTop = $device["coversTop"];
        $coversBottom = $device["coversBottom"];
        $isRestraint = $device["isRestraint"];
        $layer = $device["layer"];
        $bodyArea = $device["bodyArea"];
        $category = $device["category"];
        $description = isset($device["description"]) ? $device["description"] : $deviceType;
        
        // Create a display name that includes both the type and description
        $device["displayName"] = $deviceType;
        $device["fullDescription"] = $description;
        
        // Determine if this device limits movement
        if ($isRestraint && isset($device["limitsMovement"]) && $device["limitsMovement"]) {
            $movementLimitingRestraints[] = $deviceType;
        }
        
        // Determine visibility based on higher layer coverage
        $isHiddenTop = false;
        $isHiddenBottom = false;
        
        // Check if higher layer items are covering this device
        foreach ($narratorDevices["visible"] as $existingDevice) {
            if (isset($existingDevice["layer"]) && $existingDevice["layer"] > $layer) {
                // If existing device is a higher layer and covers the same area
                if ($existingDevice["coversTop"] && $coversTop) {
                    $isHiddenTop = true;
                }
                if ($existingDevice["coversBottom"] && $coversBottom) {
                    $isHiddenBottom = true;
                }
            }
        }
        
        // Also check basic clothing state for layers 0 and 1
        if ($layer < 2) {
            if ($coversTop && $wearingTop) {
                $isHiddenTop = true;
            }
            if ($coversBottom && $wearingBottom) {
                $isHiddenBottom = true;
            }
        }
        
        // Device is hidden if all its coverage areas are hidden
        $isHidden = false;
        if ($coversTop && $coversBottom) {
            $isHidden = $isHiddenTop && $isHiddenBottom;
        } elseif ($coversTop) {
            $isHidden = $isHiddenTop;
        } elseif ($coversBottom) {
            $isHidden = $isHiddenBottom;
        } else if ($bodyArea == "chest" && $wearingTop) {
            $isHidden = true;
        } else if (($bodyArea == "groin" || $bodyArea == "anus") && $wearingBottom) {
            $isHidden = true;
        }
        
        // Special cases for piercings visibility
        if ($category == "piercing") {
            if (($bodyArea == "chest" && $wearingTop) || 
                ($bodyArea == "groin" && $wearingBottom) || 
                ($bodyArea == "waist" && ($wearingTop || $wearingBottom))) {
                $isHidden = true;
            }
        }
        
        // Special cases for plugs/vibrators visibility
        if ($category == "vibrator") {
            $isHidden = true; // Always hidden unless explicitly exposed
        }
        
        // Restraints are always visible/inferred for self
        $isHiddenForOthers = $isHidden;
        $isHiddenForSelf = $isHidden && !$isRestraint;
        
        // Add to the appropriate arrays based on visibility
        if ($isHidden) {
            $device["hidden"] = true;
            $narratorDevices["hidden"][] = $device;
            
            if ($isHiddenForSelf) {
                $wearerDevices["hidden"][] = $device;
            } else {
                $wearerDevices["visible"][] = $device;
            }
            
            if ($isHiddenForOthers) {
                // For other observers, add inference cues for certain hidden devices
                if ($category == "vibrator" || $isRestraint) {
                    $inferredDevice = [
                        "type" => "unknown",
                        "category" => $category,
                        "isHidden" => true,
                        "inferredPresence" => true,
                        "inferredType" => $isRestraint ? "rigid object" : "unexplained reaction"
                    ];
                    $otherDevices["hidden"][] = $inferredDevice;
                } else {
                    $otherDevices["hidden"][] = $device;
                }
            } else {
                $otherDevices["visible"][] = $device;
            }
        } else {
            $device["hidden"] = false;
            $narratorDevices["visible"][] = $device;
            $wearerDevices["visible"][] = $device;
            $otherDevices["visible"][] = $device;
        }
        
        // If this is a restraint, add to constraint devices
        if ($isRestraint) {
            $constraintDevices[] = $deviceType;
        }
        
        // Add to appropriate category
        if (isset($devicesByCategory[$category])) {
            $devicesByCategory[$category][] = $device;
        } else {
            $devicesByCategory["other"][] = $device;
        }
    };
    
    // ==== PROCESS IN LAYER ORDER ====
    
    // Layer 0: Body layer devices (piercings, plugs directly on skin)
    // These would typically be the first items added and visibility determined
    if (HasEquipmentKeyword($name, "zad_DeviousPiercingsNipple")) {
        $addToLayer("nipple piercings", true, false, false, 0);
    } elseif (!$vibratingOnly && HasEquipmentKeyword($name, "SLA_PiercingNipple")) {
        $addToLayer("nipple piercings", true, false, false, 0);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousPiercingsVaginal")) {
        $addToLayer("clitoral ring", false, true, false, 0);
    } elseif (!$vibratingOnly && HasEquipmentKeyword($name, "SLA_PiercingClit")) {
        $addToLayer("clitoral piercing", false, true, false, 0);
    }
    
    if (!$vibratingOnly && HasEquipmentKeyword($name, "SLA_PiercingVulva")) {
        $addToLayer("labia piercings", false, true, false, 0);
    }
    
    if (!$vibratingOnly && HasEquipmentKeyword($name, "SLA_PiercingBelly")) {
        $addToLayer("navel piercing", false, false, false, 0);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousPlugVaginal")) {
        $addToLayer("vaginal plug", false, true, false, 0);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousPlugAnal")) {
        $addToLayer("anal plug", false, true, false, 0);
    }
    
    // Layer 1: Underwear layer
    if (!$vibratingOnly) {
        if (HasEquipmentKeyword($name, "SLA_Brabikini")) {
            $addToLayer("bra", true, false, false, 1);
        }
        
        if (HasEquipmentKeyword($name, "SLA_Thong")) {
            $addToLayer("thong", false, true, false, 1);
        }
        
        if (HasEquipmentKeyword($name, "SLA_PantiesNormal")) {
            $addToLayer("panties", false, true, false, 1);
        }
    }
    
    // Chastity items (restraints but on underwear layer)
    if (HasEquipmentKeyword($name, "zad_DeviousBelt")) {
        $addToLayer("chastity belt", false, true, false, 1);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousBra")) {
        $addToLayer("chastity bra", true, false, false, 1);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousCorset")) {
        $addToLayer("corset", false, false, false, 1);
    }
    
    if (HasEquipmentKeyword($name, "zad_DeviousHarness")) {
        $addToLayer("harness", false, false, false, 1);
    }
    
    // Layer 2: Main clothing layer
    if (!$vibratingOnly) {
        // Bottom clothing
        if (HasEquipmentKeyword($name, "SLA_PantsNormal")) {
            $addToLayer("pants", false, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_PelvicCurtain")) {
            $addToLayer("pelvic curtain", false, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_FullSkirt")) {
            $addToLayer("full skirt", false, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_MiniSkirt")) {
            $addToLayer("mini-skirt", false, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_MicroHotPants")) {
            $addToLayer("hot pants", false, true, false, 2);
        }
        
        // Full body outfits
        if (HasEquipmentKeyword($name, "SLA_ArmorHarness")) {
            $addToLayer("body harness", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_HalfNakedBikini")) {
            $addToLayer("bikini armor", true, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_ArmorHalfNaked")) {
            $addToLayer("revealing attire", true, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_ArmorSpendex")) {
            $addToLayer("form-fitting outfit", true, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_ArmorTransparent")) {
            $addToLayer("transparent outfit", true, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_ArmorLewdLeotard")) {
            $addToLayer("leotard", true, true, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "SLA_ArmorRubber")) {
            $addToLayer("form-fitting outfit", true, true, false, 2);
        }
        
        // Accessories
        if (HasEquipmentKeyword($name, "SLA_Heels")) {
            $addToLayer("high heels", false, false, false, 2);
        }
    }
    
    // Restraints (mostly layer 2)
    if (!$vibratingOnly) {
        // Head restraints
        if (HasEquipmentKeyword($name, "zad_DeviousHood")) {
            $addToLayer("hood", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousGag")) {
            $addToLayer("mouth gag", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousGagPanel")) {
            $addToLayer("panel gag", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousGagLarge")) {
            $addToLayer("large gag", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousBlindfold")) {
            $addToLayer("blindfold", false, false, true, 2);
        }
        
        // Upper body restraints
        if (HasEquipmentKeyword($name, "zad_DeviousArmCuffs")) {
            $addToLayer("arm cuffs", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousArmbinder")) {
            $addToLayer("armbinder", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousYoke")) {
            $addToLayer("restraining yoke", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousElbowTie")) {
            $addToLayer("elbow tie", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousGloves")) {
            $addToLayer("locking gloves", false, false, false, 2);
        }
        
        // Lower body restraints
        if (HasEquipmentKeyword($name, "zad_DeviousLegCuffs")) {
            $addToLayer("leg cuffs", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousAnkleShackles")) {
            $addToLayer("ankle shackles", false, false, true, 2);
        }
        
        // Full body restraints
        if (HasEquipmentKeyword($name, "zad_DeviousStraitJacket")) {
            $addToLayer("strait jacket", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousPetSuit")) {
            $addToLayer("bodysuit", true, true, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousHobbleSkirt")) {
            $addToLayer("hobble skirt", false, true, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousSuit")) {
            $addToLayer("bodysuit", true, true, true, 2);
        }

        if (HasEquipmentKeyword($name, "zad_DeviousBoots")) {
            $addToLayer("slave boots", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousArmbinderElbow")) {
            $addToLayer("elbow armbinder", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousHeavyBondage")) {
            $addToLayer("heavy bondage", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousHobbleSkirtRelaxed")) {
            $addToLayer("relaxed hobble skirt", false, true, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousCuffsFront")) {
            $addToLayer("front cuffs", false, false, false, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousYokeBB")) {
            $addToLayer("breast yoke", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousBondageMittens")) {
            $addToLayer("bondage mittens", false, false, true, 2);
        }
        
        if (HasEquipmentKeyword($name, "zad_DeviousPonyGear")) {
            $addToLayer("pony gear", false, false, true, 2);
        }
        
        // Permission keywords
        if (HasEquipmentKeyword($name, "zad_PermitOral")) {
            $addToLayer("oral permission", false, false, false, 0);
        }
        
        if (HasEquipmentKeyword($name, "zad_PermitAnal")) {
            $addToLayer("anal permission", false, false, false, 0);
        }
        
        if (HasEquipmentKeyword($name, "zad_PermitVaginal")) {
            $addToLayer("vaginal permission", false, false, false, 0);
        }
        
        // Collar (always visible, even with clothing)
        if (HasEquipmentKeyword($name, "zad_DeviousCollar")) {
            $addToLayer("collar", false, false, false, 2);
        }
    } else if ($hasGag) {
        // In vibrating-only mode, still track gags
        $addToLayer("mouth gag", false, false, false, 2);
    }
    
    // Layer 3: Outerwear (not currently used much but could be expanded)
    
    // Now process devices in order from innermost to outermost layer
    foreach ($devicesByLayer as $layer => $layerDevices) {
        foreach ($layerDevices as $device) {
            $addDevice($device);
        }
    }
    
    // Vibration handling - detect vibration faction membership even if no specific devices found
    if ($vibratingOnly && CanVibrate($name) && (IsInFaction($name, "Vibrator Effect Faction") || IsEnabled($name, "isVibratorActive"))) {
        if (empty($narratorDevices["visible"]) && empty($narratorDevices["hidden"])) {
            $defaultDevice = [
                'type' => 'vibrating device',
                'bodyArea' => 'unknown',
                'category' => 'vibrator',
                'isHidden' => true,
                'coveredBy' => ['clothing'],
                'isRestraint' => false,
                'isRestrained' => false,
                'isVibrating' => true
            ];
            
            $narratorDevices["hidden"][] = $defaultDevice;
            $wearerDevices["hidden"][] = $defaultDevice;
            $otherDevices["hidden"][] = [
                'type' => 'unknown',
                'category' => 'vibrator',
                'isHidden' => true,
                'inferredPresence' => true,
                'inferredType' => 'unexplained reaction'
            ];
        }
    }
    
    // Determine helplessness level based on movement-limiting restraints
    $helplessness = "";
    $restraintCount = count($movementLimitingRestraints);
    if ($restraintCount > 2) {
        $helplessness = "completely helpless and thoroughly restrained, unable to move freely";
    } elseif ($restraintCount > 0) {
        $helplessness = "significantly restricted in movement by $their " . implode(" and ", $movementLimitingRestraints);
    } elseif (count($constraintDevices) > 0) {
        // If they have restraints but none that limit movement
        $helplessness = "restrained by " . implode(" and ", $constraintDevices) . ", though still able to move";
    }
    
    // Prepare the data structure for return
    $returnData = [
        // Structured data for different perspectives
        "narratorDevices" => $narratorDevices,
        "wearerDevices" => $wearerDevices,
        "otherDevices" => $otherDevices,
        
        // Additional organized data
        "devicesByCategory" => $devicesByCategory,
        
        // Constraint information
        "constraintDevices" => $constraintDevices,
        "movementLimitingRestraints" => $movementLimitingRestraints,
        
        // Basic clothing info
        "wearingTop" => $wearingTop,
        "wearingBottom" => $wearingBottom,
        
        // Descriptive summary of helplessness
        "helplessness" => $helplessness,
        
        // Pronouns for convenience in formatting
        "pronouns" => $pronouns
    ];
    
    // Add vibration-specific data
    $returnData["arousal"] = $arousal;
    $returnData["arousalDesc"] = $arousalDesc;
    $returnData["hasChastityBelt"] = $hasChastityBelt;
    $returnData["hasChastityBra"] = $hasChastityBra;
    $returnData["hasHarness"] = $hasHarness;
    $returnData["hasGag"] = $hasGag;
    $returnData["hasNipplePiercing"] = $hasNipplePiercing;
    $returnData["hasVaginalPiercing"] = $hasVaginalPiercing;
    $returnData["hasAnalPlug"] = $hasAnalPlug;
    $returnData["hasVaginalPlug"] = $hasVaginalPlug;
    
    return $returnData;
}

// Legacy function that wraps the consolidated function to maintain compatibility
Function GetAllDevicesContext($name) {
    return GetDevicesContext($name, false);
}

// Legacy function that wraps the consolidated function to maintain compatibility
Function GetVibratingDevicesContext($name) {
    return GetDevicesContext($name, true);
}



Function GetRevealedStatus($name) {
    $cuirass = GetActorValue($name, "cuirass", false, true);
    
    $wearingBottom = false;
    $wearingTop = false;
    
    // if $eqContext["context"] not empty, then will set ret
    if (!empty($cuirass)) {
        $wearingTop = true;
    }
    if (HasEquipmentKeyword($name, "SLA_HalfNakedBikini")) {
        $wearingTop = true;
    }
    if (HasEquipmentKeyword($name, "SLA_ArmorHalfNaked")) {
        $wearingTop = true;
    }
    if (HasEquipmentKeyword($name, "SLA_Brabikini" )) {
        $wearingTop = true;
    }
    if (HasEquipmentKeyword($name, "SLA_Thong")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "SLA_PantiesNormal")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "SLA_PantsNormal")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "SLA_MicroHotPants")) {
        $wearingBottom = true;
    }
    
    if (HasEquipmentKeyword($name, "SLA_ArmorTransparent")) {
        $wearingBottom = false;
        $wearingTop = false;
    }
    if (HasEquipmentKeyword($name, "SLA_ArmorLewdLeotard")) {
        $wearingBottom = true;
        $wearingTop = true;
    }
    if (HasEquipmentKeyword($name, "SLA_PelvicCurtain")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "SLA_FullSkirt")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "SLA_MiniSkirt")) {
        $wearingBottom = true;
    }
    if (HasEquipmentKeyword($name, "EroticArmor")) {
        $wearingBottom = true;
        $wearingTop = true;
    }
    //error_log("DEBUG Actor: $name, wearingTop: $wearingTop, wearingBottom: $wearingBottom");
    return ["wearingTop" => $wearingTop, "wearingBottom" => $wearingBottom];
}


Function HasEquipmentKeywordAndNotSkip($name, $eqContext, $keyword) {
    return HasEquipmentKeyword($name, $keyword) && !IsSkipKeyword($keyword, $eqContext["skipKeywords"]);
}

Function GetClothingKeywordMap() {
    // Central mapping of clothing display names to their SLA keywords
    return [
        // Bottom clothing
        "hot pants" => "SLA_MicroHotPants",
        "thong" => "SLA_Thong",
        "panties" => "SLA_PantiesNormal",
        "pants" => "SLA_PantsNormal",
        "pelvic curtain" => "SLA_PelvicCurtain",
        "full skirt" => "SLA_FullSkirt",
        "mini-skirt" => "SLA_MiniSkirt",
        
        // Top clothing
        "bra" => "SLA_Brabikini",
        
        // Full body outfits
        "body harness" => "SLA_ArmorHarness",
        "bikini armor" => "SLA_HalfNakedBikini",
        "revealing attire" => "SLA_ArmorHalfNaked",
        "form-fitting outfit" => "SLA_ArmorSpendex",
        "transparent outfit" => "SLA_ArmorTransparent",
        "leotard" => "SLA_ArmorLewdLeotard",
        "rubber outfit" => "SLA_ArmorRubber"
    ];
}

// Helper function to check if the device being processed matches the keyword the actor has
Function IsDeviceMatchingSelfClothing($name, $deviceType) {
    $keywordMap = GetClothingKeywordMap();
    
    // If this device type has a corresponding keyword
    if (isset($keywordMap[$deviceType])) {
        $keyword = $keywordMap[$deviceType];
        // Check if the actor has this keyword in their equipment
        if (HasEquipmentKeyword($name, $keyword)) {
            return true;
        }
    }
    
    return false;
}

// Function to identify vibration sources for a character
Function GetVibrationSources($name) {
    $deviceContext = GetDevicesContext($name, true);
    $sources = [];
    $accessibilityInfo = [];
    
    // Get restraint info
    $hasChastityBelt = $deviceContext["hasChastityBelt"];
    $hasChastityBra = $deviceContext["hasChastityBra"];
    
    // Check for specific known vibrating devices in both visible and hidden arrays
    foreach (["visible", "hidden"] as $visibility) {
        if (isset($deviceContext["wearerDevices"][$visibility])) {
            foreach ($deviceContext["wearerDevices"][$visibility] as $device) {
                // Check for explicit vibrating devices
                if (isset($device["category"]) && $device["category"] == "vibrator") {
                    $deviceType = isset($device["type"]) ? $device["type"] : "unknown device";
                    $sources[] = $deviceType;
                    
                    // Check accessibility based on device type
                    if (strpos($deviceType, "vaginal") !== false || strpos($deviceType, "clitoral") !== false) {
                        $accessibilityInfo[$deviceType] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
                    } elseif (strpos($deviceType, "anal") !== false) {
                        $accessibilityInfo[$deviceType] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
                    } else {
                        $accessibilityInfo[$deviceType] = "accessible";
                    }
                }
                
                // Piercings can also vibrate
                if (isset($device["category"]) && $device["category"] == "piercing") {
                    $piercingType = isset($device["type"]) ? $device["type"] : "piercing";
                    $sources[] = $piercingType;
                    
                    // Check accessibility based on piercing type
                    if (strpos($piercingType, "nipple") !== false) {
                        $accessibilityInfo[$piercingType] = $hasChastityBra ? "inaccessible behind the chastity bra" : "accessible";
                    } elseif (strpos($piercingType, "clitoral") !== false || strpos($piercingType, "labia") !== false) {
                        $accessibilityInfo[$piercingType] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
                    } else {
                        $accessibilityInfo[$piercingType] = "accessible";
                    }
                }
            }
        }
    }
    
    // If no specific sources found but vibration is occurring
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

// Function to handle equipment changes
function HandleEquipmentChange($actorName) {
    // Clear the equipment cache for this actor since equipment has changed
    ClearEquipmentCache($actorName);
}

// Hook this function to events where equipment might change
// This would typically be called from game scripts when equipment changes