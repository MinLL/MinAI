<?php
require_once(dirname(__FILE__) . "/info_device_utils.php");

// Function to handle collar equipping information prompts
function get_info_device_equip_collar_prompt() {
    $cleanedMessage = GetCleanedMessage();
    
    // Extract names from the message
    $speakerName = "";
    $targetName = "";
    
    if (preg_match('/^(.+?) locked a collar on (.+?)$/', $cleanedMessage, $matches)) {
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
    
    // Descriptions for different reactions
    $collarDescriptions = [
        "a heavy leather collar with a metal ring at the front",
        "a sleek black collar with intricate silver designs",
        "a sturdy collar with a small lock at the back",
        "an ornate collar with a prominently displayed locking mechanism",
        "a thick leather collar with metal reinforcements"
    ];
    
    $collarDesc = $collarDescriptions[array_rand($collarDescriptions)];
    
    // Determine reaction based on arousal level
    $reactionDescriptions = [];
    
    if ($arousalIntensity === "low") {
        if ($hasGag) {
            $reactionDescriptions = [
                "Their eyes widen slightly as the collar tightens around their neck, a muffled sound of surprise escaping their gag",
                "They remain still as the collar is secured, though their gagged breathing quickens noticeably",
                "A brief flinch and muffled protest comes from behind their gag as the collar locks firmly in place"
            ];
        } else {
            $reactionDescriptions = [
                "Their eyes widen slightly as the collar tightens around their neck, a soft intake of breath marking the moment",
                "They remain still as the collar is secured, though their breathing quickens noticeably",
                "A brief flinch and murmured word mark the moment the collar locks firmly in place"
            ];
        }
    } elseif ($arousalIntensity === "medium") {
        if ($hasGag) {
            $reactionDescriptions = [
                "A muffled sound that might be protest or something else entirely escapes their gag as the collar tightens around their throat",
                "Their pulse visibly quickens at the collar's touch, gagged sounds suggesting a complex reaction to their new restraint",
                "The collar's weight against their neck draws a stifled sound of what could be interpreted as reluctant excitement"
            ];
        } else {
            $reactionDescriptions = [
                "A soft sound that might be protest or something else entirely escapes their lips as the collar tightens around their throat",
                "Their pulse visibly quickens at the collar's touch, a slight flush suggesting a complex reaction to their new restraint",
                "The collar's weight against their neck draws a soft gasp of what could be interpreted as reluctant excitement"
            ];
        }
    } else {
        // High arousal
        if ($hasGag) {
            $reactionDescriptions = [
                "The collar's firm pressure against their sensitive neck draws an unmistakable moan of pleasure from behind their gag",
                "Their bound form trembles visibly as the collar locks in place, gagged sounds clearly expressing arousal rather than objection",
                "The symbolism of the collar seems to intensify their already heightened state, their gagged moans leaving little doubt about their reaction"
            ];
        } else {
            $reactionDescriptions = [
                "The collar's firm pressure against their sensitive neck draws an unmistakable moan of pleasure from their lips",
                "Their body trembles visibly as the collar locks in place, the sounds they make clearly expressing arousal rather than objection",
                "The symbolism of the collar seems to intensify their already heightened state, their breathy sighs leaving little doubt about their reaction"
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
    $promptText = "$speakerName secures $collarDesc around $targetName's neck, locking it firmly in place$helplessnessContext. $reactionDesc.";
    
    return "The Narrator: " . $promptText;
}

// Register the prompt only if this specific prompt is requested
if ($GLOBALS["gameRequest"][0] == "info_device_equip_collar") {
    $promptText = OverrideGameRequestPrompt(get_info_device_equip_collar_prompt());
    $GLOBALS["PROMPTS"]["info_device_equip_collar"] = [
        "cue"=>[],
        "player_request"=>[$promptText]
    ];
} 