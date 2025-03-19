<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle moaning from touch information prompts with context
function get_info_touch_moan_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $actorName = "";
    
    if (preg_match('/^(.+?) moaned due to being touched/', $cleanedMessage, $matches)) {
        $actorName = trim($matches[1]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Determine moan description based on arousal level
    $moanDescriptions = [];
    
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $moanDescriptions = [
                "A small, muffled sound escapes around their gag as they're touched",
                "Their gagged reaction is restrained but still noticeable, their body showing subtle signs of response",
                "Despite their attempts to remain composed, the touch draws a soft sound that their gag can't completely contain"
            ];
        } else {
            $moanDescriptions = [
                "A small, hesitant sound escapes their lips at the touch",
                "Their reaction is restrained but still noticeable, their breath catching slightly",
                "Despite their attempts to remain composed, the touch draws a soft sound from them"
            ];
        }
    } elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $moanDescriptions = [
                "A clear moan of pleasure makes its way past their gag, their body responding visibly to the contact",
                "Their muffled sound has an unmistakable quality of desire, their eyes briefly fluttering closed at the sensation",
                "The touch draws a throaty sound that even their gag cannot disguise, their body tensing in response"
            ];
        } else {
            $moanDescriptions = [
                "A clear moan of pleasure escapes their lips, their body responding visibly to the contact",
                "Their sound has an unmistakable quality of desire, their eyes briefly fluttering closed at the sensation",
                "The touch draws a throaty sound from them, their body tensing in what appears to be anticipation of more"
            ];
        }
    } else {
        // High arousal
        if ($hasGag) {
            $moanDescriptions = [
                "Their gagged moan is desperate and needy, their highly sensitized body trembling at even this simple contact",
                "A sound of raw, undisguised pleasure works its way around their gag, their body arching eagerly into the touch",
                "The muffled cry that escapes them reveals just how thoroughly aroused they are, their body's response immediate and intense"
            ];
        } else {
            $moanDescriptions = [
                "Their moan is desperate and needy, their highly sensitized body trembling at even this simple contact",
                "A sound of raw, undisguised pleasure escapes them, their body arching eagerly into the touch",
                "The cry that escapes them reveals just how thoroughly aroused they are, their body's response immediate and intense"
            ];
        }
    }
    
    // Select a random description from the appropriate array
    $moanDesc = $moanDescriptions[array_rand($moanDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$actorName is touched in a sensitive area. $moanDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_touch_moan") {
    $promptText = OverrideGameRequestPrompt(get_info_touch_moan_prompt());
    $GLOBALS["PROMPTS"]["info_touch_moan"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 