<?php
// Function to get erotic device descriptions
function GetEroticDeviceDescription($deviceType, $bodyArea, $category) {
    // Mapping of basic device types to their erotic descriptions
    $descriptions = [
        // Piercings
        "nipple piercings" => "metal rings inserted through the nipples with embedded soulgem fragments",
        "clitoral ring" => "small metal ring pierced through the clitoris with a soulgem fragment",
        "clitoral piercing" => "small metal piercing through the clitoris with a soulgem fragment",
        "labia piercings" => "metal jewelry pierced through the labia with soulgem fragments",
        "navel piercing" => "decorative jewelry inserted through the belly button",
        
        // Plugs and vibrators
        "vaginal plug" => "a sleek plug nestled deep inside the vagina with embedded soulgems",
        "anal plug" => "a firm plug inserted into the anus with embedded soulgems",
        
        // Restraints - Head
        "hood" => "a leather hood that completely covers the head, restricting vision, hearing, and speech",
        "mouth gag" => "a mouth gag inserted between the lips that prevents speech and keeps the jaw open",
        "panel gag" => "a panel gag that covers and seals the mouth, preventing speech",
        "large gag" => "a large ball gag that forces the mouth wide open, preventing speech and causing drooling",
        "blindfold" => "a blindfold that completely blocks vision, preventing the wearer from seeing",
        
        // Restraints - Arms and upper body
        "arm cuffs" => "decorative arm cuffs worn around the wrists with rings for potential attachment of bindings",
        "armbinder" => "a leather armbinder that binds both arms together behind the back from wrists to shoulders",
        "restraining yoke" => "a metal restraining yoke that locks around the neck with attachments that secure the wrists away from the body",
        "elbow tie" => "an elbow tie made of ropes or straps that bind the elbows together behind the back",
        "locking gloves" => "gloves that are locked onto the hands",
        
        // Restraints - Lower body
        "leg cuffs" => "decorative leg cuffs worn around the ankles with rings for potential attachment of bindings",
        "ankle shackles" => "heavy metal ankle shackles connected by a chain between the ankles, limiting the wearer's stride",
        "hobble skirt" => "a tight hobble skirt that keeps the legs close together, allowing only small steps",
        
        // Chastity devices
        "chastity belt" => "a locked metal chastity belt covering the genital area with shields that prevent access or stimulation",
        "chastity bra" => "a locked metal chastity bra covering the breasts that prevents them from being touched or stimulated",
        
        // Full body restraints
        "strait jacket" => "a canvas strait jacket that binds the arms against the body, completely restricting upper body movement",
        "harness" => "a leather body harness with straps that wrap around the torso with rings for attaching other restraints",
        "bodysuit" => "a tight-fitting latex bodysuit that covers the entire body",
        "corset" => "a rigid, tightly-laced corset that compresses the waist, shapes the torso, and restricts breathing",
        
        // Clothing - Tops
        "bra" => "a supportive bra that lifts and covers the breasts",
        
        // Clothing - Bottoms
        "thong" => "a thong with a narrow band at the back that leaves the buttocks exposed",
        "panties" => "silk panties that cover the genital area and buttocks",
        "pants" => "form-fitting pants that cover the lower body from waist to ankles",
        "pelvic curtain" => "a pelvic curtain made of hanging fabric that covers only the front of the pelvis",
        "full skirt" => "a long, flowing skirt that covers the legs to below the knees",
        "mini-skirt" => "an extremely short mini-skirt that barely covers the buttocks",
        "hot pants" => "very short, tight hot pants that leave most of the legs bare",
        
        // Full outfits
        "body harness" => "a decorative body harness with straps that wrap around the torso in patterns without providing actual coverage",
        "bikini armor" => "bikini-style armor pieces that cover only the breasts and genital area",
        "revealing attire" => "revealing clothing intentionally designed with openings that expose significant portions of the body",
        "form-fitting outfit" => "an extremely form-fitting outfit that shows the exact shape of the body underneath",
        "transparent outfit" => "a transparent outfit made of see-through material that provides the illusion of coverage while revealing what's underneath",
        "leotard" => "a one-piece leotard that covers the torso with high-cut leg openings",
        
        // Accessories
        "collar" => "a lockable collar worn tightly around the neck, with a ring for attaching a leash",
        "high heels" => "high-heeled shoes with tall, narrow heels that force the wearer to walk on the balls of their feet"
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
    $hasChastityBelt = HasKeyword($name, "zad_DeviousBelt");
    $hasChastityBra = HasKeyword($name, "zad_DeviousBra");
    $hasNipplePiercing = HasKeyword($name, "zad_DeviousPiercingsNipple") || HasKeyword($name, "SLA_PiercingNipple");
    $hasVaginalPiercing = HasKeyword($name, "zad_DeviousPiercingsVaginal") || HasKeyword($name, "SLA_PiercingClit");
    $hasHarness = HasKeyword($name, "zad_DeviousHarness");
    $hasGag = HasKeyword($name, "zad_DeviousGag");
    $hasVaginalPlug = HasKeyword($name, "zad_DeviousPlugVaginal");
    $hasAnalPlug = HasKeyword($name, "zad_DeviousPlugAnal");

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
        if ($isRestraint) {
            // These restraints significantly limit movement
            $limitsMovement = strpos($deviceType, "armbinder") !== false || 
                               strpos($deviceType, "yoke") !== false || 
                               strpos($deviceType, "hobble") !== false ||
                               strpos($deviceType, "strait") !== false ||
                               strpos($deviceType, "pet suit") !== false;
                               
            $device["limitsMovement"] = $limitsMovement;
            
            // Update restrained areas
            if (isset($restrainedAreas[$bodyArea])) {
                $restrainedAreas[$bodyArea] = true;
            }
        }
        
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
    if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
        $addToLayer("nipple piercings", true, false, false, 0);
    } elseif (!$vibratingOnly && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingNipple")) {
        $addToLayer("nipple piercings", true, false, false, 0);
    }
    
    if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
        $addToLayer("clitoral ring", false, true, false, 0);
    } elseif (!$vibratingOnly && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingClit")) {
        $addToLayer("clitoral piercing", false, true, false, 0);
    }
    
    if (!$vibratingOnly && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingVulva")) {
        $addToLayer("labia piercings", false, true, false, 0);
    }
    
    if (!$vibratingOnly && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingBelly")) {
        $addToLayer("navel piercing", false, false, false, 0);
    }
    
    if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
        $addToLayer("vaginal plug", false, true, false, 0);
    }
    
    if (HasKeyword($name, "zad_DeviousPlugAnal")) {
        $addToLayer("anal plug", false, true, false, 0);
    }
    
    // Layer 1: Underwear layer
    if (!$vibratingOnly) {
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Brabikini")) {
            $addToLayer("bra", true, false, false, 1);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Thong")) {
            $addToLayer("thong", false, true, false, 1);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantiesNormal")) {
            $addToLayer("panties", false, true, false, 1);
        }
    }
    
    // Chastity items (restraints but on underwear layer)
    if (HasKeyword($name, "zad_DeviousBelt")) {
        $addToLayer("chastity belt", false, true, false, 1);
    }
    
    if (HasKeyword($name, "zad_DeviousBra")) {
        $addToLayer("chastity bra", true, false, false, 1);
    }
    
    if (HasKeyword($name, "zad_DeviousCorset")) {
        $addToLayer("corset", false, false, false, 1);
    }
    
    if (HasKeyword($name, "zad_DeviousHarness")) {
        $addToLayer("harness", false, false, false, 1);
    }
    
    // Layer 2: Main clothing layer
    if (!$vibratingOnly) {
        // Bottom clothing
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantsNormal")) {
            $addToLayer("pants", false, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PelvicCurtain")) {
            $addToLayer("pelvic curtain", false, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_FullSkirt")) {
            $addToLayer("full skirt", false, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MiniSkirt")) {
            $addToLayer("mini-skirt", false, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MicroHotPants")) {
            $addToLayer("hot pants", false, true, false, 2);
        }
        
        // Full body outfits
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHarness")) {
            $addToLayer("body harness", false, false, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_HalfNakedBikini")) {
            $addToLayer("bikini armor", true, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHalfNaked")) {
            $addToLayer("revealing attire", true, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorSpendex")) {
            $addToLayer("form-fitting outfit", true, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorTransparent")) {
            $addToLayer("transparent outfit", true, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorLewdLeotard")) {
            $addToLayer("leotard", true, true, false, 2);
        }
        
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorRubber")) {
            $addToLayer("form-fitting outfit", true, true, false, 2);
        }
        
        // Accessories
        if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Heels")) {
            $addToLayer("high heels", false, false, false, 2);
        }
    }
    
    // Restraints (mostly layer 2)
    if (!$vibratingOnly) {
        // Head restraints
        if (HasKeyword($name, "zad_DeviousHood")) {
            $addToLayer("hood", false, false, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousGag")) {
            $addToLayer("mouth gag", false, false, false, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousGagPanel")) {
            $addToLayer("panel gag", false, false, false, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousGagLarge")) {
            $addToLayer("large gag", false, false, false, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousBlindfold")) {
            $addToLayer("blindfold", false, false, true, 2);
        }
        
        // Upper body restraints
        if (HasKeyword($name, "zad_DeviousArmCuffs")) {
            $addToLayer("arm cuffs", false, false, false, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousArmbinder")) {
            $addToLayer("armbinder", false, false, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousYoke")) {
            $addToLayer("restraining yoke", false, false, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousElbowTie")) {
            $addToLayer("elbow tie", false, false, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousGloves")) {
            $addToLayer("locking gloves", false, false, false, 2);
        }
        
        // Lower body restraints
        if (HasKeyword($name, "zad_DeviousLegCuffs")) {
            $addToLayer("leg cuffs", false, false, false, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousAnkleShackles")) {
            $addToLayer("ankle shackles", false, false, true, 2);
        }
        
        // Full body restraints
        if (HasKeyword($name, "zad_DeviousStraitJacket")) {
            $addToLayer("strait jacket", false, false, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousPetSuit")) {
            $addToLayer("bodysuit", true, true, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousHobbleSkirt")) {
            $addToLayer("hobble skirt", false, true, true, 2);
        }
        
        if (HasKeyword($name, "zad_DeviousSuit")) {
            $addToLayer("bodysuit", true, true, true, 2);
        }
        
        // Collar (always visible, even with clothing)
        if (HasKeyword($name, "zad_DeviousCollar")) {
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
    if (HasKeyword($name, "SLA_HalfNakedBikini")) {
        $wearingTop = true;
    }
    if (HasKeyword($name, "SLA_ArmorHalfNaked")) {
        $wearingTop = true;
    }
    if (HasKeyword($name, "SLA_Brabikini" )) {
        $wearingTop = true;
    }
    if (HasKeyword($name, "SLA_Thong")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "SLA_PantiesNormal")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "SLA_PantsNormal")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "SLA_MicroHotPants")) {
        $wearingBottom = true;
    }
    
    if (HasKeyword($name, "SLA_ArmorTransparent")) {
        $wearingBottom = false;
        $wearingTop = false;
    }
    if (HasKeyword($name, "SLA_ArmorLewdLeotard")) {
        $wearingBottom = true;
        $wearingTop = true;
    }
    if (HasKeyword($name, "SLA_PelvicCurtain")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "SLA_FullSkirt")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "SLA_MiniSkirt")) {
        $wearingBottom = true;
    }
    if (HasKeyword($name, "EroticArmor")) {
        $wearingBottom = true;
        $wearingTop = true;
    }
    //error_log("DEBUG Actor: $name, wearingTop: $wearingTop, wearingBottom: $wearingBottom");
    return ["wearingTop" => $wearingTop, "wearingBottom" => $wearingBottom];
}


Function HasKeywordAndNotSkip($name, $eqContext, $keyword) {
    return HasKeyword($name, $keyword) && !IsSkipKeyword($keyword, $eqContext["skipKeywords"]);
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
        // Check if the actor has this keyword
        if (HasKeyword($name, $keyword)) {
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
        if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
            $sources[] = "nipple piercings";
            $accessibilityInfo["nipple piercings"] = $hasChastityBra ? "inaccessible behind the chastity bra" : "accessible";
        }
        if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
            $sources[] = "clitoral ring";
            $accessibilityInfo["clitoral ring"] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
        }
        if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
            $sources[] = "vaginal plug";
            $accessibilityInfo["vaginal plug"] = $hasChastityBelt ? "inaccessible behind the chastity belt" : "accessible";
        }
        if (HasKeyword($name, "zad_DeviousPlugAnal")) {
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