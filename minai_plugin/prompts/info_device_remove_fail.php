<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle failed device removal information prompts
function get_info_device_remove_fail_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names and device info from the message
    $speakerName = "";
    $targetName = "";
    $deviceType = "";
    
    if (preg_match('/^(.+?) tried, but was unable to remove a (.+?) from (.+?)$/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $deviceType = trim($matches[2]);
        $targetName = trim($matches[3]);
    } elseif (preg_match('/^(.+?) tried, but was unable to remove the (.+?) from (.+?)$/', $cleanedMessage, $matches)) {
        $speakerName = trim($matches[1]);
        $deviceType = trim($matches[2]);
        $targetName = trim($matches[3]);
    }
    
    $target = $GLOBALS["target"];
    $deviceContext = GetInfoDeviceContext($target);
    
    // Get relevant context
    $arousal = isset($deviceContext["arousal"]) ? $deviceContext["arousal"] : 50;
    $arousalIntensity = GetReactionIntensity($arousal);
    $helplessness = isset($deviceContext["helplessness"]) ? $deviceContext["helplessness"] : "";
    $hasGag = isset($deviceContext["hasGag"]) ? $deviceContext["hasGag"] : false;
    
    // Determine device type for specific descriptions
    $deviceDescriptions = [
        "The device resists all attempts at removal, its locks remaining stubbornly secure",
        "Despite their best efforts, the locking mechanism refuses to yield",
        "The secure fastening of the device proves impossible to overcome without the proper key",
        "Their attempts to manipulate the locks prove futile, the device remaining firmly in place",
        "The complex security mechanisms of the device successfully thwart all removal attempts"
    ];
    
    $deviceDesc = $deviceDescriptions[array_rand($deviceDescriptions)];
    
    // Determine reaction based on arousal level
    $reactionDescriptions = [];
    
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $reactionDescriptions = [
                "A muffled sound of frustration escapes their gag as it becomes clear the device won't be removed",
                "Their eyes show clear disappointment as the removal attempt fails, their bound form tensing with resignation",
                "The failed attempt draws a stifled sigh through their gag, their expression conveying obvious displeasure"
            ];
        } else {
            $reactionDescriptions = [
                "A sound of frustration escapes them as it becomes clear the device won't be removed",
                "Their expression shows clear disappointment as the removal attempt fails",
                "The failed attempt draws a sigh of resignation, their words expressing obvious displeasure"
            ];
        }
    } elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $reactionDescriptions = [
                "Their reaction to the failed removal seems oddly mixed, their muffled sounds and body language difficult to interpret clearly",
                "A complex emotion flashes across their face as the attempt fails, their gagged response suggesting conflicted feelings",
                "Their bound form shifts in what might be either disappointment or something else entirely as the device remains secure"
            ];
        } else {
            $reactionDescriptions = [
                "Their reaction to the failed removal seems oddly mixed, a complexity in their expression suggesting conflicted feelings",
                "A subtle emotion flashes across their face as the attempt fails, their response difficult to interpret clearly",
                "Their body shifts in what might be either disappointment or something else entirely as the device remains secure"
            ];
        }
    } else {
        // High arousal
        if ($hasGag) {
            $reactionDescriptions = [
                "What might be relief shows in their eyes as the removal attempt fails, their gagged sounds taking on an almost pleased quality",
                "Their bound form practically trembles with what appears to be excitement rather than disappointment at the failed attempt",
                "A muffled sound that seems suspiciously like satisfaction escapes their gag as the device remains firmly in place"
            ];
        } else {
            $reactionDescriptions = [
                "What might be relief shows in their eyes as the removal attempt fails, their breathing taking on an almost pleased quality",
                "Their body practically trembles with what appears to be excitement rather than disappointment at the failed attempt",
                "A sound that seems suspiciously like satisfaction escapes them as the device remains firmly in place"
            ];
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
    $promptText = "$speakerName attempts to remove the $deviceType from $targetName but finds it impossible$helplessnessContext. $deviceDesc. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_device_remove_fail") {
    $promptText = OverrideGameRequestPrompt(get_info_device_remove_fail_prompt());
    $GLOBALS["PROMPTS"]["info_device_remove_fail"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 