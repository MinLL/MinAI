<?php
// Integration file for HerikaServer
// Replace the original minai_plugin include with this file

// Only load if HerikaServer globals are available
if (!isset($GLOBALS["gameRequest"])) {
    return;
}

// Include minimal MinAI
require_once(__DIR__ . "/main.php");

// Hook into HerikaServer processing
// This replaces the original preprocessing.php inclusion
function integrateMinAI() {
    // Check if we have a valid request
    if (!isset($GLOBALS["gameRequest"]) || !is_array($GLOBALS["gameRequest"])) {
        return;
    }
    
    // Set up player name from HerikaServer if available
    if (isset($GLOBALS["PLAYER_NAME"])) {
        // Already set by HerikaServer
    } elseif (isset($_GET["player_name"])) {
        $GLOBALS["PLAYER_NAME"] = $_GET["player_name"];
    } else {
        $GLOBALS["PLAYER_NAME"] = "Player"; // Default fallback
    }
    
    // Process MinAI features
    processMinAIRequest();
}

// Execute integration
integrateMinAI();