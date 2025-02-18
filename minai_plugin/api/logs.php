<?php
header('Content-Type: application/json');
set_time_limit(120); // Increase timeout to 2 minutes
require_once("../logger.php");

$logDir = realpath('/var/www/html/HerikaServer/log/');

// Function to safely get real path even for symlinks
function getSafePath($base, $path) {
    $realBase = realpath($base);
    $fullPath = realpath($base . DIRECTORY_SEPARATOR . $path);
    
    // If the file is a symlink, get the real path
    if (is_link($base . DIRECTORY_SEPARATOR . $path)) {
        $fullPath = realpath(readlink($base . DIRECTORY_SEPARATOR . $path));
    }
    
    // Check if the path is within allowed directories
    if ($fullPath === false || 
        (strpos($fullPath, $realBase) !== 0 && 
         strpos($fullPath, '/var/log/apache2') !== 0)) {
        return false;
    }
    
    return $fullPath;
}

// Get list of log files if requested
if (isset($_GET['list'])) {
    $files = glob($logDir . DIRECTORY_SEPARATOR . '*.log');
    $logFiles = [];
    
    foreach ($files as $file) {
        if (is_readable($file) || is_readable(readlink($file))) {
            $filename = basename($file);
            $realPath = getSafePath($logDir, $filename);
            
            if ($realPath && is_readable($realPath)) {
                $logFiles[] = [
                    'name' => $filename,
                    'size' => filesize($realPath),
                    'modified' => filemtime($realPath),
                    'readable' => true
                ];
            }
        }
    }
    
    echo json_encode(['files' => $logFiles]);
    exit;
}

// Add download handler
if (isset($_GET['download'])) {
    $requestedFile = $_GET['download'];
    
    // Use safe path resolution
    $requestedPath = getSafePath($logDir, $requestedFile);
    if ($requestedPath === false || !preg_match('/\.log$/', $requestedPath)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
    
    if (!file_exists($requestedPath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Log file not found']);
        exit;
    }
    
    // Set headers for download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . basename($requestedPath) . '"');
    header('Content-Length: ' . filesize($requestedPath));
    
    // Output file contents
    readfile($requestedPath);
    exit;
}

$requestedFile = $_GET['file'] ?? '';

// Use safe path resolution
$requestedPath = getSafePath($logDir, $requestedFile);
if ($requestedPath === false || !preg_match('/\.log$/', $requestedPath)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$logPath = $logDir . DIRECTORY_SEPARATOR . $requestedFile;

if (!file_exists($logPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Log file not found']);
    exit;
}

// Get file position from request (for incremental updates)
$lastPosition = isset($_GET['position']) ? intval($_GET['position']) : 0;
$currentPosition = filesize($logPath);

// If no new content, return empty with current position
if ($lastPosition >= $currentPosition) {
    echo json_encode([
        'content' => '',
        'position' => $currentPosition
    ]);
    exit;
}

// For initial load or full refresh, get last 1000 lines
if ($lastPosition === 0) {
    $lines = [];
    $fp = fopen($logPath, 'r');
    if ($fp === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not open log file']);
        exit;
    }
    
    // More efficient way to read last N lines
    $buffer = 4096;
    $lineCount = 0;
    $data = '';
    
    fseek($fp, -1, SEEK_END);
    while (ftell($fp) > 0 && $lineCount < 1000) {
        $offset = min(ftell($fp), $buffer);
        fseek($fp, -$offset, SEEK_CUR);
        $chunk = fread($fp, $offset);
        $data = $chunk . $data;
        fseek($fp, -strlen($chunk), SEEK_CUR);
        
        $lineCount += substr_count($chunk, "\n");
    }
    
    fclose($fp);
    
    $lines = array_slice(explode("\n", $data), -1000);
    
    echo json_encode([
        'content' => implode("\n", $lines),
        'position' => $currentPosition
    ]);
} else {
    // For incremental updates, read only new content
    $fp = fopen($logPath, 'r');
    fseek($fp, $lastPosition);
    $newContent = fread($fp, $currentPosition - $lastPosition);
    fclose($fp);
    
    echo json_encode([
        'content' => $newContent,
        'position' => $currentPosition
    ]);
}
