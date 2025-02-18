<?php
header('Content-Type: application/json');
set_time_limit(120);
require_once("../logger.php");
// Set up paths and imports
$serverRoot = '/var/www/html/HerikaServer';
require_once($serverRoot . '/conf/conf.php');
require_once($serverRoot . '/connector/openrouter.php');

$logDir = realpath($serverRoot . '/log/');

// Function to safely get real path even for symlinks
function getSafePath($base, $path) {
    $realBase = realpath($base);
    $fullPath = realpath($base . DIRECTORY_SEPARATOR . $path);
    
    // Special handling for apache error log
    if ($path === 'apache_error.log') {
        $apacheLogPath = '/var/log/apache2/error.log';
        if (file_exists($apacheLogPath) && is_readable($apacheLogPath)) {
            return $apacheLogPath;
        }
    }
    
    if (is_link($base . DIRECTORY_SEPARATOR . $path)) {
        $fullPath = realpath(readlink($base . DIRECTORY_SEPARATOR . $path));
    }
    
    if ($fullPath === false || (strpos($fullPath, $realBase) !== 0 && $path !== 'apache_error.log')) {
        minai_log("info", "Troubleshooter: Invalid path access attempt: $path");
        return false;
    }
    
    return $fullPath;
}

// Get the latest context, output and error logs
function getLatestInteraction() {
    global $logDir;
    
    $contextPath = getSafePath($logDir, 'context_sent_to_llm.log');
    $outputPath = getSafePath($logDir, 'output_from_llm.log');
    $errorPath = getSafePath($logDir, 'apache_error.log');
    
    if (!$contextPath || !$outputPath || !$errorPath) {
        minai_log("info", "Troubleshooter: Failed to get valid paths for log files");
        return null;
    }
    
    $context = file_get_contents($contextPath);
    $output = file_get_contents($outputPath);
    
    // Get last 40 lines from error log
    $errorLines = array_slice(array_filter(explode("\n", file_get_contents($errorPath))), -40);
    $errorLog = implode("\n", $errorLines);
    
    // Get the last interaction from context
    $contextParts = array_filter(explode("\n=\n", $context));
    $lastContext = end($contextParts);
    
    // Get the last complete message from output
    $outputParts = array_filter(explode("\n==\n", $output));
    $lastOutputBlock = end($outputParts);
    
    // Extract just the JSON content (between START and END timestamps)
    if (preg_match('/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}) START\n\s*(\{.*\})\s*\n.*?(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}) END/s', 
        $lastOutputBlock, $matches)) {
        $startTime = $matches[1];
        $lastOutput = $matches[2];
        $endTime = $matches[3];
        minai_log("info", "Troubleshooter: Found interaction from $startTime to $endTime");
    } else {
        minai_log("info", "Troubleshooter: Failed to extract interaction timestamps from output");
        $lastOutput = '';
        $startTime = '';
        $endTime = '';
    }
    
    return [
        'context' => trim($lastContext),
        'output' => trim($lastOutput),
        'error_log' => $errorLog,
        'timestamps' => [
            'output_start' => $startTime,
            'output_end' => $endTime
        ]
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }
        
        $question = $data['question'] ?? '';
        $useRecommendedModel = $data['useRecommendedModel'] ?? true;
        
        if (empty($question)) {
            throw new Exception('Question is required');
        }
        
        $interaction = getLatestInteraction();
        if (!$interaction) {
            throw new Exception('Could not retrieve latest interaction');
        }
        
        minai_log("info", "Troubleshooter: Analyzing interaction - Question: $question");
        
        // Prepare the prompt for the troubleshooting LLM
        $systemPrompt = 'You are a helpful AI assistant that analyzes LLM interactions to help troubleshoot and explain the behavior of an AI system. ' . 
                       'Focus on explaining the relationship between the context sent and the output received, and help identify any issues or explain the behavior. ' .
                       'Pay attention to timestamps to understand the sequence of events. ' .
                       'Be specific and technical in your analysis. ' .
                       'Format your response using markdown with these elements:\n' .
                       '- Use ## for main section headers\n' .
                       '- Use bullet points for lists\n' .
                       '- Use `code` for technical terms or JSON keys\n' .
                       '- Use > for important notes or observations\n' .
                       '- Use **bold** for emphasis\n' .
                       'Keep your analysis well-structured and easy to read.';
        
        $userPrompt = "Here is the latest interaction with the LLM:\n\n" .
                     "Context sent to LLM:\n```\n{$interaction['context']}\n```\n\n" .
                     "Output received from LLM:\n" .
                     "Start Time: {$interaction['timestamps']['output_start']}\n" .
                     "```\n{$interaction['output']}\n```\n" .
                     "End Time: {$interaction['timestamps']['output_end']}\n\n" .
                     "Recent error logs:\n```\n{$interaction['error_log']}\n```\n\n" .
                     "Question: {$question}";
        
        $prompt = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];
        
        // Use recommended model or configured adapter
        if ($useRecommendedModel) {
            $model = 'anthropic/claude-3.5-sonnet';
            minai_log("info", "Troubleshooter: Using recommended model (Claude-3.5 Sonnet)");
        } else {
            $model = $GLOBALS['CONNECTOR']['openrouter']['model'];
            minai_log("info", "Troubleshooter: Using configured model: " . $model);
        }
        
        // Use OpenRouter to get analysis
        $url = $GLOBALS['CONNECTOR']['openrouter']['url'];
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$GLOBALS['CONNECTOR']['openrouter']['API_KEY']}",
            "HTTP-Referer: https://www.nexusmods.com/skyrimspecialedition/mods/126330",
            "X-Title: CHIM"
        ];
        
        $data = [
            'model' => $model,
            'messages' => $prompt,
            'max_tokens' => 1000,
            'temperature' => 0.3,
            'stream' => false
        ];
        
        minai_log("info", "Troubleshooter: Sending request to OpenRouter for analysis");
        
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
            throw new Exception('Failed to get analysis from OpenRouter');
        }
        
        $response = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid response from OpenRouter: ' . json_last_error_msg());
        }
        
        $analysis = $response['choices'][0]['message']['content'];
        minai_log("info", "Troubleshooter: Analysis completed successfully");
        
        echo json_encode([
            'analysis' => $analysis,
            'context' => $interaction['context'],
            'output' => $interaction['output'],
            'error_log' => $interaction['error_log'],
            'timestamps' => $interaction['timestamps']
        ]);
        
    } catch (Exception $e) {
        minai_log("info", "Troubleshooter Error: " . $e->getMessage());
        minai_log("info", "Troubleshooter Error Stack Trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Received: ' . $_SERVER['REQUEST_METHOD']]);
}
