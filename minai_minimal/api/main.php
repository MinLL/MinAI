<?php
// Minimal MinAI API endpoint
require_once(__DIR__ . "/../main.php");

// Simple API to handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        // Set up globals from request
        $GLOBALS["gameRequest"] = $input;
        
        // Extract player name if provided
        if (isset($input['player_name'])) {
            $GLOBALS["PLAYER_NAME"] = $input['player_name'];
        }
        
        // Process the request
        processMinAIRequest();
        
        // Return response
        echo json_encode([
            'status' => 'success',
            'response' => isset($GLOBALS["gameRequest"][3]) ? $GLOBALS["gameRequest"][3] : '',
            'request_type' => isset($GLOBALS["gameRequest"][0]) ? $GLOBALS["gameRequest"][0] : ''
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    }
} else {
    // Simple status endpoint
    echo json_encode([
        'status' => 'MinAI Minimal Active',
        'features' => [
            'translation' => $GLOBALS['translation_enabled'],
            'self_narrator' => $GLOBALS['self_narrator']
        ],
        'version' => '1.0-minimal'
    ]);
}