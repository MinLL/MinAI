<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle nipple pinching information prompts with device context
function get_info_touch_pinch_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) pinches (.+?)\'s nipples in a vulgar manner/', $cleanedMessage, $matches)) {
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
    $hasBra = isset($deviceContext["hasBra"]) ? $deviceContext["hasBra"] : false;
    
    // Determine reaction based on context
    $reactionDescriptions = [];
    
    if ($hasBra) {
        // Reactions when wearing a restrictive bra during nipple pinching
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The bra dulls some of the sensation, though their muffled sound of discomfort is still evident behind their gag",
                    "Despite the layer of protection, they flinch at the pinch, a gagged protest following quickly",
                    "Their bound form tenses as the pinch reaches through the fabric, eyes widening at the unexpected sensation"
                ];
            } else {
                $reactionDescriptions = [
                    "The bra dulls some of the sensation, though a soft hiss of discomfort still escapes their lips",
                    "Despite the layer of protection, they flinch at the pinch, a small sound of protest following quickly",
                    "Their body tenses as the pinch reaches through the fabric, a slight frown showing their displeasure"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The bra's fabric creates an interesting friction as their nipples stiffen in response, their muffled response ambiguous",
                    "Their chest rises with quickened breathing as the pinch sends a mix of pain and something else through them, gagged sounds difficult to interpret",
                    "The restrictive material intensifies the sensation somehow, drawing a stifled sound that seems caught between pain and arousal"
                ];
            } else {
                $reactionDescriptions = [
                    "The bra's fabric creates an interesting friction as their nipples stiffen in response, their sharp intake of breath revealing complexity",
                    "Their chest rises with quickened breathing as the pinch sends a mix of pain and something else through them",
                    "The restrictive material intensifies the sensation somehow, drawing a sound that seems caught between pain and arousal"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "The combination of the rough fabric and the pinch sends waves of intense sensation through their already sensitized body, muffled moans escaping their gag",
                    "Their bound form arches into the touch despite the discomfort, the bra enhancing the stimulation in ways that draw desperate sounds from behind their gag",
                    "The bra's constriction amplifies every sensation, their gagged moans clearly conveying how thoroughly their body has converted pain to pleasure"
                ];
            } else {
                $reactionDescriptions = [
                    "The combination of the rough fabric and the pinch sends waves of intense sensation through their already sensitized body, drawing forth a raw moan",
                    "Their body arches into the touch despite the discomfort, the bra enhancing the stimulation in ways that draw desperate sounds from them",
                    "The bra's constriction amplifies every sensation, their breathless sounds clearly conveying how thoroughly their body has converted pain to pleasure"
                ];
            }
        }
    } else {
        // Reactions without bra
        if ($arousalIntensity === "low") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The direct contact with the sensitive flesh draws a sharp, muffled cry of discomfort from behind their gag",
                    "Their bound body jerks reflexively at the painful pinch, gagged protests clearly expressing their objection",
                    "They attempt to pull away from the painful stimulation, eyes narrowing as muffled sounds of displeasure escape around their gag"
                ];
            } else {
                $reactionDescriptions = [
                    "The direct contact with the sensitive flesh draws a sharp cry of discomfort",
                    "Their body jerks reflexively at the painful pinch, a protest immediately following",
                    "They attempt to pull away from the painful stimulation, a look of displeasure crossing their features"
                ];
            }
        } elseif ($arousalIntensity === "medium") {
            if ($hasGag) {
                $reactionDescriptions = [
                    "The pinch sends contrasting signals of pain and arousal through their body, their muffled response complex and difficult to interpret",
                    "Their gagged sounds shift from initial discomfort to something more ambiguous as their body begins to respond to the painful stimulation",
                    "The direct stimulation of such sensitive flesh draws a complicated reaction, their bound form shifting in ways that might be rejection or invitation"
                ];
            } else {
                $reactionDescriptions = [
                    "The pinch sends contrasting signals of pain and arousal through their body, their gasp containing notes of both protest and something else",
                    "Their response shifts from initial discomfort to something more ambiguous as their body begins to respond to the painful stimulation",
                    "The direct stimulation of such sensitive flesh draws a complicated reaction, a soft moan escaping despite what might be their conscious intentions"
                ];
            }
        } else {
            // High arousal
            if ($hasGag) {
                $reactionDescriptions = [
                    "The pain blossoms immediately into intense pleasure, their gagged moans unmistakably expressing desire rather than objection",
                    "Their already sensitized nerves transform the sharp sensation into waves of pleasure, their bound body pressing forward rather than retreating",
                    "The stiff peaks of their nipples reveal how thoroughly they're enjoying the painful attention, desperate sounds escaping around their gag"
                ];
            } else {
                $reactionDescriptions = [
                    "The pain blossoms immediately into intense pleasure, their moans unmistakably expressing desire rather than objection",
                    "Their already sensitized nerves transform the sharp sensation into waves of pleasure, their body pressing forward rather than retreating",
                    "The stiff peaks of their nipples reveal how thoroughly they're enjoying the painful attention, a breathy plea for more escaping their lips"
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
    $promptText = "$speakerName cruelly pinches $targetName's nipples$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_touch_pinch") {
    $promptText = OverrideGameRequestPrompt(get_info_touch_pinch_prompt());
    $GLOBALS["PROMPTS"]["info_touch_pinch"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 