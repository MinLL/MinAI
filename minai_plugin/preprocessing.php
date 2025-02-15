<?php

require_once("util.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
InitiateDBTables();

function interceptRoleplayInput() {
    
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && isPlayerInput() ) {
        error_log("minai: Intercepting dialogue input for Roleplay");
        error_log("minai: Original input: " . $GLOBALS["gameRequest"][3]);
        
        // SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
        
        // Get the original input and strip the player name prefix if it exists
        $originalInput = $GLOBALS["gameRequest"][3];
        
        // Extract any context information that's in parentheses
        $contextPrefix = "";
        if (preg_match('/^\((.*?)\)/', $originalInput, $matches)) {
            $contextPrefix = $matches[0]; // Keep the full context with parentheses
            $originalInput = trim(substr($originalInput, strlen($contextPrefix)));
        }
        
        // Strip player name prefix if it exists
        $originalInput = preg_replace('/^' . preg_quote($GLOBALS["PLAYER_NAME"] . ':') . '\s*/', '', $originalInput);
        $originalInput = trim($originalInput);
        
        // Construct the system prompt using player bio and personality
        $systemPrompt = "You are a translator converting casual speech into {$GLOBALS["PLAYER_NAME"]}'s unique way of speaking. ";
        if (isset($GLOBALS["PLAYER_BIO"]) && !empty($GLOBALS["PLAYER_BIO"])) {
            $systemPrompt .= "Character background: {$GLOBALS["PLAYER_BIO"]} ";
        }
        if (isset($GLOBALS["HERIKA_PERS"]) && !empty($GLOBALS["HERIKA_PERS"])) {
            $systemPrompt .= "Speaking style: {$GLOBALS["HERIKA_PERS"]} ";
        }
        $systemPrompt .= "Transform the player's casual input into how their character would naturally express the same sentiment, maintaining the character's unique personality and speaking style. Keep the same core meaning but express it in the character's voice. Provide only the translated dialogue without any explanation.";

        // Construct the user prompt with context
        $userPrompt = "Convert this casual speech into {$GLOBALS["PLAYER_NAME"]}'s way of speaking: \"{$originalInput}\"";

        // Set up messages for LLM
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        // Call LLM with specific parameters for dialogue generation
        $response = callLLM($messages, null, [
            'temperature' => 0.7,
            'max_tokens' => 150
        ]);

        if ($response !== null) {
            // Clean up the response - remove quotes and ensure it's dialogue-ready
            $response = trim($response, "\"' \n");
            error_log("minai: Roleplay input transformed from \"{$originalInput}\" to \"{$response}\"");
            
            // Preserve the original format with context prefix and player name
            $GLOBALS["gameRequest"][3] = $contextPrefix . "{$GLOBALS["PLAYER_NAME"]}:" . $response;
            error_log("minai: Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            error_log("minai: Failed to generate roleplay response, using original input");
        }

        // Disable roleplay mode after processing
        SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
    }
}

interceptRoleplayInput();

