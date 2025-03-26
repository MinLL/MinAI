<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle gag removal information prompts
function get_info_device_remove_gag_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) removes a Gag from (.+?)$/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $targetName = trim($matches[2]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    
    // Determine reaction based on arousal level and context
    $reactionDescriptions = [];
    
    if ($arousalIntensity === "low") {
        $reactionDescriptions = [
            "They work their jaw carefully once freed, relief evident in their expression",
            "A deep breath and a quiet 'thank you' follow the gag's removal, their voice slightly hoarse from disuse",
            "They immediately test their newfound freedom of speech with a few experimental sounds, clearly grateful for the release"
        ];
    } elseif ($arousalIntensity === "medium") {
        $reactionDescriptions = [
            "A soft sound somewhere between relief and disappointment escapes them as the gag is removed",
            "They seem momentarily uncertain how to react to their returned voice, their expression a complex mixture of feelings",
            "Their first words after removal are slightly breathless, suggesting the gag had effects beyond mere silencing"
        ];
    } else {
        // High arousal
        $reactionDescriptions = [
            "A moan escapes their lips the moment they're freed, revealing how the gag had been part of their aroused state",
            "Their breathing is heavy and labored after removal, hints of disappointment mingling with relief in their expression",
            "They seem almost reluctant to be freed from the silencing restraint, their first words decidedly sensual in tone"
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
    $promptText = "$speakerName unbuckles and removes the gag from $targetName's mouth$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_device_remove_gag") {
    $promptText = OverrideGameRequestPrompt(get_info_device_remove_gag_prompt());
    $GLOBALS["PROMPTS"]["info_device_remove_gag"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 