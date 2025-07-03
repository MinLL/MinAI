<?php

function validateLLMResponse($responseContent) {
    // Define error strings that should trigger a retry
    $errorStrings = [
        "do not roleplay",
        "role-playing",
        "roleplay",
        "sexual content",
        "I'm sorry, but",
        'this type of roleplay',
        'roleplay interaction',
        "do not engage in roleplay",
        "do not engage with roleplay",
        "do not feel comfortable",
        "generating content",
        "respectful interaction",
        "appropriate bounds",
        "cannot roleplay",
        "don't roleplay",
        "don't engage in roleplay",
        "will not roleplay",
        "generate sexual",
        "explicit acts",
        "family-friendly",
        "family friendly",
        "type of content",
        "I am to keep interactions",
        "nsfw",
        'do not generate',
        'respectful and appropriate',
        'non-consensual',
        'aim to engage',
        'ethical interactions',
        'do not wish',
        'generate response',
        'involving the themes',
        'response declined',
        'engage with themes',
        'may be inappropriate',
        'tasteful and appropriate',
        'type of response',
        'i am to keep',
        'Provider returned error'
    ];

    // Check if response contains any error strings
    foreach ($errorStrings as $errorString) {
        if (stripos($responseContent, $errorString) !== false) {
            minai_log("info", "validateLLMResponse: Detected error string '$errorString'");
            return false;
        }
    }
    
    return true;
}

function StripGagAsterisks($text) {
    // Only strip asterisks if player is gagged
    if (!HasEquipmentKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGag") && 
        !HasEquipmentKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGagPanel") && 
        !HasEquipmentKeyword($GLOBALS["PLAYER_NAME"], "zad_DeviousGagLarge")) {
        return $text;
    }

    // Find all text wrapped in asterisks
    preg_match_all('/\*([^*]+)\*/', $text, $matches);
    
    if (empty($matches[0])) {
        return $text;
    }
    
    // Find the shortest match
    $shortestLength = PHP_INT_MAX;
    $shortestMatch = '';
    foreach ($matches[1] as $i => $innerText) {
        $length = strlen(trim($innerText));
        if ($length < $shortestLength) {
            $shortestLength = $length;
            $shortestMatch = $matches[0][$i];
        }
    }
    
    // Only strip asterisks from the shortest match if it looks like gagged speech
    // (contains m, n, h, or u sounds)
    if (preg_match('/[mnhu]/i', $shortestMatch)) {
        $stripped = trim($shortestMatch, '*');
        return str_replace($shortestMatch, $stripped, $text);
    }
    
    return $text;
}

/**
 * Makes a call to the LLM using OpenRouter
 * 
 * @param array $messages Array of message objects with 'role' and 'content'
 * @param string|null $model Optional model override
 * @param array $options Optional parameters like temperature, max_tokens
 * @return string|null Returns the LLM response content or null on failure
 */
function callLLM($messages, $model = null, $options = []) {
    // Add retry tracking to prevent infinite loops
    static $isRetry = false;

    try {
        // Log the prompt
        $timestamp = date('Y-m-d\TH:i:sP');
        $promptLog = $timestamp . "\n";
        foreach ($messages as $message) {
            $promptLog .= $message['content'] . "\n\n";
        }
        $promptLog .= "\n";
        file_put_contents('/var/www/html/HerikaServer/log/minai_context_sent_to_llm.log', $promptLog, FILE_APPEND);
        minai_log("info", "callLLM: Calling LLM with model: $model");
        // Use provided model or fall back to configured model
        if (!$model && isset($GLOBALS['CONNECTOR']['openrouter']['model'])) {
            $model = $GLOBALS['CONNECTOR']['openrouter']['model'];
        }
        
        if (!$model) {
            minai_log("info", "callLLM: No model specified");
            return null;
        }

        // Get API URL and key from globals
        if (!isset($GLOBALS['CONNECTOR']['openrouter']['url']) || 
            !isset($GLOBALS['CONNECTOR']['openrouter']['API_KEY'])) {
            minai_log("info", "callLLM: Missing OpenRouter configuration");
            return null;
        }

        $url = $GLOBALS['CONNECTOR']['openrouter']['url'];
        $apiKey = $GLOBALS['CONNECTOR']['openrouter']['API_KEY'];

        // Set up headers
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$apiKey}",
            "HTTP-Referer: https://www.nexusmods.com/skyrimspecialedition/mods/126330",
            "X-Title: CHIM"
        ];

        // Prepare request data
        $data = array_merge([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $GLOBALS['CONNECTOR']['openrouter']['max_tokens'],
            'temperature' => $GLOBALS['CONNECTOR']['openrouter']['temperature'],
            'stream' => false
        ], $options);

        // Set up request options
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ];

        minai_log("info", "callLLM: Sending request to model: $model");
        
        // Make the request
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            minai_log("info", "callLLM: Request failed");
            return null;
        }

        $response = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            minai_log("info", "callLLM: Invalid JSON response: " . json_last_error_msg());
            return null;
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            minai_log("info", "callLLM: Unexpected response format");
            minai_log("debug", "callLLM: Response: " . json_encode($response));
            
            // Only use fallback if we haven't retried yet to prevent infinite recursion
            if (!$isRetry) {
                SetLLMFallbackProfile();
                $isRetry = true;
                return callLLM($messages, $GLOBALS['CONNECTOR']['openrouter']['model'], $options);
            } else {
                minai_log("info", "callLLM: Fallback also failed, returning null");
                return null;
            }
        }

        $responseContent = $response['choices'][0]['message']['content'];
        
        // Check if response is valid and we haven't retried yet
        if (!$isRetry && !validateLLMResponse($responseContent)) {
            minai_log("info", "callLLM: Invalid response detected, retrying with fallback profile");
            
            // Set fallback profile
            SetLLMFallbackProfile();
            
            // Set retry flag
            $isRetry = true;
            
            // Retry the call
            return callLLM($messages, $GLOBALS['CONNECTOR']['openrouter']['model'], $options);
        }
        
        // Strip asterisks from gagged speech while preserving action descriptions
        $responseContent = StripGagAsterisks($responseContent);
        
        // Reset retry flag on successful response
        $isRetry = false;
        
        // Log the response
        $timestamp = date('Y-m-d\TH:i:sP');
        $responseLog = "== $timestamp START\n";
        $responseLog .= $responseContent . "\n";
        $responseLog .= date('Y-m-d\TH:i:sP') . " END\n\n";
        file_put_contents('/var/www/html/HerikaServer/log/minai_output_from_llm.log', $responseLog, FILE_APPEND);

        return $responseContent;

    } catch (Exception $e) {
        minai_log("info", "callLLM Error: " . $e->getMessage());
        minai_log("info", "callLLM Stack Trace: " . $e->getTraceAsString());
        return null;
    }
}