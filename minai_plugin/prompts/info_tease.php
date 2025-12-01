<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle teasing information prompts with arousal-based reactions
function get_info_tease_prompt($intensity = "medium") {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names and intensity from the message if possible
    $speakerName = "";
    $targetName = "";
    $intensityWord = "";
    
    if (preg_match('/^(.+?) (very weakly|weakly|medium|strongly|very strongly) teases (.+?) with/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $intensityWord = trim($matches[2]);
        $targetName = trim($matches[3]);
    }
    
    if (strlen($targetName) > 0) {
        $targetName = $targetName."'s ";
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
    $teaseDescriptions = [];
    
    if ($intensityWord == "very weakly") {
        $teaseDescriptions = [
            "barely perceptible pulses",
            "the faintest of vibrations",
            "gentle, teasing sensations",
            "whisper-soft tingles"
        ];
    } 
    elseif ($intensityWord == "weakly") {
        $teaseDescriptions = [
            "light, rhythmic pulses",
            "gentle but noticeable vibrations",
            "soft waves of pleasure",
            "mild but persistent tingles"
        ];
    }
    elseif ($intensityWord == "strongly" || $intensityWord == "strong") {
        $teaseDescriptions = [
            "powerful, pulsating vibrations",
            "strong waves of teasing pleasure",
            "intense, rhythmic sensations",
            "deep, resonating pulses"
        ];
    }
    elseif ($intensityWord == "very strongly" || $intensityWord == "very strong") {
        $teaseDescriptions = [
            "overwhelming, intense vibrations",
            "nearly overpowering waves of pleasure",
            "relentless, intense sensations",
            "almost unbearable pulses of stimulation"
        ];
    }
    else { // medium or default
        $teaseDescriptions = [
            "steady, rhythmic pulses",
            "tantalizing vibrations",
            "waves of pleasurable stimulation",
            "persistent sensations that demand attention"
        ];
    }
    
    // Get reactions based on arousal intensity
    $reactionDescriptions = [];
    
    // Low arousal reactions - surprise/unexpectedness
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $reactionDescriptions = [
                "causing a look of surprise in their eyes, their body flinching slightly at the unexpected sensation",
                "making them emit a muffled sound of startled confusion behind their gag",
                "their eyes widening with surprise as the unexpected stimulation interrupts their thoughts",
                "triggering a subtle twitch of startled response, their attention suddenly diverted"
            ];
        } else {
            $reactionDescriptions = [
                "causing a sharp intake of breath in surprise, their focus suddenly shattered",
                "drawing a quiet gasp as their body registers the unexpected sensation",
                "making their lips part with a soft 'oh' of surprise",
                "triggering a subtle flinch as they're caught off guard by the sensation"
            ];
        }
    }
    // Medium arousal reactions - more pronounced pleasure
    elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $reactionDescriptions = [
                "eliciting muffled sounds of growing pleasure behind their gag",
                "causing them to shift their weight restlessly, eyes betraying their growing arousal",
                "making their breathing quicken noticeably despite the gag",
                "triggering stifled gasps as they begin to respond more eagerly"
            ];
        } else {
            $reactionDescriptions = [
                "eliciting soft sounds of building pleasure from their parted lips",
                "causing them to shift restlessly, their body beginning to respond eagerly",
                "making their breathing quicken as their focus narrows to the sensations",
                "triggering quiet gasps that they try but fail to fully suppress"
            ];
        }
    }
    // High arousal reactions - difficult to resist
    elseif ($arousalIntensity === "high") {
        if ($hasGag) {
            $reactionDescriptions = [
                "drawing insistent muffled moans that can't be contained by their gag",
                "making them squirm uncontrollably as they struggle to process the building sensations",
                "causing their thighs to press together tightly as muffled sounds escape their gagged mouth",
                "triggering urgent gagged sounds as their hips begin to move of their own accord"
            ];
        } else {
            $reactionDescriptions = [
                "drawing increasingly desperate moans they can no longer control",
                "making them squirm visibly as they struggle to maintain composure",
                "causing their thighs to press together tightly as they bite their lip hard",
                "triggering unmistakable gasps of heightened desire"
            ];
        }
    }
    // Very high arousal reactions - desperate for more
    else {
        if ($hasGag) {
            $reactionDescriptions = [
                "forcing desperate sounds that somehow penetrate their gag, their body trembling with need",
                "making them writhe helplessly against their restraints, eyes pleading for more",
                "causing their knees to buckle as muffled whimpers of frustration escape around the gag",
                "triggering violent trembling they cannot control as they strain against the bonds holding them back"
            ];
        } else {
            $reactionDescriptions = [
                "forcing cries of frustrated pleasure from their lips as they beg for more",
                "making them writhe helplessly, desperate for greater stimulation",
                "causing their knees to buckle as they struggle to remain upright through waves of pleasure",
                "triggering uncontrolled trembling as they desperately fight the urge to touch themselves"
            ];
        }
    }
    
    $teaseDesc = $teaseDescriptions[array_rand($teaseDescriptions)];
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while $helplessness";
    }
    
    // Add chastity context if relevant
    $chastityContext = "";
    if ($hasChastityBelt) {
        $chastityContext = " The locked chastity belt prevents any relief from the maddening sensations.";
    }
    
    // Create the prompt with detailed context
    $promptText = "$speakerName activates {$targetName}{$deviceDescription}, sending $teaseDesc through their body. ";
    $promptText .= "The carefully controlled vibrations tease without satisfying$helplessnessContext, $reactionDesc.$chastityContext";
    
    return "The Narrator: " . $promptText;
}

// Register the prompts for different intensities only if requested
if ($GLOBALS["gameRequest"][0] == "info_tease_very_weak") {
    $promptText = OverrideGameRequestPrompt(get_info_tease_prompt("very weakly"));
    $GLOBALS["PROMPTS"]["info_tease_very_weak"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_tease_weak") {
    $promptText = OverrideGameRequestPrompt(get_info_tease_prompt("weakly"));
    $GLOBALS["PROMPTS"]["info_tease_weak"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_tease_medium") {
    $promptText = OverrideGameRequestPrompt(get_info_tease_prompt("medium"));
    $GLOBALS["PROMPTS"]["info_tease_medium"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_tease_strong") {
    $promptText = OverrideGameRequestPrompt(get_info_tease_prompt("strongly"));
    $GLOBALS["PROMPTS"]["info_tease_strong"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
}

if ($GLOBALS["gameRequest"][0] == "info_tease_very_strong") {
    $promptText = OverrideGameRequestPrompt(get_info_tease_prompt("very strongly"));
    $GLOBALS["PROMPTS"]["info_tease_very_strong"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 