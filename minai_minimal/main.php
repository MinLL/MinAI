<?php
// Minimal MinAI - Main entry point
// Only includes self narrator and translation features

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/translation.php");
require_once(__DIR__ . "/narrator.php");

// Main processing function
function processMinAIRequest() {
    if (!$GLOBALS['minai_enabled']) {
        return;
    }
    
    minai_log("info", "Processing MinAI request: " . $GLOBALS["gameRequest"][0]);
    
    // Handle translation requests
    if (in_array($GLOBALS["gameRequest"][0], ["minai_translate", "minai_roleplay"])) {
        handleTranslation();
        return;
    }
    
    // Handle narrator requests
    if (in_array($GLOBALS["gameRequest"][0], ["minai_narrator", "self_narrator"])) {
        handleSelfNarrator();
        return;
    }
    
    // Check for automatic translation if enabled
    if ($GLOBALS['translation_enabled'] && $GLOBALS["gameRequest"][0] === "inputtext") {
        $input = isset($GLOBALS["gameRequest"][3]) ? $GLOBALS["gameRequest"][3] : "";
        
        // Simple check for casual input that might need translation
        if (needsTranslation($input)) {
            $GLOBALS["gameRequest"][0] = "minai_translate";
            handleTranslation();
        }
    }
}

function needsTranslation($input) {
    // Simple heuristics to detect casual speech
    $casualIndicators = [
        "gonna", "wanna", "hey", "yo", "ok", "okay", "yeah", "nah", 
        "wtf", "omg", "lol", "brb", "sup", "what's up"
    ];
    
    $lowerInput = strtolower($input);
    foreach ($casualIndicators as $indicator) {
        if (strpos($lowerInput, $indicator) !== false) {
            return true;
        }
    }
    
    return false;
}

// Initialize
minai_log("info", "Minimal MinAI initialized");

// Process current request
processMinAIRequest();