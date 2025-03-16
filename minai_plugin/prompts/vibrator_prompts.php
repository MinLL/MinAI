<?php

// Function to get vibrate start prompt
function get_vibrate_start_prompt() {
    $target = $GLOBALS["target"];
    
    // Better intensity extraction
    $intensity = "strongly"; // Default
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["gameRequest"][3])) {
        $message = $GLOBALS["gameRequest"][3];
        // Try to find any of the known intensity words in the message
        $intensityWords = ["weakly", "strongly", "intensely", "extremely intensely"];
        foreach ($intensityWords as $word) {
            if (stripos($message, $word) !== false) {
                $intensity = $word;
                break;
            }
        }
    }
    
    $deviceContext = GetVibratingDevicesContext($target);
    
    // Get specific vibration sources with accessibility info
    $vibrationData = GetVibrationSources($target);
    $vibrationSources = isset($vibrationData["sources"]) ? $vibrationData["sources"] : [];
    $accessibilityInfo = isset($vibrationData["accessibility"]) ? $vibrationData["accessibility"] : [];
    
    // Create a descriptive list of sources with accessibility
    $descriptiveSources = [];
    foreach ($vibrationSources as $source) {
        $accessibility = isset($accessibilityInfo[$source]) ? $accessibilityInfo[$source] : "accessible";
        
        // Only add accessibility info if it's not accessible
        if ($accessibility != "accessible") {
            $descriptiveSources[] = "$source ($accessibility)";
        } else {
            $descriptiveSources[] = $source;
        }
    }
    
    $sourcesList = !empty($descriptiveSources) ? implode(" and ", $descriptiveSources) : "hidden devices";
    
    // Device lists for different perspectives
    $narratorDeviceList = !empty($vibrationSources) ? $sourcesList : "hidden devices";
    $wearerDeviceList = !empty($vibrationSources) ? $sourcesList : "hidden devices";
    
    // Create separate lists for visible and hidden devices for the narrator perspective
    $narratorVisibleDevices = [];
    $narratorHiddenDevices = [];
    
    // Check visibility of devices to others
    $visibleToOthers = false;
    $visibleDevices = [];
    
    foreach ($vibrationSources as $source) {
        $isVisible = false;
        $isHidden = true;
        
        // Check if this device is in the otherDevices visible array
        if (isset($deviceContext["otherDevices"]["visible"])) {
            foreach ($deviceContext["otherDevices"]["visible"] as $device) {
                if (isset($device["type"]) && $device["type"] == $source) {
                    $isVisible = true;
                    $visibleDevices[] = $source;
                    break;
                }
            }
        }
        
        // Check if this device is in the narratorDevices visible array
        if (isset($deviceContext["narratorDevices"]["visible"])) {
            foreach ($deviceContext["narratorDevices"]["visible"] as $device) {
                if (isset($device["type"]) && $device["type"] == $source) {
                    $isHidden = false;
                    $narratorVisibleDevices[] = $source;
                    break;
                }
            }
        }
        
        // Add to narrator hidden devices if it's hidden
        if ($isHidden) {
            $narratorHiddenDevices[] = $source;
        }
        
        if ($isVisible) {
            $visibleToOthers = true;
        }
    }
    
    // Create descriptive device lists for narrator
    $narratorVisibleDeviceList = !empty($narratorVisibleDevices) ? implode(" and ", $narratorVisibleDevices) : "";
    $narratorHiddenDeviceList = !empty($narratorHiddenDevices) ? implode(" and ", $narratorHiddenDevices) : "";
    
    // Combine visible and hidden devices for narrator with appropriate description
    $narratorDeviceDescription = "";
    if (!empty($narratorVisibleDeviceList) && !empty($narratorHiddenDeviceList)) {
        $narratorDeviceDescription = $narratorVisibleDeviceList . " and the hidden " . $narratorHiddenDeviceList;
    } elseif (!empty($narratorVisibleDeviceList)) {
        $narratorDeviceDescription = $narratorVisibleDeviceList;
    } elseif (!empty($narratorHiddenDeviceList)) {
        $narratorDeviceDescription = "hidden " . $narratorHiddenDeviceList;
    } else {
        $narratorDeviceDescription = "hidden devices";
    }
    
    $otherDeviceList = !empty($visibleDevices) ? implode(" and ", $visibleDevices) : "hidden devices";
    
    // Get additional context from device data
    $arousalDesc = isset($deviceContext["arousalDesc"]) ? $deviceContext["arousalDesc"] : "";
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    $hasChastityBelt = isset($deviceContext["hasChastityBelt"]) ? $deviceContext["hasChastityBelt"] : false;
    $hasChastityBra = isset($deviceContext["hasChastityBra"]) ? $deviceContext["hasChastityBra"] : false;
    $wearingTop = isset($deviceContext["wearingTop"]) ? $deviceContext["wearingTop"] : false;
    $wearingBottom = isset($deviceContext["wearingBottom"]) ? $deviceContext["wearingBottom"] : false;
    
    // Random variation words
    $intensityWords = ["suddenly", "abruptly", "unexpectedly", "without warning"];
    $intensityWord = $intensityWords[array_rand($intensityWords)];
    
    // Adjust reaction words based on intensity and gag
    $reactionWords = [];
    if ($hasGag) {
        if ($intensity == "weakly") {
            $reactionWords = ["lets out a muffled sound", "makes a soft sound behind the gag", "twitches slightly", "blinks in surprise"];
        } elseif ($intensity == "strongly") {
            $reactionWords = ["mmmphs behind the gag", "makes muffled sounds", "shudders", "stiffens with a stifled moan"];
        } elseif ($intensity == "intensely") {
            $reactionWords = ["emits muffled moans", "trembles with gagged sounds", "jerks in surprise", "eyes widen with a stifled groan"];
        } else { // extremely intensely
            $reactionWords = ["makes loud muffled cries", "moans loudly despite the gag", "nearly buckles with a gagged cry", "writhes with muffled sounds"];
        }
    } else {
        if ($intensity == "weakly") {
            $reactionWords = ["notices", "twitches slightly", "blinks in surprise", "raises an eyebrow"];
        } elseif ($intensity == "strongly") {
            $reactionWords = ["gasps", "moans softly", "shudders", "stiffens"];
        } elseif ($intensity == "intensely") {
            $reactionWords = ["moans audibly", "trembles", "jerks in surprise", "eyes widen"];
        } else { // extremely intensely
            $reactionWords = ["cries out", "moans loudly", "nearly buckles at the knees", "gasps loudly"];
        }
    }
    $reactionWord = $reactionWords[array_rand($reactionWords)];
    
    // Helplessness addition if applicable
    $helplessnessAddition = "";
    if (!empty($helplessness)) {
        $helplessnessAddition = ", " . $helplessness;
    }
    
    // Create specific body part reactions and add accessibility context
    $bodyPartReaction = "";
    $accessibilityContext = "";
    
    if (!empty($vibrationSources)) {
        // Track which body areas are affected for combined description
        $affectedAreas = [
            "chest" => false,
            "groin" => false,
            "rear" => false
        ];
        
        // Track which areas have inaccessible devices
        $inaccessibleAreas = [];
        
        foreach ($vibrationSources as $source) {
            // Determine body area affected
            if (strpos($source, "nipple") !== false) {
                $affectedAreas["chest"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["chest"] = $accessibilityInfo[$source];
                }
            } 
            elseif (strpos($source, "clitoral") !== false || strpos($source, "vaginal") !== false || strpos($source, "labia") !== false) {
                $affectedAreas["groin"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["groin"] = $accessibilityInfo[$source];
                }
            } 
            elseif (strpos($source, "anal") !== false) {
                $affectedAreas["rear"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["rear"] = $accessibilityInfo[$source];
                }
            }
        }
        
        // Construct body part reaction string
        if ($affectedAreas["chest"]) {
            $bodyPartReaction .= "their chest ";
        }
        if ($affectedAreas["groin"]) {
            if (!empty($bodyPartReaction)) {
                $bodyPartReaction .= "and ";
            }
            $bodyPartReaction .= "their most intimate areas ";
        }
        if ($affectedAreas["rear"]) {
            if (!empty($bodyPartReaction)) {
                $bodyPartReaction .= "and ";
            }
            $bodyPartReaction .= "their rear passage ";
        }
        
        // Add inaccessibility context if relevant
        if (!empty($inaccessibleAreas)) {
            $accessibilityPhrases = [];
            
            if (isset($inaccessibleAreas["chest"])) {
                $accessibilityPhrases[] = "their chest piercing vibrates tantalizingly behind the locked chastity bra";
            }
            if (isset($inaccessibleAreas["groin"])) {
                $accessibilityPhrases[] = "the vibrations between their legs pulse behind the locked chastity belt, preventing any direct touch";
            }
            if (isset($inaccessibleAreas["rear"])) {
                $accessibilityPhrases[] = "the plug in their rear vibrates intensely while secured behind restraints";
            }
            
            if (!empty($accessibilityPhrases)) {
                $accessibilityContext = " As " . implode(", and ", $accessibilityPhrases) . ",";
            }
        }
    }
    
    // If we have a chastity belt but no specific inaccessible context yet, add a generic one
    if ($hasChastityBelt && empty($accessibilityContext) && (
        strpos($sourcesList, "vaginal") !== false || 
        strpos($sourcesList, "anal") !== false || 
        strpos($sourcesList, "clitoral") !== false)) {
        $accessibilityContext = " The locked chastity belt prevents any direct touch to relieve the maddening sensations,";
    }
    
    // Add a fallback if no body part reaction was set
    if (empty($bodyPartReaction)) {
        $bodyPartReaction = "their body ";
    }
    
    // Observer-specific erotic descriptions when devices aren't visible
    $observerDescriptions = [];
    if (!$visibleToOthers) {
        // Descriptions get more intense based on intensity
        if ($intensity == "weakly") {
            $observerDescriptions = [
                "their body tensing unexpectedly",
                "a subtle tremble running through them",
                "their breath catches momentarily",
                "their posture shifting as if distracted by something unseen"
            ];
        } elseif ($intensity == "strongly") {
            $observerDescriptions = [
                "their thighs pressing together as if to contain some inner sensation",
                "their breath becoming irregular and husky",
                "their lips parting in a silent gasp",
                "their eyes slightly unfocusing as they're overcome by hidden pleasure"
            ];
        } elseif ($intensity == "intensely") {
            $observerDescriptions = [
                "their body betraying the intense sensations coursing through their hidden places",
                "their cheeks flushing with unbidden arousal",
                "their hands curling into fists as they struggle to maintain composure",
                "an unmistakable quiver running through their form as they battle inner sensations"
            ];
        } else { // extremely intensely
            $observerDescriptions = [
                "their entire body trembling with barely restrained pleasure",
                "their knees weakening as unseen vibrations assault their most sensitive areas",
                "their expression contorting with unmistakable arousal despite no visible cause",
                "helpless whimpers escaping their lips as they battle overwhelming hidden sensations"
            ];
        }
    }
    $observerDesc = !empty($observerDescriptions) ? $observerDescriptions[array_rand($observerDescriptions)] : "";
    
    // Create three different types of prompts - all narrated by The Narrator
    
    // 1. Narrator Prompt (omniscient view with full information)
    $narratorPrompt = "#SEX_INFO {$target}'s {$narratorDeviceDescription} {$intensityWord} begin to vibrate {$intensity}, sending waves of pleasure through their {$arousalDesc} body{$helplessnessAddition}!" . 
                     (!empty($accessibilityContext) ? " $accessibilityContext" : "");
    
    // 2. Self Prompt (from the perspective of the person wearing the devices)
    $selfPrompt = "#SEX_INFO {$target} {$reactionWord} as their {$wearerDeviceList} spring to life {$intensityWord}, {$intensity} stimulating " . 
                   $bodyPartReaction . (!empty($helplessness) ? ", while they remain $helplessness" : "") . "!" .
                   (!empty($accessibilityContext) ? " $accessibilityContext" : "");
    
    // 3. Other Prompt (what others can see, which may not include the actual devices)
    if ($visibleToOthers) {
        // Observers can see the devices
        $otherPrompt = "#SEX_INFO {$target} is caught by surprise as their visible {$otherDeviceList} begin to {$intensity} vibrate" . 
                       ", causing them to " . $reactionWord . "!";
    } else {
        // Devices hidden - describe only the reactions
        $otherPrompt = "#SEX_INFO {$target} {$intensityWord} " . $reactionWord . ", " . $observerDesc . 
                       ", hinting at unseen stimulation" . (!empty($helplessness) ? " while $helplessness" : "") . "!";
    }
    
    // Choose which prompt to use based on perspective
    $selectedPrompt = "";
    $perspective = GetPromptPerspective($target);
    
    minai_log("debug", "Vibrator prompts - perspective: " . $perspective);
    if ($perspective == "narrator") {
        // Narrator perspective - omniscient view
        $selectedPrompt = "The Narrator: " . $narratorPrompt;
    } 
    else if ($perspective == "self") {
        // Self perspective - wearer's experience
        $selectedPrompt = "The Narrator: " . $selfPrompt;
    }
    else {
        // Other perspective - observer view
        $selectedPrompt = "The Narrator: " . $otherPrompt;
    }
    
    return $selectedPrompt;
}

// Function to get vibrate stop prompt
function get_vibrate_stop_prompt() {
    $target = $GLOBALS["target"];
    
    // Better intensity extraction
    $intensity = "strongly"; // Default
    if (isset($GLOBALS["gameRequest"]) && isset($GLOBALS["gameRequest"][3])) {
        $message = $GLOBALS["gameRequest"][3];
        // Try to find any of the known intensity words in the message
        $intensityWords = ["weakly", "strongly", "intensely", "extremely intensely"];
        foreach ($intensityWords as $word) {
            if (stripos($message, $word) !== false) {
                $intensity = $word;
                break;
            }
        }
    }
    
    $deviceContext = GetVibratingDevicesContext($target);
    
    // Get specific vibration sources with accessibility info
    $vibrationData = GetVibrationSources($target);
    $vibrationSources = isset($vibrationData["sources"]) ? $vibrationData["sources"] : [];
    $accessibilityInfo = isset($vibrationData["accessibility"]) ? $vibrationData["accessibility"] : [];
    
    // Create a descriptive list of sources with accessibility
    $descriptiveSources = [];
    foreach ($vibrationSources as $source) {
        $accessibility = isset($accessibilityInfo[$source]) ? $accessibilityInfo[$source] : "accessible";
        
        // Only add accessibility info if it's not accessible
        if ($accessibility != "accessible") {
            $descriptiveSources[] = "$source ($accessibility)";
        } else {
            $descriptiveSources[] = $source;
        }
    }
    
    $sourcesList = !empty($descriptiveSources) ? implode(" and ", $descriptiveSources) : "hidden devices";
    
    // Device lists for different perspectives
    $wearerDeviceList = !empty($vibrationSources) ? $sourcesList : "hidden devices";
    
    // Create separate lists for visible and hidden devices for the narrator perspective
    $narratorVisibleDevices = [];
    $narratorHiddenDevices = [];
    
    // Check visibility of devices to others
    $visibleToOthers = false;
    $visibleDevices = [];
    
    foreach ($vibrationSources as $source) {
        $isVisible = false;
        $isHidden = true;
        
        // Check if this device is in the otherDevices visible array
        if (isset($deviceContext["otherDevices"]["visible"])) {
            foreach ($deviceContext["otherDevices"]["visible"] as $device) {
                if (isset($device["type"]) && $device["type"] == $source) {
                    $isVisible = true;
                    $visibleDevices[] = $source;
                    break;
                }
            }
        }
        
        // Check if this device is in the narratorDevices visible array
        if (isset($deviceContext["narratorDevices"]["visible"])) {
            foreach ($deviceContext["narratorDevices"]["visible"] as $device) {
                if (isset($device["type"]) && $device["type"] == $source) {
                    $isHidden = false;
                    $narratorVisibleDevices[] = $source;
                    break;
                }
            }
        }
        
        // Add to narrator hidden devices if it's hidden
        if ($isHidden) {
            $narratorHiddenDevices[] = $source;
        }
        
        if ($isVisible) {
            $visibleToOthers = true;
        }
    }
    
    // Create descriptive device lists for narrator
    $narratorVisibleDeviceList = !empty($narratorVisibleDevices) ? implode(" and ", $narratorVisibleDevices) : "";
    $narratorHiddenDeviceList = !empty($narratorHiddenDevices) ? implode(" and ", $narratorHiddenDevices) : "";
    
    // Combine visible and hidden devices for narrator with appropriate description
    $narratorDeviceDescription = "";
    if (!empty($narratorVisibleDeviceList) && !empty($narratorHiddenDeviceList)) {
        $narratorDeviceDescription = $narratorVisibleDeviceList . " and the hidden " . $narratorHiddenDeviceList;
    } elseif (!empty($narratorVisibleDeviceList)) {
        $narratorDeviceDescription = $narratorVisibleDeviceList;
    } elseif (!empty($narratorHiddenDeviceList)) {
        $narratorDeviceDescription = "hidden " . $narratorHiddenDeviceList;
    } else {
        $narratorDeviceDescription = "hidden devices";
    }
    
    $otherDeviceList = !empty($visibleDevices) ? implode(" and ", $visibleDevices) : "hidden devices";
    
    // Get additional context
    $arousalDesc = isset($deviceContext["arousalDesc"]) ? $deviceContext["arousalDesc"] : "";
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 0;
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    $hasChastityBelt = isset($deviceContext["hasChastityBelt"]) ? $deviceContext["hasChastityBelt"] : false;
    $hasChastityBra = isset($deviceContext["hasChastityBra"]) ? $deviceContext["hasChastityBra"] : false;
    $wearingTop = isset($deviceContext["wearingTop"]) ? $deviceContext["wearingTop"] : false;
    $wearingBottom = isset($deviceContext["wearingBottom"]) ? $deviceContext["wearingBottom"] : false;
    
    // Adjust aftereffect words based on previous intensity, arousal and gag
    $afterEffectWords = [];
    if ($hasGag) {
        if ($intensity == "weakly" || $arousal < 30) {
            $afterEffectWords = ["bringing a muffled sigh of relief", "their stifled reaction barely noticeable", "causing a small sound behind the gag", "their gagged expression relaxing slightly"];
        } elseif ($intensity == "strongly" || $arousal < 60) {
            $afterEffectWords = ["leaving them breathing heavily through their nose", "causing muffled sounds of relief", "making them shift uncomfortably", "their gagged expression showing relief"];
        } elseif ($intensity == "intensely" || $arousal < 80) {
            $afterEffectWords = ["leaving them making gagged noises", "causing muffled whimpers of frustration", "making them slump slightly with stifled sounds", "their body still visibly affected despite the gag"];
        } else { // extremely intensely or very aroused
            $afterEffectWords = ["leaving them making desperate noises behind the gag", "causing frustrated groans to escape despite the gag", "making them stagger with muffled cries", "their gagged form still quivering noticeably"];
        }
    } else {
        if ($intensity == "weakly" || $arousal < 30) {
            $afterEffectWords = ["bringing a sigh of relief", "the effect barely noticeable", "causing barely a reaction", "their expression relaxing slightly"];
        } elseif ($intensity == "strongly" || $arousal < 60) {
            $afterEffectWords = ["leaving them breathing heavily", "causing an audible gasp of relief", "making them shift uncomfortably", "their expression showing relief"];
        } elseif ($intensity == "intensely" || $arousal < 80) {
            $afterEffectWords = ["leaving them slightly breathless", "causing a whimper of frustration", "making them slump slightly", "their body still visibly affected"];
        } else { // extremely intensely or very aroused
            $afterEffectWords = ["leaving them almost desperate", "causing a frustrated groan to escape their lips", "making them stagger slightly", "their form still quivering noticeably"];
        }
    }
    $afterEffectWord = $afterEffectWords[array_rand($afterEffectWords)];
    
    // Create specific body part reactions and add accessibility context
    $bodyPartReaction = "";
    $lingering = "";
    
    if (!empty($vibrationSources)) {
        // Track which body areas are affected for combined description
        $affectedAreas = [
            "chest" => false,
            "groin" => false,
            "rear" => false
        ];
        
        // Track which areas have inaccessible devices that will continue to have lingering effects
        $inaccessibleAreas = [];
        
        foreach ($vibrationSources as $source) {
            // Determine body area affected
            if (strpos($source, "nipple") !== false) {
                $affectedAreas["chest"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["chest"] = $accessibilityInfo[$source];
                }
            } 
            elseif (strpos($source, "clitoral") !== false || strpos($source, "vaginal") !== false || strpos($source, "labia") !== false) {
                $affectedAreas["groin"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["groin"] = $accessibilityInfo[$source];
                }
            } 
            elseif (strpos($source, "anal") !== false) {
                $affectedAreas["rear"] = true;
                
                // Check if this source is inaccessible
                if (isset($accessibilityInfo[$source]) && $accessibilityInfo[$source] != "accessible") {
                    $inaccessibleAreas["rear"] = $accessibilityInfo[$source];
                }
            }
        }
        
        // Construct body part reaction string
        if ($affectedAreas["chest"]) {
            $bodyPartReaction .= "their chest ";
        }
        if ($affectedAreas["groin"]) {
            if (!empty($bodyPartReaction)) {
                $bodyPartReaction .= "and ";
            }
            $bodyPartReaction .= "their most intimate areas ";
        }
        if ($affectedAreas["rear"]) {
            if (!empty($bodyPartReaction)) {
                $bodyPartReaction .= "and ";
            }
            $bodyPartReaction .= "their rear passage ";
        }
        
        // Add lingering effects for inaccessible areas if arousal is high
        if (!empty($inaccessibleAreas) && $arousal > 50) {
            $lingeringPhrases = [];
            
            if (isset($inaccessibleAreas["chest"])) {
                $lingeringPhrases[] = "the sensations in their chest linger, trapped by the chastity bra";
            }
            if (isset($inaccessibleAreas["groin"])) {
                $lingeringPhrases[] = "arousal still pulses behind the locked chastity belt, with no way to find release";
            }
            if (isset($inaccessibleAreas["rear"])) {
                $lingeringPhrases[] = "the memory of the vibrations still teases their secured rear passage";
            }
            
            if (!empty($lingeringPhrases)) {
                $lingering = " Though the vibrations have stopped, " . implode(", and ", $lingeringPhrases) . ".";
            }
        }
    }
    
    // Add a fallback if no body part reaction was set
    if (empty($bodyPartReaction)) {
        $bodyPartReaction = "their body ";
    }
    
    // Observer-specific erotic descriptions for when devices stop but aren't visible
    $observerDescriptions = [];
    if (!$visibleToOthers && $arousal > 30) {
        // Descriptions get more intense based on arousal level
        if ($arousal < 50) {
            $observerDescriptions = [
                "their posture relaxing slightly as if a distraction has passed",
                "a subtle sigh escaping their lips",
                "their eyes refocusing as if returning from somewhere else",
                "their fidgeting subsiding gradually"
            ];
        } elseif ($arousal < 70) {
            $observerDescriptions = [
                "their breathing slowly returning to normal, though still slightly quickened",
                "their thighs still pressed together as if containing lingering sensations",
                "their cheeks still flushed with residual arousal",
                "their hands unclenching as the unseen stimulation eases"
            ];
        } else { // very aroused
            $observerDescriptions = [
                "their body still trembling with unresolved desire",
                "a frustrated look crossing their features as the hidden stimulation ends",
                "their knees still weak from the intense unseen pleasure",
                "their lips parted as if silently begging for more"
            ];
        }
    }
    $observerDesc = !empty($observerDescriptions) ? $observerDescriptions[array_rand($observerDescriptions)] : "";
    
    // Create three different types of prompts - all narrated by The Narrator
    
    // 1. Narrator Prompt (omniscient view with full information)
    $narratorPrompt = "#SEX_INFO The vibrations from {$target}'s {$narratorDeviceDescription} gradually come to a stop, " . 
                     $afterEffectWord . (!empty($helplessness) ? ", as they remain $helplessness" : "") . "." . $lingering;
    
    // 2. Self Prompt (from the perspective of the person wearing the devices)
    $selfPrompt = "#SEX_INFO The stimulation from {$target}'s {$wearerDeviceList} gradually subsides, " . 
                  $afterEffectWord . ". The sensations in " . $bodyPartReaction . 
                  " slowly fade away" . (!empty($helplessness) ? ", while they remain $helplessness" : "") . "." . $lingering;
    
    // 3. Other Prompt (what others can see, which may not include the actual devices)
    if ($visibleToOthers) {
        // Observers can see the devices
        $otherPrompt = "#SEX_INFO The visible vibrations from {$target}'s {$otherDeviceList} come to a stop, " . 
                       $afterEffectWord . ".";
    } else {
        // Devices hidden - describe only the reactions
        $otherPrompt = "#SEX_INFO {$target}'s unusual behavior subsides as " . $observerDesc . 
                       ", suggesting the hidden stimulation has ended" . 
                       (!empty($helplessness) ? ", though they remain $helplessness" : "") . ".";
    }
    
    // Choose which prompt to use based on perspective
    $selectedPrompt = "";
    $perspective = GetPromptPerspective($target);
    
    if ($perspective == "narrator") {
        // Narrator perspective - omniscient view
        $selectedPrompt = "The Narrator: " . $narratorPrompt;
    } 
    else if ($perspective == "self") {
        // Self perspective - wearer's experience
        $selectedPrompt = "The Narrator: " . $selfPrompt;
    }
    else {
        // Other perspective - observer view
        $selectedPrompt = "The Narrator: " . $otherPrompt;
    }
    
    return $selectedPrompt;
}

// Store the actual prompt strings in the PROMPTS global array
$GLOBALS["PROMPTS"]["minai_vibrate_start"] = [
    "player_request"=>[get_vibrate_start_prompt()]
];

$GLOBALS["PROMPTS"]["minai_vibrate_stop"] = [
    "player_request"=>[get_vibrate_stop_prompt()]
];