<?php
// Self narrator feature for MinAI
require_once(__DIR__ . "/utils/llm_utils.php");

function handleSelfNarrator() {
    if (!$GLOBALS['self_narrator']) {
        return;
    }
    
    // Check if this is a narrator request
    if ($GLOBALS["gameRequest"][0] !== "minai_narrator" && $GLOBALS["gameRequest"][0] !== "self_narrator") {
        return;
    }
    
    $playerName = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "Player";
    $currentSituation = isset($GLOBALS["gameRequest"][3]) ? $GLOBALS["gameRequest"][3] : "";
    
    minai_log("info", "Processing self narrator request for: " . $playerName);
    
    // Build narrator context from recent events
    $context = buildNarratorContext();
    
    // Build narrator prompt
    $systemPrompt = str_replace("#PLAYER_NAME#", $playerName, $GLOBALS['narrator_settings']['system_prompt']);
    $narratorRequest = $GLOBALS['narrator_settings']['narrator_request'];
    
    if (!empty($currentSituation)) {
        $narratorRequest .= " Recent event: " . $currentSituation;
    }
    
    if (!empty($context)) {
        $narratorRequest .= " Context: " . $context;
    }
    
    $messages = [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $narratorRequest]
    ];
    
    // Call LLM
    $response = callLLM($messages);
    
    if ($response && validateResponse($response)) {
        // Clean up response
        $response = trim($response, "\"' \n");
        
        // Remove any character name prefixes
        $response = preg_replace('/^' . preg_quote($playerName . ':') . '\s*/', '', $response);
        
        minai_log("info", "Narrator response generated: " . $response);
        
        // Set up for narrator voice
        $originalHerikaName = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : "";
        $GLOBALS["HERIKA_NAME"] = "The Narrator";
        $GLOBALS["speaker"] = "The Narrator";
        
        // Set narrator voice
        $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $GLOBALS['narrator_voice'];
        
        // Send as narrator response
        $GLOBALS["gameRequest"][0] = "inputtext";
        $GLOBALS["gameRequest"][3] = "Narrator: " . $response;
        
        minai_log("info", "Narrator response sent with voice: " . $GLOBALS['narrator_voice']);
        
        // Restore original speaker after processing
        if (!empty($originalHerikaName)) {
            $GLOBALS["HERIKA_NAME"] = $originalHerikaName;
        }
        
    } else {
        minai_log("error", "Narrator response generation failed");
    }
}

function buildNarratorContext() {
    $context = "";
    
    // Get recent game events if available
    if (isset($GLOBALS["LAST_EVENTS"]) && is_array($GLOBALS["LAST_EVENTS"])) {
        $recentEvents = array_slice($GLOBALS["LAST_EVENTS"], -3); // Last 3 events
        $context = implode(". ", $recentEvents);
    }
    
    // Add location if available
    if (isset($GLOBALS["CURRENT_LOCATION"])) {
        $context .= " Location: " . $GLOBALS["CURRENT_LOCATION"];
    }
    
    return $context;
}

function enableSelfNarrator() {
    $GLOBALS['self_narrator'] = true;
    minai_log("info", "Self narrator enabled");
}

function disableSelfNarrator() {
    $GLOBALS['self_narrator'] = false;
    minai_log("info", "Self narrator disabled");
}