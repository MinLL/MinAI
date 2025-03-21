<?php

/**
 * Dungeon Master functionality for MinAI
 * This file handles the dungeon master prompts and processing
 */

require_once("util.php");

/**
 * Set up the dungeon master prompts
 * @param string $message Optional message from the dungeon master
 */
function SetDungeonMasterPrompts($message = "") {
    minai_log("info", "Setting up dungeon master prompts: {$message}");
    
    // Base prompt for the dungeon master
    if (isset($GLOBALS['enable_prompt_slop_cleanup']) && $GLOBALS['enable_prompt_slop_cleanup']) {        
        $dungeonMasterPrompt = [
            "cue" => [
            ]
        ];
    }
    else {
        $dungeonMasterPrompt = [
            "cue" => [
                "The Narrator: You feel as if you should respond to the latest dialogue or events. {$GLOBALS["TEMPLATE_DIALOG"]}"
            ]
        ];
    }

    // Add player_request with the message if provided
    if (!empty($message)) {
        // For NPCs, we use the standard format
        $dungeonMasterPrompt["player_request"] = [
            "The Narrator: {$message}"
        ];
    } else {
        
    }
    
    // Set the prompt
    $GLOBALS["PROMPTS"]["minai_dungeon_master"] = $dungeonMasterPrompt;
}

/**
 * Process a dungeon master event
 * @param string $requestData The raw request data from the game
 */
function ProcessDungeonMasterEvent($requestData) {
    minai_log("info", "Processing dungeon master event: {$requestData}");
    
    // Check if the target is The Narrator
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
        // Set up the narrator profile
        if (function_exists('SetNarratorProfile')) {
            SetNarratorProfile();
        }
        
        minai_log("info", "Dungeon master event targeting The Narrator");
    }
    
    // Parse the message from the request data
    $message = "";
    
    // Check if this is a generic event trigger
    if (stripos($requestData, "The dungeon master has triggered an event") !== false) {
        minai_log("info", "Generic dungeon master event trigger detected");
        $message = "";
        $GLOBALS["gameRequest"][3] = "";
    }
    // Check specifically for "The dungeon master says:" prefix
    elseif (preg_match('/.*The dungeon master says:\s*(.*)$/i', $requestData, $matches)) {
        $message = $matches[1];
    }
    // Otherwise try the general pattern
    elseif (preg_match('/^.*?:\s*(.*)$/i', $requestData, $matches)) {
        $message = $matches[1];
    } else {
        $message = $requestData;
    }

    // Set up the prompts with the extracted message
    SetDungeonMasterPrompts($message);
} 