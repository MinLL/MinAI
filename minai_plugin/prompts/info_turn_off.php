<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle turn off vibrator information prompts
function get_info_turn_off_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message if possible
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) turns off (.+?)\'s remote vibrator/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
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
    
    // Create intro variations for turning off
    $turnOffIntros = [
        "deactivates $targetName's $deviceDescription",
        "turns off $targetName's $deviceDescription",
        "shuts down $targetName's $deviceDescription",
        "stops the stimulation from $targetName's $deviceDescription"
    ];
    
    $turnOffIntro = $turnOffIntros[array_rand($turnOffIntros)];
    
    // Create transition variations
    $transitionPhrases = [
        "ceasing all sensations",
        "bringing the stimulation to an abrupt halt",
        "instantly stopping all vibrations",
        "cutting off the pleasurable pulses"
    ];
    
    $transitionPhrase = $transitionPhrases[array_rand($transitionPhrases)];
    
    // Create appropriate ending descriptions based on arousal intensity
    $endingDescriptions = [];
    
    // Very high arousal reactions - extreme frustration at denial
    if ($arousalIntensity === "very_high") {
        if ($hasGag) {
            $endingDescriptions = [
                "leaving them making desperate muffled cries of frustration as they squirm helplessly, on the very edge of climax with no release",
                "causing them to emit strangled sounds of protest that penetrate their gag, their body trembling violently with unresolved need",
                "their gagged form thrashing desperately against their bonds, eyes wide with desperate, unfulfilled hunger",
                "their body quivering with denied release, muffled sounds of begging escaping around the edges of their gag"
            ];
        } else {
            $endingDescriptions = [
                "leaving them making desperate sounds of frustration, openly begging for the vibrations to resume",
                "causing them to cry out in anguished disappointment, their body still on the very edge of climax",
                "their body trembling violently with unsatisfied need as they plead incoherently for more",
                "a desperate, frustrated sob escaping their lips as they're cruelly denied their imminent release"
            ];
        }
    }
    // High arousal reactions - significant frustration
    elseif ($arousalIntensity === "high") {
        if ($hasGag) {
            $endingDescriptions = [
                "drawing a long, muffled groan of mingled relief and frustration from behind their gag",
                "their breathing still heavy and labored as they slump against their restraints, making frustrated sounds",
                "their body still visibly aroused and tense despite the end of stimulation, soft protests clear despite the gag",
                "causing a stifled sound of disappointment as the building pleasure is abruptly cut short"
            ];
        } else {
            $endingDescriptions = [
                "drawing a long groan of mingled relief and frustration as they grit their teeth",
                "their breathing still heavy and uneven as they struggle to regain composure",
                "their body still visibly aroused and tense despite the end of stimulation",
                "causing a clear sound of disappointment as they're left without satisfaction"
            ];
        }
    }
    // Medium arousal reactions - mild disappointment
    elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $endingDescriptions = [
                "drawing a muffled sigh that could be relief or mild disappointment",
                "their breathing gradually steadying behind the gag as the distraction fades",
                "their posture relaxing slightly though residual tension remains visible in their form",
                "causing a soft sound behind their gag as the pleasant sensations fade"
            ];
        } else {
            $endingDescriptions = [
                "drawing a sigh that might be relief or mild disappointment",
                "their breathing gradually steadying as the distraction recedes",
                "their posture relaxing though some residual tension remains visible",
                "causing a quiet 'hmm' of response as the pleasant sensations fade"
            ];
        }
    }
    // Low arousal reactions - mostly relief
    else {
        if ($hasGag) {
            $endingDescriptions = [
                "their tension visibly easing as the distracting sensations cease",
                "a muffled sound of relief escaping through their gag as they regain focus",
                "their posture relaxing completely as the unwanted stimulation finally ends",
                "the distraction of the vibrations gone, allowing them to return their attention elsewhere"
            ];
        } else {
            $endingDescriptions = [
                "their tension visibly easing as the distracting sensations cease",
                "a soft sound of relief escaping their lips as they regain their composure",
                "their posture relaxing completely as the unwanted stimulation finally ends",
                "a quiet 'thank you' escaping as the distraction of the vibrations finally ends"
            ];
        }
    }
    
    $endingDesc = $endingDescriptions[array_rand($endingDescriptions)];
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = " while still $helplessness";
    }
    
    // Create the prompt with detailed context and natural flow
    $promptText = "$speakerName $turnOffIntro, $transitionPhrase. ";
    $promptText .= "The stimulation abruptly stops$helplessnessContext, $endingDesc";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_turn_off") {
    $promptText = OverrideGameRequestPrompt(get_info_turn_off_prompt());
    $GLOBALS["PROMPTS"]["info_turn_off"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 