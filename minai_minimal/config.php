<?php
// Minimal MinAI Configuration
// Only self narrator and translation features

// Basic settings
$GLOBALS['minai_enabled'] = true;
$GLOBALS['self_narrator'] = true;
$GLOBALS['translation_enabled'] = true;

// Voice settings for narrator
$GLOBALS['narrator_voice'] = "dragon";

// Player settings
$GLOBALS['player_voice_model'] = "femaleeventoned"; // Default voice

// Translation settings
$GLOBALS['translation_settings'] = array(
    "context_messages" => 10,
    "system_prompt" => "You are #PLAYER_NAME#. TRANSLATION MODE: Convert casual speech into how your character would say the same thing while keeping the original meaning.",
    "translation_request" => "TRANSLATE this casual speech into your character's manner while keeping the same meaning: \"#ORIGINAL_INPUT#\""
);

// Self narrator settings
$GLOBALS['narrator_settings'] = array(
    "system_prompt" => "You are #PLAYER_NAME#. Respond with your internal thoughts and reactions to what's happening. Express genuine emotions and feelings about the current situation.",
    "narrator_request" => "Think to yourself about the current situation. What are your honest thoughts and feelings about what just happened?"
);

// Logging
function minai_log($level, $message) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp][$level] $message\n";
    error_log($logMessage, 3, "/var/www/html/HerikaServer/log/minai_minimal.log");
}

minai_log("info", "Minimal MinAI loaded");