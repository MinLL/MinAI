<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle gag equipping information prompts
function get_info_device_equip_gag_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) Puts a gag on (.+?)$/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    
    // Descriptions for different gags
    $gagDescriptions = [
        "a large ball gag that forces their mouth wide open",
        "a leather panel gag that effectively silences them",
        "a ring gag that keeps their mouth helplessly open",
        "a complex harness gag with multiple straps securing it in place",
        "a simple but effective ball gag with leather straps"
    ];
    
    $gagDesc = $gagDescriptions[array_rand($gagDescriptions)];
    
    // Determine reaction based on arousal level
    $reactionDescriptions = [];
    
    if ($arousalIntensity === "low") {
        $reactionDescriptions = [
            "They make a final sound of protest before the gag silences them, their eyes communicating what their voice no longer can",
            "They resist briefly but are quickly subdued, the gag rendering their objections into meaningless sounds",
            "The gag is secured despite their reluctance, reducing their speech to muffled noises"
        ];
    } elseif ($arousalIntensity === "medium") {
        $reactionDescriptions = [
            "Their eyes betray a mix of apprehension and something less definable as the gag is secured, stifling any clear expression of their feelings",
            "The intrusion of the gag seems to trigger complex emotions, their breathing quickening as they test the limits of their new restraint",
            "A sound that might be protest but carries other undertones is cut short as the gag fills their mouth, leaving them to communicate with only eyes and body language"
        ];
    } else {
        // High arousal
        $reactionDescriptions = [
            "A moan escapes them just before the gag is secured, their eyes half-closing in what appears to be pleasure rather than distress",
            "They seem to welcome the intrusion of the gag, their body language suggesting excitement rather than resistance",
            "The gag's silencing effect seems to intensify their arousal, their body responding with visible signs of pleasure as speech is taken from them"
        ];
    }
    
    // Add helplessness context if relevant
    $helplessnessContext = "";
    if (!empty($helplessness)) {
        $helplessnessContext = ", while $helplessness";
    }
    
    // Select a random reaction from the appropriate array
    $reactionDesc = $reactionDescriptions[array_rand($reactionDescriptions)];
    
    // Create the prompt with detailed context
    $promptText = "$speakerName forces $gagDesc into $targetName's mouth, buckling it securely behind their head$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_device_equip_gag") {
    $promptText = OverrideGameRequestPrompt(get_info_device_equip_gag_prompt());
    $GLOBALS["PROMPTS"]["info_device_equip_gag"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 