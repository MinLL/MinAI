<?php
/**
 * MinAI Metrics Export Tool
 * 
 * This script exports metrics data to various formats for external analysis.
 * Usage: php export_metrics.php [format] [timeRange] [outputFile]
 *   format: csv, json (default: json)
 *   timeRange: 1h, 24h, 7d, 30d, all (default: all)
 *   outputFile: path to output file (default: minai_metrics_export.[format])
 * 
 * Example: php export_metrics.php csv 7d metrics_weekly.csv
 */

require_once("logger.php");
require_once("utils/metrics_util.php");

// Process command line arguments
$format = isset($argv[1]) ? strtolower($argv[1]) : 'json';
$timeRange = isset($argv[2]) ? strtolower($argv[2]) : 'all';
$outputFile = isset($argv[3]) ? $argv[3] : null;

// Validate format
if (!in_array($format, ['csv', 'json'])) {
    echo "Error: Invalid format. Use 'csv' or 'json'.\n";
    exit(1);
}

// Default output file if not specified
if (!$outputFile) {
    $outputFile = "minai_metrics_export.{$format}";
}

// Convert time range to seconds
$timeRangeSeconds = [
    '1h' => 3600,
    '24h' => 86400,
    '7d' => 604800,
    '30d' => 2592000,
    'all' => PHP_INT_MAX
][$timeRange] ?? PHP_INT_MAX;

// Calculate cutoff time
$cutoffTime = time() - $timeRangeSeconds;

// Get metrics file path
$metricsFile = isset($GLOBALS['minai_metrics_file']) 
    ? $GLOBALS['minai_metrics_file'] 
    : "/var/www/html/HerikaServer/log/minai_metrics.jsonl";

// Read metrics data
$metrics = [];
try {
    if (file_exists($metricsFile)) {
        $handle = fopen($metricsFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $metric = json_decode($line, true);
                if ($metric && isset($metric['timestamp']) && $metric['timestamp'] > $cutoffTime) {
                    $metrics[] = $metric;
                }
            }
            fclose($handle);
        } else {
            echo "Error: Unable to open metrics file.\n";
            exit(1);
        }
    } else {
        echo "Error: Metrics file not found.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error reading metrics file: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($metrics)) {
    echo "No metrics data found for the specified time range.\n";
    exit(0);
}

// Export based on format
switch ($format) {
    case 'json':
        exportJson($metrics, $outputFile);
        break;
    case 'csv':
        exportCsv($metrics, $outputFile);
        break;
}

echo "Exported " . count($metrics) . " metrics to {$outputFile}\n";
exit(0);

/**
 * Export metrics as JSON
 * 
 * @param array $metrics Metrics data
 * @param string $outputFile Output file path
 */
function exportJson($metrics, $outputFile) {
    file_put_contents($outputFile, json_encode($metrics, JSON_PRETTY_PRINT));
}

/**
 * Export metrics as CSV
 * 
 * @param array $metrics Metrics data
 * @param string $outputFile Output file path
 */
function exportCsv($metrics, $outputFile) {
    // Collect all possible fields from the metrics
    $headers = ['type', 'timestamp', 'component', 'duration', 'request_type', 'actor_name'];
    
    // Add any additional fields found in the metrics
    foreach ($metrics as $metric) {
        foreach ($metric as $key => $value) {
            if (!in_array($key, $headers) && !is_array($value)) {
                $headers[] = $key;
            }
        }
    }
    
    // Write CSV
    $file = fopen($outputFile, 'w');
    
    // Write headers
    fputcsv($file, $headers);
    
    // Write data
    foreach ($metrics as $metric) {
        $row = [];
        foreach ($headers as $header) {
            if (isset($metric[$header]) && !is_array($metric[$header])) {
                $row[] = $metric[$header];
            } else {
                $row[] = '';
            }
        }
        fputcsv($file, $row);
    }
    
    fclose($file);
} 