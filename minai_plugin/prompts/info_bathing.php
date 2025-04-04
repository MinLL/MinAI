<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle bathing start information prompts
function get_info_bathing_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract name from the message
    $speakerName = "";
    
    if (preg_match('/^(.+?)\s+stripped down naked and started bathing/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get arousal context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Get device descriptions
    $deviceDescription = GetDeviceDescription($target);
    $hasDevices = $deviceDescription !== "hidden devices";
    
    // Create different descriptions based on arousal level and devices
    $bathingDescriptions = [];
    
    if ($arousalIntensity == "very_high") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName's hands tremble slightly as they strip down, their cheeks burning crimson as they work around their $deviceDescription while undressing before bathing.",
                "A deep blush spreads across $speakerName's face and chest as they remove their clothes, their $deviceDescription making them squirm with every movement as they prepare to bathe.",
                "$speakerName fidgets nervously as they undress, their $deviceDescription drawing involuntary shivers as they prepare to begin bathing."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName's hands tremble slightly as they strip down, their cheeks burning crimson with every touch of their own skin as they prepare to bathe.",
                "A deep blush spreads across $speakerName's face and chest as they remove their clothes, their body responding with involuntary shivers before bathing.",
                "$speakerName fidgets nervously as they undress, their skin hypersensitive to the air's touch as they prepare to begin bathing."
            ];
        }
    } elseif ($arousalIntensity == "high") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName's cheeks flush pink as they strip down, their $deviceDescription making them shift uncomfortably with each piece of clothing removed before bathing.",
                "A visible shiver runs through $speakerName as they undress, their $deviceDescription drawing their attention with every movement as they prepare to bathe.",
                "$speakerName's breath hitches slightly as they remove their clothes, their $deviceDescription making them squirm with quiet embarrassment before bathing."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName's cheeks flush pink as they strip down, their body responding with quiet shivers to the air's touch as they prepare to bathe.",
                "A visible shiver runs through $speakerName as they undress, their skin tingling with heightened sensitivity before bathing.",
                "$speakerName's breath hitches slightly as they remove their clothes, their body responding with quiet embarrassment to the exposure as they prepare to bathe."
            ];
        }
    } elseif ($arousalIntensity == "medium") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName begins undressing with a slight blush, their $deviceDescription drawing occasional fidgeting as they remove their clothes before bathing.",
                "A soft pink tint colors $speakerName's cheeks as they strip down, their $deviceDescription making them shift occasionally while undressing before bathing.",
                "$speakerName carefully removes their clothes, their $deviceDescription drawing quiet, involuntary movements as they prepare to begin bathing."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName begins undressing with a slight blush, their body responding with quiet awareness to the air's touch as they prepare to bathe.",
                "A soft pink tint colors $speakerName's cheeks as they strip down, their skin tingling with gentle sensitivity before bathing.",
                "$speakerName carefully removes their clothes, their body responding with quiet, involuntary movements to the exposure as they prepare to bathe."
            ];
        }
    } else {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName begins undressing, their $deviceDescription requiring careful attention as they remove their clothes before bathing.",
                "$speakerName starts stripping down, taking care to work around their $deviceDescription while undressing before bathing.",
                "$speakerName removes their clothes, ensuring proper care of their $deviceDescription as they prepare to begin bathing."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName begins undressing, moving with practiced ease as they prepare to bathe.",
                "$speakerName starts stripping down, taking care to remove their clothes methodically before bathing.",
                "$speakerName removes their clothes, preparing for their bathing routine with quiet efficiency."
            ];
        }
    }
    
    // Select a random description
    $bathingDesc = $bathingDescriptions[array_rand($bathingDescriptions)];
    
    // Create the prompt
    $promptText = "$bathingDesc";
    
    return "The Narrator: " . $promptText;
}

// Function to handle bathing completion information prompts
function get_info_bathing_complete_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract name from the message
    $speakerName = "";
    
    if (preg_match('/^(.+?)\s+finished bathing and gets dressed/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get arousal context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Get device descriptions
    $deviceDescription = GetDeviceDescription($target);
    $hasDevices = $deviceDescription !== "hidden devices";
    
    // Create different descriptions based on arousal level and devices
    $completionDescriptions = [];
    
    if ($arousalIntensity == "very_high") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName's entire body trembles as they finish bathing and reach for their clothes, their cheeks burning crimson as they work around their $deviceDescription while dressing after bathing.",
                "A deep blush spreads across $speakerName's face and chest as they get dressed, their $deviceDescription making them squirm with every movement as they dry off after bathing.",
                "$speakerName fidgets nervously as they put on their clothes, their $deviceDescription drawing involuntary shivers with each touch as they finish bathing."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName's entire body trembles as they finish bathing and reach for their clothes, their cheeks burning crimson with every touch of fabric as they dry off after bathing.",
                "A deep blush spreads across $speakerName's face and chest as they get dressed, their body responding with involuntary shivers as they finish bathing.",
                "$speakerName fidgets nervously as they put on their clothes, their skin hypersensitive to the fabric's touch as they dry off after bathing."
            ];
        }
    } elseif ($arousalIntensity == "high") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName's cheeks remain flushed pink as they finish bathing and dress, their $deviceDescription making them shift uncomfortably with each piece of clothing as they dry off after bathing.",
                "A visible shiver runs through $speakerName as they get dressed, their $deviceDescription drawing their attention with every movement as they finish bathing.",
                "$speakerName's breath hitches slightly as they put on their clothes, their $deviceDescription making them squirm with quiet embarrassment as they dry off after bathing."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName's cheeks remain flushed pink as they finish bathing and dress, their body responding with quiet shivers to the fabric's touch as they dry off after bathing.",
                "A visible shiver runs through $speakerName as they get dressed, their skin tingling with heightened sensitivity as they finish bathing.",
                "$speakerName's breath hitches slightly as they put on their clothes, their body responding with quiet embarrassment to the fabric's touch as they dry off after bathing."
            ];
        }
    } elseif ($arousalIntensity == "medium") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName completes their bathing routine with a slight blush, their $deviceDescription drawing occasional fidgeting as they get dressed after bathing.",
                "A soft pink tint remains on $speakerName's cheeks as they finish bathing and dress, their $deviceDescription making them shift occasionally as they dry off.",
                "$speakerName carefully gets dressed, their $deviceDescription drawing quiet, involuntary movements as they prepare to leave after bathing."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName completes their bathing routine with a slight blush, their body responding with quiet awareness to the fabric's touch as they dry off after bathing.",
                "A soft pink tint remains on $speakerName's cheeks as they finish bathing and dress, their skin tingling with gentle sensitivity as they complete their bathing routine.",
                "$speakerName carefully gets dressed, their body responding with quiet, involuntary movements to the fabric's touch as they dry off after bathing."
            ];
        }
    } else {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName finishes bathing and gets dressed, having carefully worked around their $deviceDescription as they dry off after bathing.",
                "$speakerName completes their hygiene ritual and dresses, ensuring their $deviceDescription is properly maintained as they finish bathing.",
                "$speakerName concludes their bathing routine and gets dressed, having given proper attention to their $deviceDescription as they dry off after bathing."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName finishes bathing and gets dressed, feeling clean and refreshed after bathing.",
                "$speakerName completes their hygiene ritual and dresses, their body now clean and properly cared for after bathing.",
                "$speakerName concludes their bathing routine and gets dressed, feeling fresh and renewed as they dry off after bathing."
            ];
        }
    }
    
    // Select a random description
    $completionDesc = $completionDescriptions[array_rand($completionDescriptions)];
    
    // Create the prompt
    $promptText = "$completionDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompts only if these specific prompts are requested
if ($GLOBALS["gameRequest"][0] == "minai_bathing" || $GLOBALS["gameRequest"][0] == "info_bathing") {
    $promptText = OverrideGameRequestPrompt(get_info_bathing_prompt());
    $GLOBALS["PROMPTS"]["minai_bathing"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["info_bathing"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} elseif ($GLOBALS["gameRequest"][0] == "minai_bathing_complete" || $GLOBALS["gameRequest"][0] == "info_bathing_complete") {
    $promptText = OverrideGameRequestPrompt(get_info_bathing_complete_prompt());
    $GLOBALS["PROMPTS"]["minai_bathing_complete"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["info_bathing_complete"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 