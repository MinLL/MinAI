<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");
require_once(dirname(__FILE__) . "/../util.php");

// Function to handle orgasm information prompts
function get_info_orgasm_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    //$targetName = $GLOBALS["target"];
    $target = "";
    $targetName = "";
    if (preg_match('/^(.*?) just had an orgasm/', $cleanedMessage, $matches)) {
        $target = $matches[1];
    }
    if (strlen($target) > 0) {
        $targetName = $target."'s ";
    } else {
        $target = $GLOBALS["target"];
        $targetName = "";
    }
        
    // Check if actor can orgasm
    $canOrgasm = ActorCanOrgasm($target);
    
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get specific device description
    $deviceDescription = GetDeviceDescription($target);
    
    // Get additional context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 80; // Default to high arousal for orgasm
    $intensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    if (!$canOrgasm) {
        // Create denial variations when orgasm is not allowed
        $denialIntros = [
            "The intensity of {$targetName}{$deviceDescription} surges to an impossible peak, threatening to shatter their sanity",
            "{$targetName}{$deviceDescription} unleashes its full devastating power, pushing them beyond all limits",
            "Overwhelming waves of pleasure from {$targetName}{$deviceDescription} build to a maddening crescendo",
            "The relentless stimulation from {$targetName}{$deviceDescription} drives them to the very brink of consciousness"
        ];
        
        $denialReactions = [];
        if ($hasGag) {
            $denialReactions = [
                "their body convulses violently as primal, desperate screams are reduced to helpless whimpers behind their gag",
                "they thrash with such force their restraints creak ominously, their gag barely containing their desperate animal sounds",
                "their eyes roll back as they arch off the ground, muffled howls of desperate need echoing through their gag",
                "they writhe with such intensity their bonds dig deep, their gagged pleas becoming increasingly incoherent and frantic"
            ];
        } else {
            $denialReactions = [
                "their body spasms uncontrollably as they wail in desperate frustration, pleasure building far past what they thought possible",
                "they scream themselves hoarse begging for mercy as their body remains trapped at the peak of pleasure",
                "broken sounds of desperate need escape their lips as they're held suspended in an endless moment of almost-release",
                "they thrash wildly, their mind unraveling as they're forced to endure pleasure beyond their limits without relief"
            ];
        }
        
        $denialFinales = [
            "Their body remains locked in this exquisite agony, forced to endure pleasure so intense it borders on torture.",
            "The devastating stimulation continues relentlessly, proving that even the most powerful climax can be denied.",
            "Their consciousness fragments under the assault of sensation, yet release remains forever just out of reach.",
            "The endless peak of pleasure becomes both paradise and hell, a reminder that their body is no longer their own."
        ];
        
        $promptText = $denialIntros[array_rand($denialIntros)] . ", but something deep within prevents them from finding release. ";
        $promptText .= $denialReactions[array_rand($denialReactions)];
        
        if (!empty($helplessness)) {
            $promptText .= ", while remaining completely $helplessness";
        }
        
        $promptText .= ". " . $denialFinales[array_rand($denialFinales)];
        
        return "The Narrator: " . $promptText;
    }
    
    // Create intro variations for orgasm control - reworded to work without a speaker
    $orgasmIntros = [
        "The intensity of {$targetName}{$deviceDescription} suddenly turns to maximum",
        "The full power of {$targetName}{$deviceDescription} is unleashed",
        "{$targetName}{$deviceDescription} activates at its highest setting",
        "Intense pulses surge through {$targetName}{$deviceDescription}"
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
if ($GLOBALS["gameRequest"][0] == "info_orgasm" || $GLOBALS["gameRequest"][0] == "minai_orgasm") {
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