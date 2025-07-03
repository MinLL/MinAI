<?php
// Minimal LLM utilities for MinAI

function callLLM($messages, $model = null) {
    try {
        // Use configured model or fall back to global connector
        if (!$model && isset($GLOBALS['CONNECTOR']['openrouter']['model'])) {
            $model = $GLOBALS['CONNECTOR']['openrouter']['model'];
        }
        
        if (!$model) {
            minai_log("error", "No model specified for LLM call");
            return null;
        }

        // Get API configuration
        if (!isset($GLOBALS['CONNECTOR']['openrouter']['url']) || 
            !isset($GLOBALS['CONNECTOR']['openrouter']['API_KEY'])) {
            minai_log("error", "Missing OpenRouter configuration");
            return null;
        }

        $url = $GLOBALS['CONNECTOR']['openrouter']['url'];
        $apiKey = $GLOBALS['CONNECTOR']['openrouter']['API_KEY'];

        // Set up headers
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}",
            "HTTP-Referer: https://www.nexusmods.com/skyrimspecialedition/mods/126330",
            "X-Title: MinAI-Minimal"
        ];

        // Prepare request data
        $data = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => 250,
            'temperature' => 0.8,
            'stream' => false
        ];

        // Make the request
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            minai_log("error", "LLM request failed");
            return null;
        }

        $response = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            minai_log("error", "Invalid JSON response: " . json_last_error_msg());
            return null;
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            minai_log("error", "Unexpected response format");
            return null;
        }

        $responseContent = trim($response['choices'][0]['message']['content']);
        minai_log("info", "LLM response received: " . strlen($responseContent) . " characters");
        
        return $responseContent;

    } catch (Exception $e) {
        minai_log("error", "LLM Error: " . $e->getMessage());
        return null;
    }
}

function validateResponse($response) {
    if (empty($response)) {
        return false;
    }
    
    // Basic validation - ensure it's not an error message
    $errorStrings = ["I cannot", "I'm sorry", "I don't", "cannot provide"];
    foreach ($errorStrings as $error) {
        if (stripos($response, $error) !== false) {
            return false;
        }
    }
    
    return true;
}