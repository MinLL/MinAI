<?php
// Test script for MinAI Minimal

// Mock HerikaServer globals for testing
$GLOBALS["PLAYER_NAME"] = "TestPlayer";
$GLOBALS["CONNECTOR"] = [
    "openrouter" => [
        "model" => "google/gemma-2-9b-it:free",
        "url" => "https://openrouter.ai/api/v1/chat/completions",
        "API_KEY" => "test-key" // Replace with real key for actual testing
    ]
];

echo "Testing MinAI Minimal...\n\n";

// Include minimal MinAI
require_once(__DIR__ . "/minai_minimal/main.php");

// Test 1: Translation feature
echo "=== Test 1: Translation ===\n";
$GLOBALS["gameRequest"] = [
    "0" => "minai_translate",
    "3" => "hey what's up dude"
];

echo "Input: " . $GLOBALS["gameRequest"][3] . "\n";
processMinAIRequest();
echo "Output: " . $GLOBALS["gameRequest"][3] . "\n\n";

// Test 2: Self Narrator
echo "=== Test 2: Self Narrator ===\n";
$GLOBALS["gameRequest"] = [
    "0" => "minai_narrator", 
    "3" => "A dragon just appeared in front of me"
];

echo "Situation: " . $GLOBALS["gameRequest"][3] . "\n";
processMinAIRequest();
echo "Narrator thoughts: " . $GLOBALS["gameRequest"][3] . "\n\n";

// Test 3: API Status
echo "=== Test 3: Feature Status ===\n";
echo "Translation enabled: " . ($GLOBALS['translation_enabled'] ? "YES" : "NO") . "\n";
echo "Self narrator enabled: " . ($GLOBALS['self_narrator'] ? "YES" : "NO") . "\n";
echo "Narrator voice: " . $GLOBALS['narrator_voice'] . "\n";
echo "Player voice: " . $GLOBALS['player_voice_model'] . "\n\n";

echo "MinAI Minimal test completed.\n";
echo "Check /var/www/html/HerikaServer/log/minai_minimal.log for detailed logs.\n";