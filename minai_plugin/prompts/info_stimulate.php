<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle stimulation information prompts with arousal-based reactions
function get_info_stimulate_prompt($intensity = "medium") {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names and intensity from the message if possible
    $speakerName = "";
    $targetName = "";
    $intensityWord = "";
    
    if (preg_match('/^(.+?) (very weakly|weakly|medium|strongly|very strongly) stimulates (.+?) with/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $intensityWord = trim($matches[2]);
        $targetName = trim($matches[3]);
    }
    
    // If intensity wasn't found in the message, use the parameter
    if (empty($intensityWord)) {
        $intensityWord = $intensity;
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    $hasChastityBelt = isset($deviceContext["hasChastityBelt"]) ? $deviceContext["hasChastityBelt"] : false;
    
    // Adjust descriptions based on device intensity
    $stimulateDescriptions = [];
    
    if ($intensityWord == "very weakly") {
        $stimulateDescriptions = [
            "faint, continuous vibrations",
            "gentle but persistent stimulation",
            "subtle waves of pleasure",
            "mild, teasing sensations"
        ];
    } 
    elseif ($intensityWord == "weakly") {
        $stimulateDescriptions = [
            "steady, warming vibrations",
            "noticeable waves of stimulation",
            "pleasant pulses of sensation",
            "consistent, enjoyable tingles"
        ];
    }
    elseif ($intensityWord == "strongly" || $intensityWord == "strong") {
        $stimulateDescriptions = [
            "powerful, concentrated vibrations",
            "strong, persistent stimulation",
            "intense waves of direct pleasure",
            "deep, resonating sensations"
        ];
    }
    elseif ($intensityWord == "very strongly" || $intensityWord == "very strong") {
        $stimulateDescriptions = [
            "overwhelming, relentless vibrations",
            "extremely intense stimulation",
            "powerful, all-consuming waves of pleasure",
            "nearly unbearable pulses of direct sensation"
        ];
    }
    else { // medium or default
        $stimulateDescriptions = [
            "firm, rhythmic vibrations",
            "strong waves of stimulation",
            "intense pulses of pleasure",
            "powerful, continuous sensations"
        ];
    }
    
    // Get reactions based on arousal intensity - stimulation reactions are more direct than teasing
    $reactionDescriptions = [];
    
    // Low arousal reactions - surprise/unexpectedness
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $reactionDescriptions = [
                "causing a jolt of surprised pleasure, their muffled exclamation clearly audible despite the gag",
                "making their body stiffen with unexpected intensity, a startled sound escaping around their gag",
                "triggering an immediate physical response they weren't prepared for, eyes widening in surprise",
                "eliciting a muffled gasp as the sensation catches them completely off guard"
            ];
        } else {
            $reactionDescriptions = [
                "causing a sudden gasp as the unexpected pleasure takes them by surprise",
                "making their body jerk with startled response to the direct stimulation",
                "triggering an immediate 'Oh!' of surprise as the sensation registers",
                "eliciting a sharp intake of breath as they're caught unprepared by the intensity"
            ];
        }
    }
    // Medium arousal reactions - more pronounced pleasure
    elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $reactionDescriptions = [
                "drawing eager muffled sounds from behind their gag as their body responds eagerly",
                "making their eyes flutter closed momentarily as they lean into the sensation",
                "causing them to shift position to maximize the pleasure, soft sounds escaping despite the gag",
                "triggering stifled moans as they begin to lose themselves in the feeling"
            ];
        } else {
            $reactionDescriptions = [
                "drawing soft, approving sounds as they welcome the direct stimulation",
                "making their eyes flutter closed as they focus intently on the sensation",
                "causing them to shift position instinctively to enhance the pleasure",
                "triggering quiet moans they don't bother trying to suppress"
            ];
        }
    }
    // High arousal reactions - difficult to resist
    elseif ($arousalIntensity === "high") {
        if ($hasGag) {
            $reactionDescriptions = [
                "forcing muffled cries past their gag as the stimulation builds on their already heightened state",
                "making their body twist and arch as the sensations overwhelm their restraint",
                "causing them to pull urgently at their restraints, desperate for more direct contact",
                "triggering muffled but unmistakable sounds of approaching climax"
            ];
        } else {
            $reactionDescriptions = [
                "forcing loud, uncontrolled moans as the stimulation drives them closer to the edge",
                "making their body twist and arch as they struggle to process the intense pleasure",
                "causing them to clutch desperately at anything within reach for support",
                "triggering unmistakable sounds that reveal how close they are to climax"
            ];
        }
    }
    // Very high arousal reactions - desperate for more/release
    else {
        if ($hasGag) {
            $reactionDescriptions = [
                "forcing desperate, primal sounds that the gag barely contains as they teeter on the edge of release",
                "making their entire body convulse with each pulse, their eyes pleading desperately above the gag",
                "causing them to thrash against their bonds, their muffled cries becoming increasingly frantic",
                "triggering violent shudders that wrack their body as they struggle not to succumb immediately"
            ];
        } else {
            $reactionDescriptions = [
                "forcing raw cries that verge on screams as they desperately approach climax",
                "making their entire body convulse with each pulse as they teeter on the edge of collapse",
                "causing them to beg incoherently for release, words dissolving into desperate moans",
                "triggering violent shudders that wrack their frame as they struggle not to climax immediately"
            ];
        }
    }
    
    $stimulateDesc = $stimulateDescriptions[array_rand($stimulateDescriptions)];
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while remaining $helplessness";
    }
    
    // Add chastity context if relevant
    $chastityContext = "";
    if ($hasChastityBelt) {
        $chastityContext = " The locked chastity belt intensifies the sensations by preventing any direct touch.";
    }
    
    // Create the prompt with detailed context
    $promptText = "$speakerName deliberately activates $targetName's $deviceDescription, sending $stimulateDesc through their body. ";
    $promptText .= "The direct stimulation builds steadily$helplessnessContext, $reactionDesc.$chastityContext";
    
    return "The Narrator: " . $promptText;
}

// Register the prompts for different intensities only if requested
if ($GLOBALS["gameRequest"][0] == "info_stimulate_very_weak") {
    $promptText = OverrideGameRequestPrompt(get_info_stimulate_prompt("very weakly"));
    $GLOBALS["PROMPTS"]["info_stimulate_very_weak"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_stimulate_weak") {
    $promptText = OverrideGameRequestPrompt(get_info_stimulate_prompt("weakly"));
    $GLOBALS["PROMPTS"]["info_stimulate_weak"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_stimulate_medium") {
    $promptText = OverrideGameRequestPrompt(get_info_stimulate_prompt("medium"));
    $GLOBALS["PROMPTS"]["info_stimulate_medium"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_stimulate_strong") {
    $promptText = OverrideGameRequestPrompt(get_info_stimulate_prompt("strongly"));
    $GLOBALS["PROMPTS"]["info_stimulate_strong"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_stimulate_very_strong") {
    $promptText = OverrideGameRequestPrompt(get_info_stimulate_prompt("very strongly"));
    $GLOBALS["PROMPTS"]["info_stimulate_very_strong"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 