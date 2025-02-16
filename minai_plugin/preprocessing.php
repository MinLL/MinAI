<?php

require_once("util.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
InitiateDBTables();

function interceptRoleplayInput() {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && isPlayerInput() ) {
        error_log("minai: Intercepting dialogue input for Roleplay");
        error_log("minai: Original input: " . $GLOBALS["gameRequest"][3]);
        
        // Initialize local variables with global defaults
        $PLAYER_NAME = $GLOBALS["PLAYER_NAME"];
        $PLAYER_BIOS = $GLOBALS["PLAYER_BIOS"];
        $HERIKA_NAME = $GLOBALS["HERIKA_NAME"];
        $HERIKA_PERS = $GLOBALS["HERIKA_PERS"];
        $CONNECTOR = $GLOBALS["CONNECTOR"];
        $HERIKA_DYNAMIC = $GLOBALS["HERIKA_DYNAMIC"];

        // Import narrator profile which may override the above variables
        if (file_exists(GetNarratorConfigPath())) {
            error_log("minai: Using Narrator Profile");
            $path = GetNarratorConfigPath();    
            include($path);
        }
        
        SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
        
        // Get the original input and strip the player name prefix if it exists
        $originalInput = $GLOBALS["gameRequest"][3];
        
        // Extract any context information that's in parentheses
        $contextPrefix = "";
        if (preg_match('/^\((.*?)\)/', $originalInput, $matches)) {
            $contextPrefix = $matches[0];
            $originalInput = trim(substr($originalInput, strlen($contextPrefix)));
        }
        
        // Strip player name prefix if it exists
        $originalInput = preg_replace('/^' . preg_quote($PLAYER_NAME . ':') . '\s*/', '', $originalInput);
        $originalInput = trim($originalInput);

        // Get recent context - limit to last 5 exchanges for more focused context
        $lastNDataForContext = 10;
        $contextDataHistoric = DataLastDataExpandedFor("", $lastNDataForContext * -1);
        
        // Get info about location and NPCs
        $contextDataWorld = DataLastInfoFor("", -2);
        
        // Get lists of valid names and locations
        $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange())));
        $possibleLocations = DataPosibleLocationsToGo();
        
        // Combine contexts
        $contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);
        
        // Build messages array with distinct roles
        $messages = [
            // Core identity
            ['role' => 'system', 'content' => "You are {$PLAYER_NAME}, translating casual speech into your noble manner of speaking. Be brief and keep the same meaning."],
            
            // Character background
            ['role' => 'system', 'content' => "=== YOUR BACKGROUND ===\n" . $PLAYER_BIOS],
            
            // Environment context
            ['role' => 'system', 'content' => "=== NEARBY ENTITIES ===\nCharacters: " . implode(", ", $nearbyActors) . 
                "\nLocations: " . implode(", ", $possibleLocations)],
            
            // Recent conversation context
            ['role' => 'system', 'content' => "=== RECENT EVENTS ===\n" . 
                implode("\n", array_map(function($ctx) { return $ctx['content']; }, $contextDataFull))],

            // Instructions with example
            ['role' => 'system', 'content' => "Instructions:\n" .
                "1. Correct any misheard names using the nearby names list\n" .
                "2. Keep responses brief and true to the original meaning\n" .
                "Example:\n" .
                "Input: \"Hello there\"\n" .
                "Output: \"Greetings\"\n" .
                "NOT: \"Greetings, though my heart weighs heavy with...\""],

            // The actual input to translate
            ['role' => 'user', 'content' => "Translate: \"{$originalInput}\""]
        ];

        // Call LLM with specific parameters for dialogue generation
        $response = callLLM($messages, $CONNECTOR["openrouter"]["model"], [
            'temperature' => $CONNECTOR["openrouter"]["temperature"],
            'max_tokens' => 150
        ]);

        if ($response !== null) {
            // Clean up the response - remove quotes and ensure it's dialogue-ready
            $response = trim($response, "\"' \n");
            error_log("minai: Roleplay input transformed from \"{$originalInput}\" to \"{$response}\"");
            
            // Preserve the original format with context prefix and player name
            $GLOBALS["gameRequest"][3] = $contextPrefix . "{$PLAYER_NAME}:" . $response;
            error_log("minai: Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            error_log("minai: Failed to generate roleplay response, using original input");
        }

        // Disable roleplay mode after processing
        SetEnabled($PLAYER_NAME, "isRoleplaying", false);
    }
}

interceptRoleplayInput();

