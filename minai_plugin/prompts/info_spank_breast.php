<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle breast spanking information prompts with appropriate context
function get_info_spank_breast_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) spanks (.+?)\'s tits/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get arousal context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    
    // Check for specific devices that would affect the spanking sensation
    $hasBra = isset($deviceContext["hasBra"]) ? $deviceContext["hasBra"] : false;
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Determine reaction based on arousal and devices
    $reactionDescriptions = [];
    
    if ($hasBra) {
        // Reactions when wearing a restrictive bra during breast spanking
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The restrictive bra dulls some of the impact, but their gagged protest still comes through as the sensitive flesh is struck",
                    "Despite the bra's protection, they flinch with each strike, emitting muffled sounds of discomfort",
                    "Their eyes widen with surprise as the bra provides only minimal protection from the spanking"
                ];
            } else {
                $reactionDescriptions = [
                    "The restrictive bra dulls some of the impact, but they still hiss with discomfort as the sensitive flesh is struck",
                    "Despite the bra's protection, they flinch with each strike, their breath catching",
                    "A soft gasp escapes them as they realize the bra provides only minimal protection from the spanking"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "Each impact presses the bra's material against their sensitive nipples, drawing muffled groans that seem confused between pain and stimulation",
                    "The constrictive bra traps and amplifies the heat from each strike, their gagged sounds becoming more urgent",
                    "Their chest heaves against the restrictive bra, stifled moans suggesting the spanking is having complex effects"
                ];
            } else {
                $reactionDescriptions = [
                    "Each impact presses the bra's material against their sensitive nipples, drawing groans that seem confused between pain and stimulation",
                    "The constrictive bra traps and amplifies the heat from each strike, their breathing becoming more labored",
                    "Their chest heaves against the restrictive bra, soft moans suggesting the spanking is having complex effects"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "The bra rubs against their hardened nipples with each strike, converting pain to unmistakable pleasure behind their gag",
                    "Their bound body trembles as the combination of constriction and impact sends waves of intense sensation through them",
                    "Desperate muffled moans escape around their gag as the bra heightens every sensation from the spanking"
                ];
            } else {
                $reactionDescriptions = [
                    "The bra rubs against their hardened nipples with each strike, converting pain to unmistakable pleasure",
                    "Their body trembles as the combination of constriction and impact sends waves of intense sensation through them",
                    "Desperate moans escape them as the bra heightens every sensation from the spanking"
                ];
            }
        }
    } else {
        // Reactions without bra - more direct sensation
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The exposed flesh reddens immediately with each impact, their gagged protests rising in urgency",
                    "They squirm with evident discomfort, muffled sounds of protest coming from behind their gag",
                    "Their bound form tenses with each strike to the sensitive flesh of their exposed breasts"
                ];
            } else {
                $reactionDescriptions = [
                    "The exposed flesh reddens immediately with each impact, drawing sharp cries of discomfort",
                    "They squirm with evident discomfort, wincing as the sensitive flesh is struck",
                    "Their body tenses with each strike to the sensitive flesh of their exposed breasts"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The delicate skin flushes with heat, their muffled sounds becoming increasingly ambiguous behind the gag",
                    "Each strike seems to sensitize them further, their chest heaving as muffled groans escape",
                    "Their nipples visibly harden despite the pain, betraying a complex reaction their gagged sounds can't fully express"
                ];
            } else {
                $reactionDescriptions = [
                    "The delicate skin flushes with heat, their cries becoming increasingly ambiguous",
                    "Each strike seems to sensitize them further, their chest heaving as they struggle to process the sensations",
                    "Their nipples visibly harden despite the pain, betraying a complex reaction they seem unwilling to voice"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "Their fully exposed breasts bounce with each impact, muffled moans of unmistakable pleasure escaping their gag",
                    "The reddened flesh seems to grow more sensitive with each strike, their bound form writhing in what appears to be eagerness rather than evasion",
                    "Their hardened nipples and desperate gagged sounds betray how thoroughly their body has converted the pain to pleasure"
                ];
            } else {
                $reactionDescriptions = [
                    "Their fully exposed breasts bounce with each impact, drawing moans of unmistakable pleasure",
                    "The reddened flesh seems to grow more sensitive with each strike, their body arching in what appears to be eagerness rather than evasion",
                    "Their hardened nipples and breathless pleas betray how thoroughly their body has converted the pain to pleasure"
                ];
            }
        }
    }
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while $helplessness";
    }
    
    // Select a random reaction from the appropriate array
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$speakerName delivers a firm spanking to $targetName's breasts$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_spank_breast") {
    $promptText = OverrideGameRequestPrompt(get_info_spank_breast_prompt());
    $GLOBALS["PROMPTS"]["info_spank_breast"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 