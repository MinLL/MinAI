<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle groping information prompts with device context
function get_info_touch_grope_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) gropes (.+?) in a vulgar manner/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    $hasBelt = isset($deviceContext["hasChastityBelt"]) ? $deviceContext["hasChastityBelt"] : false;
    
    // Determine reaction based on context
    $reactionDescriptions = [];
    
    if ($hasBelt) {
        // Reactions when wearing a chastity belt during groping
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The cold metal of the chastity belt prevents any direct contact, though they still flinch with surprise, a muffled sound escaping their gag",
                    "Their gagged sound of surprise is cut short as they realize the belt protects them from the unwanted touch",
                    "Despite the belt's protection, their bound form tenses at the violation of their personal space"
                ];
            } else {
                $reactionDescriptions = [
                    "The cold metal of the chastity belt prevents any direct contact, though they still flinch with surprise at the sudden move",
                    "A small sound of surprise is cut short as they realize the belt protects them from the unwanted touch",
                    "Despite the belt's protection, they tense at the violation of their personal space"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "Though the belt prevents direct stimulation, the pressure against it sends dull waves of sensation through them, drawing a muffled groan",
                    "Their gagged sounds betray frustration as the belt blocks what their aroused state makes them crave",
                    "The belt cruelly keeps the touch at a distance while their body clearly desires more, as evidenced by their stifled sounds"
                ];
            } else {
                $reactionDescriptions = [
                    "Though the belt prevents direct stimulation, the pressure against it sends dull waves of sensation through them, drawing a frustrated sigh",
                    "Their breathing quickens despite the belt's protection, betraying how their body craves the touch it prevents",
                    "The belt cruelly keeps the touch at a distance while their body clearly desires more"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "The belt becomes an instrument of torment as it prevents the direct stimulation their body desperately craves, muffled whimpers escaping their gag",
                    "The pressure against the belt only increases their frustrated arousal, their bound form squirming helplessly seeking relief",
                    "Their gagged moans reach a desperate pitch as the belt ensures they remain stimulated yet unsatisfied"
                ];
            } else {
                $reactionDescriptions = [
                    "The belt becomes an instrument of torment as it prevents the direct stimulation their body desperately craves",
                    "The pressure against the belt only increases their frustrated arousal, their hips moving of their own accord seeking relief",
                    "A desperate whimper escapes them as the belt ensures they remain stimulated yet unsatisfied"
                ];
            }
        }
    } else {
        // Reactions without chastity belt
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The sudden invasive touch draws a muffled sound of shock and indignation from behind their gag",
                    "Their body stiffens with surprise, gagged protests clearly conveying their discomfort",
                    "They flinch away from the unwanted contact, eyes widening with alarm above their gag"
                ];
            } else {
                $reactionDescriptions = [
                    "The sudden invasive touch draws a sharp intake of breath and a look of shock",
                    "Their body stiffens with surprise, a protest clearly forming on their lips",
                    "They flinch away from the unwanted contact, face flushing with indignation"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "Their muffled response is caught somewhere between protest and involuntary pleasure",
                    "Despite their evident desire to pull away, their body betrays them with an instinctive push against the hand, gagged sounds ambiguous",
                    "The touch elicits a complex reaction their gag partially conceals, their body's response at odds with what might be their conscious desire"
                ];
            } else {
                $reactionDescriptions = [
                    "Their response is caught somewhere between protest and involuntary pleasure",
                    "Despite their evident desire to pull away, their body betrays them with an instinctive push against the hand",
                    "The touch elicits a complex reaction, a soft gasp revealing how their body has begun to respond despite themselves"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "Their body responds eagerly to the intimate touch, hips pressing forward as muffled moans escape around their gag",
                    "Whatever objections they might have had are lost to overwhelming arousal, their bound form pressing desperately against the stimulation",
                    "The touch sends waves of intense pleasure through their highly sensitized body, drawing desperate sounds that even their gag cannot fully contain"
                ];
            } else {
                $reactionDescriptions = [
                    "Their body responds eagerly to the intimate touch, hips pressing forward as a moan escapes unbidden",
                    "Whatever objections they might have had are lost to overwhelming arousal, their body pressing desperately against the stimulation",
                    "The touch sends waves of intense pleasure through their highly sensitized body, drawing sounds they seem unable to control"
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
    $promptText = "$speakerName reaches down and gropes $targetName's crotch with vulgar intent$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_touch_grope") {
    $promptText = OverrideGameRequestPrompt(get_info_touch_grope_prompt());
    $GLOBALS["PROMPTS"]["info_touch_grope"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 