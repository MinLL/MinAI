<?php
// Translation feature for MinAI
require_once(__DIR__ . "/utils/llm_utils.php");

function handleTranslation() {
    if (!$GLOBALS['translation_enabled']) {
        return;
    }
    
    // Check if this is a translation request
    if ($GLOBALS["gameRequest"][0] !== "minai_translate" && $GLOBALS["gameRequest"][0] !== "minai_roleplay") {
        return;
    }
    
    $originalInput = isset($GLOBALS["gameRequest"][3]) ? $GLOBALS["gameRequest"][3] : "";
    $playerName = isset($GLOBALS["PLAYER_NAME"]) ? $GLOBALS["PLAYER_NAME"] : "Player";
    
    if (empty($originalInput)) {
        minai_log("error", "No input to translate");
        return;
    }
    
    minai_log("info", "Processing translation request: " . $originalInput);
    
    // Build translation prompt
    $systemPrompt = str_replace("#PLAYER_NAME#", $playerName, $GLOBALS['translation_settings']['system_prompt']);
    $translationRequest = str_replace("#ORIGINAL_INPUT#", $originalInput, $GLOBALS['translation_settings']['translation_request']);
    
    $messages = [
        ["role" => "system", "content" => $systemPrompt],
        ["role" => "user", "content" => $translationRequest]
    ];
    
    // Call LLM
    $response = callLLM($messages);
    
    if ($response && validateResponse($response)) {
        // Clean up response
        $response = trim($response, "\"' \n");
        
        // Remove any character name prefixes the LLM might have added
        $response = preg_replace('/^' . preg_quote($playerName . ':') . '\s*/', '', $response);
        $response = str_replace(["", '"'], '', $response);
        
        minai_log("info", "Translation completed: \"$originalInput\" -> \"$response\"");
        
        // Update game request with translated response
        $GLOBALS["gameRequest"][0] = "inputtext";
        $GLOBALS["gameRequest"][3] = $playerName . ": " . $response;
        
        // Trigger TTS for the translated response
        triggerPlayerTTS($response, $playerName);
        
    } else {
        minai_log("error", "Translation failed, using original input");
    }
}

function triggerPlayerTTS($text, $playerName) {
    // Get player voice type
    $voiceType = getPlayerVoiceType();
    
    // Set TTS globals
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $voiceType;
    $GLOBALS["HERIKA_NAME"] = $playerName;
    $GLOBALS["speaker"] = $playerName;
    
    minai_log("info", "Triggering TTS for player: $text (voice: $voiceType)");
}

function getPlayerVoiceType() {
    if (isset($GLOBALS['player_voice_model']) && !empty($GLOBALS['player_voice_model'])) {
        return $GLOBALS['player_voice_model'];
    }
    
    // Default fallback
    return 'femaleeventoned';
}