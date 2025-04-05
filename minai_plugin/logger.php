<?php
include_once("/var/www/html/HerikaServer/lib/logger.php");
function minai_log($level, $message, $logFile = 'minai.log') {
    // Ensure level is lowercase for consistency
    $level = strtolower($level);
    
    // Get timestamp in ISO 8601 format
    $timestamp = date('Y-m-d\TH:i:sP');
    
    // Format the log entry
    $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
    
    // Construct the full path
    $logPath = "/var/www/html/HerikaServer/log/{$logFile}";
    
    // Append to log file
    file_put_contents($logPath, $logEntry, FILE_APPEND);
}