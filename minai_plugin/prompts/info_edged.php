<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle edging information prompts
function get_info_edged_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    // Removed speaker name as it's no longer needed
    $targetName = $GLOBALS["target"];
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 90; // Default to very high arousal for edging
    $intensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Create intro variations for edging - reworded to work without a speaker
    $edgingIntros = [
        "The intensity of $targetName's $deviceDescription rises and falls with perfect control, bringing them to the very edge",
        "$targetName's $deviceDescription pulses with expertly calculated patterns to build unbearable tension",
        "The settings on $targetName's $deviceDescription teasingly increase to near-climax levels",
        "Precisely timed surges of sensation from $targetName's $deviceDescription push them to their limit"
    ];
    
    $edgingIntro = $edgingIntros[array_rand($edgingIntros)];
    
    // Create buildup variations
    $buildupPhrases = [
        "gradually building the pleasure until they're teetering on the brink",
        "steadily intensifying the stimulation until release seems inevitable",
        "carefully escalating the sensations until they're desperate for climax",
        "meticulously raising the pleasure to an almost unbearable peak"
    ];
    
    $buildupPhrase = $buildupPhrases[array_rand($buildupPhrases)];
    
    // Create denial variations
    $denialPhrases = [
        "then abruptly stops, leaving them quivering at the precipice of release",
        "only to suddenly cease all stimulation, abandoning them on the knife-edge of climax",
        "before cruelly withdrawing, stranding them at the very threshold of orgasm",
        "then unexpectedly halts, trapping them in an exquisite state of unfulfilled need"
    ];
    
    $denialPhrase = $denialPhrases[array_rand($denialPhrases)];
    
    // Create variations for reactions based on context and arousal intensity
    $reactionDescriptions = [];
    
    if ($hasGag) {
        if ($intensity === "very_high") {
            $reactionDescriptions = [
                "their entire body trembling violently as muffled desperate pleas escape past their gag",
                "their hips bucking frantically seeking the denied release, gagged whimpers filling the air",
                "their eyes wide with frustrated need as they strain against their restraints, guttural sounds barely contained by the gag",
                "convulsing with unfulfilled desire, desperate begging sounds muffled by their gag"
            ];
        } else {
            $reactionDescriptions = [
                "their body shuddering with need as stifled moans escape through their gag",
                "their limbs twitching with unfulfilled desire, muffled whimpers barely audible",
                "their eyes pleading for mercy as they squirm helplessly, gagged sounds of frustration filling the air",
                "their muscles tensing and releasing as they're left in a state of suspended pleasure"
            ];
        }
    } else {
        if ($intensity === "very_high") {
            $reactionDescriptions = [
                "their entire body quaking with frustration as they desperately beg for release",
                "their voice breaking as they plead, hips writhing involuntarily seeking the denied stimulation",
                "gasping raggedly between increasingly desperate demands for completion",
                "tears forming at the corners of their eyes as they hover in an exquisite agony of denial"
            ];
        } else {
            $reactionDescriptions = [
                "their breath coming in short, frustrated gasps as they whimper in need",
                "soft groans of disappointment escaping their lips as they squirm with unfulfilled desire",
                "their body tense with anticipation that now has nowhere to go",
                "their expression a mixture of pleasure and torment as they're left wanting"
            ];
        }
    }
    
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while remaining completely $helplessness";
    }
    
    // Create aftermath variations
    $aftermathDescriptions = [
        "They hover in a delicious state of denial, the promise of release tantalizingly out of reach.",
        "Their body remains suspended in a heightened state of arousal, craving the completion it was denied.",
        "The cruel interruption leaves them trapped in an exquisite purgatory between pleasure and frustration.",
        "Every nerve ending remains electrified with need, their entire being focused on the release withheld from them."
    ];
    
    $aftermathDesc = $aftermathDescriptions[array_rand($aftermathDescriptions)];
    
    // Create the prompt with detailed context - removed speakerName dependency
    $promptText = "$edgingIntro, $buildupPhrase, $denialPhrase. ";
    $promptText .= "$reactionDesc$helplessnessContext. ";
    $promptText .= "$aftermathDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_edged") {
    $promptText = OverrideGameRequestPrompt(get_info_edged_prompt());
    $GLOBALS["PROMPTS"]["info_edged"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["minai_edged"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 