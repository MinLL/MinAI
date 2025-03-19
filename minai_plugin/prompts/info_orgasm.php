<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle orgasm information prompts
function get_info_orgasm_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    // Removed speaker name as it's no longer needed
    $targetName = $GLOBALS["target"];
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 80; // Default to high arousal for orgasm
    $intensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Create intro variations for orgasm control - reworded to work without a speaker
    $orgasmIntros = [
        "The intensity of $targetName's $deviceDescription suddenly turns to maximum",
        "The full power of $targetName's $deviceDescription is unleashed",
        "$targetName's $deviceDescription activates at its highest setting",
        "Intense pulses surge through $targetName's $deviceDescription"
    ];
    
    $orgasmIntro = $orgasmIntros[array_rand($orgasmIntros)];
    
    // Create transition variations
    $transitionPhrases = [
        "forcing an immediate, powerful orgasm",
        "driving them helplessly over the edge",
        "triggering an uncontrollable climax",
        "overwhelming their senses with unstoppable pleasure"
    ];
    
    $transitionPhrase = $transitionPhrases[array_rand($transitionPhrases)];
    
    // Create intensity variations
    $intensityPhrases = [
        "The sensations intensify relentlessly",
        "The stimulation builds to unbearable heights",
        "The pulsations grow stronger and faster",
        "The vibrations hit their most sensitive spots with perfect precision"
    ];
    
    $intensityPhrase = $intensityPhrases[array_rand($intensityPhrases)];
    
    // Create variations based on context and arousal intensity
    $orgasmDescriptions = [];
    
    // For orgasms, reactions are always intense, but can vary
    if ($hasGag) {
        if ($intensity === "very_high") {
            $orgasmDescriptions = [
                "their entire body spasming violently as desperate muffled screams struggle past their gag",
                "their back arching almost painfully as their limbs jerk uncontrollably, gagged cries filling the air",
                "their eyes rolling back completely as they thrash against their restraints, guttural sounds barely contained by the gag",
                "convulsing with such force they might tear their bonds, desperate animalistic sounds muffled by their gag"
            ];
        } else {
            $orgasmDescriptions = [
                "their eyes rolling back as muffled cries escape through their gag",
                "their body convulsing in helpless pleasure, stifled moans barely audible through the gag",
                "their limbs straining against restraints as they experience an overwhelming climax",
                "their gagged mouth unable to fully express the intensity of their forced pleasure"
            ];
        }
    } else {
        if ($intensity === "very_high") {
            $orgasmDescriptions = [
                "their entire body spasming violently as they scream in uncontrollable pleasure",
                "their back arching almost painfully as their limbs jerk uncontrollably, primal cries filling the air",
                "their eyes rolling back completely as a series of desperate, shuddering cries escape their lips",
                "convulsing with such force they collapse completely, surrendering utterly to overwhelming sensation"
            ];
        } else {
            $orgasmDescriptions = [
                "their back arching as waves of intense pleasure course through them",
                "their body trembling uncontrollably as they're pushed over the edge",
                "gasping and moaning as they succumb to the overwhelming sensations",
                "crying out as their body surrenders to the irresistible climax"
            ];
        }
    }
    
    $orgasmDesc = $orgasmDescriptions[array_rand($orgasmDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while remaining completely $helplessness";
    }
    
    // Create finale variations
    $finaleDescriptions = [
        "Their body has no choice but to surrender to the intense pleasure.",
        "The overwhelming sensations leave them completely at the mercy of their own body's response.",
        "There is no fighting the powerful climax that wracks their form.",
        "The relentless stimulation proves that pleasure can be commanded with perfect precision."
    ];
    
    $finaleDesc = $finaleDescriptions[array_rand($finaleDescriptions)];
    
    // Create the prompt with detailed context - removed speakerName dependency
    $promptText = "$orgasmIntro, $transitionPhrase. ";
    $promptText .= "$intensityPhrase$helplessnessContext, $orgasmDesc. ";
    $promptText .= "$finaleDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_orgasm") {
    $promptText = OverrideGameRequestPrompt(get_info_orgasm_prompt());
    $GLOBALS["PROMPTS"]["info_orgasm"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
    $GLOBALS["PROMPTS"]["minai_orgasm"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 