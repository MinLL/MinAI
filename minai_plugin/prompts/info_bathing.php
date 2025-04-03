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
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 0;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Get device descriptions
    $deviceDescription = GetDeviceDescription($target);
    $hasDevices = $deviceDescription !== "hidden devices";
    
    // Create different descriptions based on arousal level and devices
    $bathingDescriptions = [];
    
    if ($arousalIntensity == "very_high") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName's hands tremble slightly as they begin bathing, their cheeks burning crimson as they work around their $deviceDescription.",
                "A deep blush spreads across $speakerName's face and chest as they start bathing, their $deviceDescription making them squirm with every touch.",
                "$speakerName fidgets nervously as they begin bathing, their $deviceDescription drawing involuntary shivers with each movement."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName's hands tremble slightly as they begin bathing, their cheeks burning crimson with every touch of the water.",
                "A deep blush spreads across $speakerName's face and chest as they start bathing, their body responding with involuntary shivers.",
                "$speakerName fidgets nervously as they begin bathing, their skin hypersensitive to the water's touch."
            ];
        }
    } elseif ($arousalIntensity == "high") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName's cheeks flush pink as they begin bathing, their $deviceDescription making them shift uncomfortably with each movement.",
                "A visible shiver runs through $speakerName as they start bathing, their $deviceDescription drawing their attention with every touch.",
                "$speakerName's breath hitches slightly as they begin bathing, their $deviceDescription making them squirm with quiet embarrassment."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName's cheeks flush pink as they begin bathing, their body responding with quiet shivers to the water's touch.",
                "A visible shiver runs through $speakerName as they start bathing, their skin tingling with heightened sensitivity.",
                "$speakerName's breath hitches slightly as they begin bathing, their body responding with quiet embarrassment to the water's touch."
            ];
        }
    } elseif ($arousalIntensity == "medium") {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName begins their bathing routine with a slight blush, their $deviceDescription drawing occasional fidgeting.",
                "A soft pink tint colors $speakerName's cheeks as they start bathing, their $deviceDescription making them shift occasionally.",
                "$speakerName carefully begins their hygiene routine, their $deviceDescription drawing quiet, involuntary movements."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName begins their bathing routine with a slight blush, their body responding with quiet awareness.",
                "A soft pink tint colors $speakerName's cheeks as they start bathing, their skin tingling with gentle sensitivity.",
                "$speakerName carefully begins their hygiene routine, their body responding with quiet, involuntary movements to the water's touch."
            ];
        }
    } else {
        if ($hasDevices) {
            $bathingDescriptions = [
                "$speakerName begins their bathing routine, their $deviceDescription requiring careful attention.",
                "$speakerName starts their hygiene ritual, taking care to clean around their $deviceDescription.",
                "$speakerName begins their bathing routine, ensuring proper care of their $deviceDescription."
            ];
        } else {
            $bathingDescriptions = [
                "$speakerName begins their bathing routine, moving with practiced ease.",
                "$speakerName starts their hygiene ritual, taking care to clean every part of their body.",
                "$speakerName begins their bathing routine, ensuring thorough cleaning of their body."
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
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 0;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Get device descriptions
    $deviceDescription = GetDeviceDescription($target);
    $hasDevices = $deviceDescription !== "hidden devices";
    
    // Create different descriptions based on arousal level and devices
    $completionDescriptions = [];
    
    if ($arousalIntensity == "very_high") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName's entire body trembles as they finish bathing, their cheeks burning crimson as they work around their $deviceDescription.",
                "A deep blush spreads across $speakerName's face and chest as they complete their bathing, their $deviceDescription making them squirm with every movement.",
                "$speakerName fidgets nervously as they finish bathing, their $deviceDescription drawing involuntary shivers with each touch."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName's entire body trembles as they finish bathing, their cheeks burning crimson with every touch of the water.",
                "A deep blush spreads across $speakerName's face and chest as they complete their bathing, their body responding with involuntary shivers.",
            ];
        }
    } elseif ($arousalIntensity == "high") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName's cheeks remain flushed pink as they finish bathing, their $deviceDescription making them shift uncomfortably with each movement.",
                "A visible shiver runs through $speakerName as they complete their bathing, their $deviceDescription drawing their attention with every touch.",
                "$speakerName's breath hitches slightly as they finish bathing, their $deviceDescription making them squirm with quiet embarrassment."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName's cheeks remain flushed pink as they finish bathing, their body responding with quiet shivers to the water's touch.",
                "A visible shiver runs through $speakerName as they complete their bathing, their skin tingling with heightened sensitivity.",
            ];
        }
    } elseif ($arousalIntensity == "medium") {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName completes their bathing routine with a slight blush, their $deviceDescription drawing occasional fidgeting.",
                "A soft pink tint remains on $speakerName's cheeks as they finish bathing, their $deviceDescription making them shift occasionally.",
                "$speakerName carefully concludes their hygiene routine, their $deviceDescription drawing quiet, involuntary movements."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName completes their bathing routine with a slight blush, their body responding with quiet awareness.",
                "A soft pink tint remains on $speakerName's cheeks as they finish bathing, their skin tingling with gentle sensitivity.",
            ];
        }
    } else {
        if ($hasDevices) {
            $completionDescriptions = [
                "$speakerName finishes their bathing routine, having carefully cleaned around their $deviceDescription.",
                "$speakerName completes their hygiene ritual, ensuring their $deviceDescription is properly maintained.",
                "$speakerName concludes their bathing routine, having given proper attention to their $deviceDescription."
            ];
        } else {
            $completionDescriptions = [
                "$speakerName finishes their bathing routine, feeling clean and refreshed.",
                "$speakerName completes their hygiene ritual, their body now clean and properly cared for.",
                "$speakerName concludes their bathing routine, feeling fresh and renewed."
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
if ($GLOBALS["gameRequest"][0] == "minai_bathing" || $GLOBALS["gameRequest"][0] == "info_minai_bathing") {
    $promptText = OverrideGameRequestPrompt(get_info_bathing_prompt());
    $GLOBALS["PROMPTS"]["minai_bathing"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["info_minai_bathing"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} elseif ($GLOBALS["gameRequest"][0] == "minai_bathing_complete" || $GLOBALS["gameRequest"][0] == "info_minai_bathing_complete") {
    $promptText = OverrideGameRequestPrompt(get_info_bathing_complete_prompt());
    $GLOBALS["PROMPTS"]["minai_bathing_complete"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["info_minai_bathing_complete"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 