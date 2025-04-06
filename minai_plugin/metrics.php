<?php
/**
 * MinAI Metrics Dashboard Backend
 * 
 * This file processes and serves metrics data to the metrics dashboard.
 */

// Set to true to enable detailed debug logging for metrics calculations
define('MINAI_METRICS_DEBUG', false);

require_once("logger.php");
require_once("db_utils.php");
require_once("utils/metrics_util.php");

// Set content type to JSON
header('Content-Type: application/json');

// Check for action parameter
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    // Handle clear metrics request
    $metricsFile = isset($GLOBALS['minai_metrics_file']) 
        ? $GLOBALS['minai_metrics_file'] 
        : "/var/www/html/HerikaServer/log/minai_metrics.jsonl";
    
    try {
        $clearedFiles = [];
        
        // Clear main metrics file
        if (file_exists($metricsFile)) {
            // Clear the file by opening it with 'w' mode
            file_put_contents($metricsFile, '');
            $clearedFiles[] = $metricsFile;
        }
        
        if (count($clearedFiles) > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cleared ' . count($clearedFiles) . ' metrics files: ' . implode(', ', $clearedFiles)
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No metrics files found to clear.'
            ]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error clearing metrics: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Process query parameters
$timeRange = isset($_GET['timeRange']) ? $_GET['timeRange'] : '24h';
$component = isset($_GET['component']) ? $_GET['component'] : 'all';
$requestType = isset($_GET['requestType']) ? $_GET['requestType'] : 'all';
$actor = isset($_GET['actor']) ? $_GET['actor'] : 'all';

// Convert time range to seconds
$timeRangeSeconds = [
    '1h' => 3600,
    '24h' => 86400,
    '7d' => 604800,
    '30d' => 2592000
][$timeRange] ?? 86400;

// Calculate cutoff time
$cutoffTime = time() - $timeRangeSeconds;

// Read metrics data from file
$metricsFile = isset($GLOBALS['minai_metrics_file']) 
    ? $GLOBALS['minai_metrics_file'] 
    : "/var/www/html/HerikaServer/log/minai_metrics.jsonl";

$metrics = [];
try {
    // Get a list of all available log files (main file and rotated files)
    $logFiles = [];
    
    // Add the main file if it exists
    if (file_exists($metricsFile)) {
        $logFiles[] = $metricsFile;
    }
    
    // Find rotated log files (they follow pattern: filename.1, filename.2, etc.)
    $dir = dirname($metricsFile);
    $baseFilename = basename($metricsFile);
    for ($i = 1; $i <= 5; $i++) { // Check up to 5 rotated files
        $rotatedFile = $dir . '/' . $baseFilename . '.' . $i;
        if (file_exists($rotatedFile)) {
            $logFiles[] = $rotatedFile;
        }
    }
    
    // Set maximum metrics to load to avoid excessive memory usage
    $maxMetricsToLoad = 10000;
    
    // Process the most recent files first (main file, then .1, .2, etc.)
    foreach ($logFiles as $file) {
        // Stop if we've already loaded enough metrics
        if (count($metrics) >= $maxMetricsToLoad) {
            minai_log("info", "Reached maximum number of metrics, stopping file processing");
            break;
        }
        
        $handle = fopen($file, 'r');
        if ($handle) {
            // Flag to determine if this file has any records newer than cutoff
            $hasRecentRecords = false;
            $oldestRecordTime = PHP_INT_MAX;
            
            // First scan first and last line to check if file contains data in our time range
            // This optimization helps skip entire files if they're too old
            $firstLine = fgets($handle);
            if ($firstLine) {
                $firstMetric = json_decode($firstLine, true);
                if ($firstMetric && isset($firstMetric['timestamp'])) {
                    $oldestRecordTime = $firstMetric['timestamp'];
                }
                
                // If the newest record in this file is already older than cutoff,
                // we can skip the entire file
                if ($oldestRecordTime < $cutoffTime) {
                    // Go to end of file to check the latest record
                    fseek($handle, -2048, SEEK_END); // Go back 2KB from end
                    $buffer = "";
                    while (!feof($handle)) {
                        $buffer .= fread($handle, 1024);
                    }
                    
                    // Get the last complete line
                    $lines = explode("\n", $buffer);
                    $lastLine = "";
                    for ($i = count($lines) - 1; $i >= 0; $i--) {
                        if (trim($lines[$i]) !== "") {
                            $lastLine = $lines[$i];
                            break;
                        }
                    }
                    
                    if ($lastLine) {
                        $lastMetric = json_decode($lastLine, true);
                        if ($lastMetric && isset($lastMetric['timestamp']) && $lastMetric['timestamp'] < $cutoffTime) {
                            // This entire file is too old, skip it
                            fclose($handle);
                            continue;
                        }
                    }
                }
            }
            
            // Reset file pointer to beginning
            rewind($handle);
            
            // Process file line by line
            while (($line = fgets($handle)) !== false) {
                $metric = json_decode($line, true);
                if ($metric && isset($metric['timestamp'])) {
                    // Skip metrics older than cutoff time
                    if ($metric['timestamp'] < $cutoffTime) {
                        continue;
                    }
                    
                    // Filter by component if needed
                    if ($component !== 'all' && 
                        (!isset($metric['component']) || $metric['component'] !== $component)) {
                        continue;
                    }
                    
                    // Filter by request type if needed
                    if ($requestType !== 'all' && 
                        (!isset($metric['request_type']) || $metric['request_type'] !== $requestType)) {
                        continue;
                    }
                    
                    // Filter by actor if needed
                    if ($actor !== 'all' && 
                        (!isset($metric['actor_name']) || $metric['actor_name'] !== $actor)) {
                        continue;
                    }
                    
                    $metrics[] = $metric;
                    $hasRecentRecords = true;
                    
                    // Stop if we've reached the maximum limit
                    if (count($metrics) >= $maxMetricsToLoad) {
                        minai_log("info", "Reached maximum number of metrics within file, stopping processing");
                        break;
                    }
                }
            }
            
            fclose($handle);
        }
    }
} catch (Exception $e) {
    minai_log("error", "Error reading metrics files: " . $e->getMessage());
}

// Helper function to determine the depth of a component
function determineComponentDepth($component) {
    // Define known hierarchy of components
    $hierarchyMap = [
        // Level 1 - Top level components (Entry Points)
        'total_processing' => 1,
        'globals_php' => 1,
        'preprocessing_php' => 1, 
        'prerequest_php' => 1,
        'functions_php' => 1,
        'context_php' => 1,
        'pre_llm_total' => 1,
        
        // Level 2 - Major subsystems
        'system_prompt_builder' => 2,
        'context_builder' => 2,
        'preprocessing' => 2,
        'llm_request' => 2,
        'set_narrator_profile' => 2,
        'set_narrator_profile_2' => 2,
        'set_devious_narrator' => 2,
        'prompt_slop_cleanup' => 2,
        
        // Level 3 - Functional components
        'section_build_' => 3, // All section builders
        'section_build_character' => 3,
        'section_build_status' => 3,
        'section_build_interaction' => 3,
        'section_build_environment' => 3,
        'section_build_misc' => 3,
        'expand_decorators' => 3,
        'process_actions' => 3,
        'validate_output' => 3,
        
        // Level 4 - Implementation details
        'context_builder_' => 4 // All specific context builders start with this
    ];
    
    // Check for direct matches first
    if (isset($hierarchyMap[$component])) {
        return $hierarchyMap[$component];
    }
    
    // Check for prefix matches
    foreach ($hierarchyMap as $prefix => $depth) {
        // Skip exact match keys that end with _ (we'll handle prefixes separately)
        if (substr($prefix, -1) === '_' && strpos($component, $prefix) === 0) {
            return $depth;
        }
    }
    
    // Some additional heuristics based on naming patterns
    if (preg_match('/^process_/', $component)) {
        return 3; // All processing functions are level 3
    }
    
    if (preg_match('/^build_/', $component)) {
        return 3; // All builder functions are level 3
    }
    
    if (preg_match('/_total$/', $component)) {
        return 1; // Components ending with _total are level 1 rollups
    }
    
    if (preg_match('/_php$/', $component)) {
        return 1; // Components ending with _php are entry points
    }
    
    // Default depth based on some common patterns
    if (strpos($component, 'validate') !== false || strpos($component, 'check') !== false) {
        return 3; // Validation functions
    }
    
    // Default depth
    return 2; // Most components are level 2 if not explicitly categorized
}

// Collect unique request types and actors for filters
$uniqueRequestTypes = [];
$uniqueActors = [];
foreach ($metrics as $metric) {
    if (isset($metric['request_type']) && $metric['request_type'] !== 'unknown') {
        $uniqueRequestTypes[$metric['request_type']] = true;
    }
    if (isset($metric['actor_name']) && $metric['actor_name'] !== 'unknown') {
        $uniqueActors[$metric['actor_name']] = true;
    }
}

// Process timer metrics
$timerMetrics = array_filter($metrics, function($metric) {
    return isset($metric['type']) && $metric['type'] === 'timer';
});

// Group timer metrics by component
$componentTimes = [];
$componentDepths = [];
foreach ($timerMetrics as $metric) {
    if (isset($metric['component']) && isset($metric['duration'])) {
        $componentName = $metric['component'];
        if (!isset($componentTimes[$componentName])) {
            $componentTimes[$componentName] = [
                'total' => 0,
                'count' => 0
            ];
            
            // Determine the component's hierarchy depth
            $componentDepths[$componentName] = determineComponentDepth($componentName);
        }
        $componentTimes[$componentName]['total'] += $metric['duration'];
        $componentTimes[$componentName]['count']++;
    }
}

// Calculate averages
foreach ($componentTimes as $component => $data) {
    $componentTimes[$component]['average'] = $data['total'] / $data['count'];
}

// Sort by average time (descending)
uasort($componentTimes, function($a, $b) {
    return $b['average'] <=> $a['average'];
});

// Prepare component times chart data
$componentTimesChartData = [
    'labels' => [],
    'values' => [],
    'depths' => []
];

// Ensure all components have a proper depth value
foreach ($componentTimes as $component => $data) {
    // If depth wasn't already determined, determine it now
    if (!isset($componentDepths[$component])) {
        $componentDepths[$component] = determineComponentDepth($component);
    }
}

// Group components by depth
$componentsByDepth = [
    1 => [],
    2 => [],
    3 => [],
    4 => []
];

// Organize components by depth
foreach ($componentTimes as $component => $data) {
    $depth = $componentDepths[$component];
    $componentsByDepth[$depth][] = [
        'name' => $component,
        'average' => $data['average'],
        'count' => $data['count']
    ];
}

// Sort components within each depth by average time
foreach ($componentsByDepth as $depth => $components) {
    usort($componentsByDepth[$depth], function($a, $b) {
        return $b['average'] <=> $a['average'];
    });
}

// Add components to chart data in depth order (top level first)
foreach ([1, 2, 3, 4] as $depth) {
    foreach ($componentsByDepth[$depth] as $comp) {
        $componentTimesChartData['labels'][] = $comp['name'];
        $componentTimesChartData['values'][] = $comp['average'];
        $componentTimesChartData['depths'][$comp['name']] = $depth;
    }
}

// Get unique session timestamps to count actual server requests
$uniqueSessionStarts = array_filter($metrics, function($metric) {
    return isset($metric['type']) && $metric['type'] === 'session_start';
});
$requestCount = count($uniqueSessionStarts);

// Prepare request type distribution chart data
$requestTypeCounts = [];
foreach ($uniqueSessionStarts as $metric) {
    if (isset($metric['request_type'])) {
        $type = $metric['request_type'];
        if (!isset($requestTypeCounts[$type])) {
            $requestTypeCounts[$type] = 0;
        }
        $requestTypeCounts[$type]++;
    }
}

// Sort by count (descending)
arsort($requestTypeCounts);

// Prepare request types chart data
$requestTypesChartData = [
    'labels' => [],
    'values' => []
];
foreach ($requestTypeCounts as $type => $count) {
    $requestTypesChartData['labels'][] = $type;
    $requestTypesChartData['values'][] = $count;
}

// Prepare time series data for each component
$timeSeriesData = [
    'datasets' => []
];

// Collect unique components
$uniqueComponents = array_keys($componentTimes);

// Create a dataset for each component
foreach ($uniqueComponents as $componentName) {
    $dataPoints = [];
    
    // Collect data points for this component
    foreach ($timerMetrics as $metric) {
        if (isset($metric['component']) && $metric['component'] === $componentName &&
            isset($metric['timestamp']) && isset($metric['duration'])) {
            $dataPoints[] = [
                'x' => $metric['timestamp'] * 1000, // Convert to milliseconds for Chart.js
                'y' => $metric['duration']
            ];
        }
    }
    
    // Sort by timestamp
    usort($dataPoints, function($a, $b) {
        return $a['x'] <=> $b['x'];
    });
    
    // Add to datasets
    $timeSeriesData['datasets'][] = [
        'label' => $componentName,
        'data' => $dataPoints
    ];
}

// Group timer metrics by actor
$actorTimes = [];
foreach ($timerMetrics as $metric) {
    if (isset($metric['actor_name']) && isset($metric['duration'])) {
        $actorName = $metric['actor_name'];
        if (!isset($actorTimes[$actorName])) {
            $actorTimes[$actorName] = [
                'total' => 0,
                'count' => 0
            ];
        }
        $actorTimes[$actorName]['total'] += $metric['duration'];
        $actorTimes[$actorName]['count']++;
    }
}

// Calculate averages
foreach ($actorTimes as $actor => $data) {
    $actorTimes[$actor]['average'] = $data['total'] / $data['count'];
}

// Sort by average time (descending)
uasort($actorTimes, function($a, $b) {
    return $b['average'] <=> $a['average'];
});

// Prepare actor times chart data
$actorTimesChartData = [
    'labels' => [],
    'values' => []
];
foreach ($actorTimes as $actor => $data) {
    $actorTimesChartData['labels'][] = $actor;
    $actorTimesChartData['values'][] = $data['average'];
}

// Process system metrics
$systemMetrics = [
    'memory' => [],
    'cpu' => [],
    'disk' => []
];

$systemMemoryUsage = 0;

foreach ($metrics as $metric) {
    if (isset($metric['type']) && ($metric['type'] === 'session_start' || $metric['type'] === 'session_end') &&
        isset($metric['system']) && isset($metric['timestamp'])) {
        
        $system = $metric['system'];
        $timestamp = $metric['timestamp'];
        
        // Memory usage
        if (isset($system['memory_usage'])) {
            $systemMetrics['memory'][] = [
                'timestamp' => $timestamp,
                'value' => $system['memory_usage']
            ];
        }
        
        // CPU load
        if (isset($system['cpu_load'])) {
            $systemMetrics['cpu'][] = [
                'timestamp' => $timestamp,
                'value' => $system['cpu_load']
            ];
        }
        
        // Disk usage
        if (isset($system['disk_used_percent'])) {
            $systemMetrics['disk'][] = [
                'timestamp' => $timestamp,
                'value' => $system['disk_used_percent']
            ];
            $systemMemoryUsage = $system['disk_used_percent'];
        }
    }
}

// Sort system metrics by timestamp
foreach ($systemMetrics as $key => $data) {
    usort($systemMetrics[$key], function($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });
}

// Process entry points data
$entrypointMetrics = [
    'globals' => 0,
    'preprocessing' => 0,
    'prerequest' => 0,
    'functions' => 0,
    'context' => 0,
    'prellm' => 0
];

// Calculate average times for each entry point
$entrypointCounts = [
    'globals' => 0,
    'preprocessing' => 0,
    'prerequest' => 0,
    'functions' => 0,
    'context' => 0,
    'prellm' => 0
];

// Map between component names and entry point keys
$entrypointMap = [
    'globals_php' => 'globals',
    'preprocessing_php' => 'preprocessing',
    'prerequest_php' => 'prerequest',
    'functions_php' => 'functions',
    'context_php' => 'context',
    'pre_llm_total' => 'prellm'
];

foreach ($timerMetrics as $metric) {
    if (isset($metric['component']) && isset($metric['duration'])) {
        $component = $metric['component'];
        
        // Check if this is an entry point component
        if (isset($entrypointMap[$component])) {
            $entryKey = $entrypointMap[$component];
            $entrypointMetrics[$entryKey] += $metric['duration'];
            $entrypointCounts[$entryKey]++;
        }
    }
}

// Calculate averages
foreach ($entrypointMetrics as $key => $total) {
    if ($entrypointCounts[$key] > 0) {
        $entrypointMetrics[$key] = $total / $entrypointCounts[$key];
    }
}

// Calculate summary metrics
$summaryMetrics = [
    'avgPromptTime' => 0,
    'avgTotalTime' => 0,
    'requestCount' => $requestCount,
    'peakMemory' => 0,
    'systemMemoryUsage' => $systemMemoryUsage
];

// Find system prompt builder metrics
$promptBuilderMetrics = array_filter($timerMetrics, function($metric) {
    return isset($metric['component']) && $metric['component'] === 'system_prompt_builder';
});

// Calculate average system prompt build time
if (count($promptBuilderMetrics) > 0) {
    $totalPromptTime = array_sum(array_column($promptBuilderMetrics, 'duration'));
    $summaryMetrics['avgPromptTime'] = $totalPromptTime / count($promptBuilderMetrics);
}

// Calculate average total processing time
$totalMetrics = array_filter($timerMetrics, function($metric) {
    return isset($metric['component']) && $metric['component'] === 'total_processing';
});

// Debug function to log hierarchy data
function logDebugInfo($message, $data = null) {
    minai_log("info", "$message: " . json_encode($data, JSON_PRETTY_PRINT));
}

if (count($totalMetrics) > 0) {
    $totalTime = array_sum(array_column($totalMetrics, 'duration'));
    $summaryMetrics['avgTotalTime'] = $totalTime / count($totalMetrics);
    logDebugInfo("Using explicit total_processing time", $summaryMetrics['avgTotalTime']);
} else {
    // Calculate total processing time based on component hierarchy without double-counting
    
    // Step 1: Build a tree structure from the hierarchy
    $componentTree = [];
    $allComponents = [];
    
    // Initialize component nodes 
    // Replace with proper null check on $hierarchy
    if (!empty($hierarchy) && is_array($hierarchy)) {
        foreach ($hierarchy as $component => $data) {
            if (!isset($allComponents[$component])) {
                $allComponents[$component] = [
                    'name' => $component,
                    'parent' => $data['parent'],
                    'children' => [],
                    'time' => isset($componentTimes[$component]) ? $componentTimes[$component]['average'] : 0,
                    'processed' => false
                ];
            }
        }
    }
    
    // Build parent-child relationships
    foreach ($allComponents as $component => $node) {
        if ($node['parent'] && isset($allComponents[$node['parent']])) {
            $allComponents[$node['parent']]['children'][] = $component;
        } else {
            // Root nodes (no parent or parent not in our dataset)
            $componentTree[] = $component;
        }
    }
    
    logDebugInfo("Component tree root nodes", $componentTree);
    logDebugInfo("All components structure", array_slice($allComponents, 0, 5)); // Log just a few components
    
    // Step 2: Calculate total time without double-counting
    $totalComponentTime = 0;
    $requestCount = max(1, $requestCount); // Avoid division by zero
    
    // Function to sum only non-overlapping time
    $processComponentTime = function($componentName) use (&$allComponents, &$processComponentTime, &$logDebugInfo) {
        $component = $allComponents[$componentName];
        
        // Skip if already processed
        if ($component['processed']) {
            return 0;
        }
        
        $allComponents[$componentName]['processed'] = true;
        
        // If this is a leaf node (no children), return its time
        if (empty($component['children'])) {
            logDebugInfo("Leaf component time", ["component" => $componentName, "time" => $component['time']]);
            return $component['time'];
        }
        
        // Get time for all children
        $childrenTime = 0;
        foreach ($component['children'] as $childName) {
            $childTime = $processComponentTime($childName);
            $childrenTime += $childTime;
            logDebugInfo("Child component time", ["parent" => $componentName, "child" => $childName, "time" => $childTime]);
        }
        
        // If component time is greater than sum of children,
        // it means there's own processing time not accounted by children
        $ownTime = max(0, $component['time'] - $childrenTime);
        $totalTime = $childrenTime + $ownTime;
        
        logDebugInfo("Component total time", [
            "component" => $componentName, 
            "own_time" => $ownTime,
            "children_time" => $childrenTime,
            "total" => $totalTime
        ]);
        
        return $totalTime;
    };
    
    // Calculate time starting from root components
    foreach ($componentTree as $rootComponent) {
        $compTime = $processComponentTime($rootComponent);
        $totalComponentTime += $compTime;
        logDebugInfo("Root component processed", ["component" => $rootComponent, "time" => $compTime, "running_total" => $totalComponentTime]);
    }
    
    // As a fallback, if no time calculated, use sum of top-level components
    if ($totalComponentTime <= 0) {
        logDebugInfo("No time calculated from tree, using entry points fallback");
        
        // Reset processed flags
        foreach ($allComponents as $name => $component) {
            $allComponents[$name]['processed'] = false;
        }
        
        // Filter for top level (entry point) components
        $entryPointComponents = array_filter(array_keys($componentDepths), function($comp) use ($componentDepths) {
            return $componentDepths[$comp] === 1;
        });
        
        logDebugInfo("Entry point components", $entryPointComponents);
        
        foreach ($entryPointComponents as $component) {
            if (isset($allComponents[$component])) {
                $compTime = $processComponentTime($component);
                $totalComponentTime += $compTime;
                logDebugInfo("Entry point processed", ["component" => $component, "time" => $compTime, "running_total" => $totalComponentTime]);
            } elseif (isset($componentTimes[$component])) {
                $compTime = $componentTimes[$component]['average'];
                $totalComponentTime += $compTime;
                logDebugInfo("Entry point time added (not in hierarchy)", ["component" => $component, "time" => $compTime, "running_total" => $totalComponentTime]);
            }
        }
    }
    
    $summaryMetrics['avgTotalTime'] = $totalComponentTime;
    logDebugInfo("Final total processing time", $summaryMetrics['avgTotalTime']);
}

// Find peak memory usage
foreach ($metrics as $metric) {
    if (isset($metric['system']) && isset($metric['system']['memory_peak'])) {
        $summaryMetrics['peakMemory'] = max($summaryMetrics['peakMemory'], $metric['system']['memory_peak']);
    }
}

// Prepare raw data for table (add depth information)
$rawData = array_slice($timerMetrics, 0, 100); // Limit to 100 entries

// Sort raw data by component depth and then by timestamp
usort($rawData, function($a, $b) {
    // First get component names (default to empty string if not set)
    $compA = isset($a['component']) ? $a['component'] : '';
    $compB = isset($b['component']) ? $b['component'] : '';
    
    // Get depths
    $depthA = determineComponentDepth($compA);
    $depthB = determineComponentDepth($compB);
    
    // First sort by depth
    if ($depthA != $depthB) {
        return $depthA - $depthB;
    }
    
    // If same depth, sort by component name
    if ($compA != $compB) {
        return strcmp($compA, $compB);
    }
    
    // If same component, sort by timestamp (newest first)
    return $b['timestamp'] - $a['timestamp'];
});

// Add depth and category info to each item
foreach ($rawData as &$item) {
    if (isset($item['component'])) {
        $depth = determineComponentDepth($item['component']);
        $item['depth'] = $depth;
        
        // Add category label based on depth
        switch ($depth) {
            case 1:
                $item['category'] = 'Entry Point';
                break;
            case 2:
                $item['category'] = 'Major Component';
                break;
            case 3:
                $item['category'] = 'Sub-Component';
                break;
            case 4:
                $item['category'] = 'Implementation';
                break;
            default:
                $item['category'] = 'Other';
        }
    } else {
        $item['depth'] = 1; // Default depth
        $item['category'] = 'Unknown';
    }
}

// Also include component counts by level in the response
$levelCounts = [
    1 => count($componentsByDepth[1] ?? []),
    2 => count($componentsByDepth[2] ?? []),
    3 => count($componentsByDepth[3] ?? []),
    4 => count($componentsByDepth[4] ?? [])
];

// Build hierarchy relationships based on actual parent_component data from metrics
$hierarchy = [];

// First initialize all components with their depths
foreach ($componentDepths as $component => $depth) {
    $hierarchy[$component] = [
        'depth' => $depth,
        'parent' => null
    ];
}

// Then extract actual parent-child relationships from the metrics data
foreach ($timerMetrics as $metric) {
    if (isset($metric['component']) && isset($metric['parent_component'])) {
        $component = $metric['component'];
        $parentComponent = $metric['parent_component'];
        
        // Make sure component exists in hierarchy
        if (!isset($hierarchy[$component])) {
            $depth = determineComponentDepth($component);
            $hierarchy[$component] = [
                'depth' => $depth,
                'parent' => null
            ];
        }
        
        // Set parent relationship if parent component exists
        if ($parentComponent && $parentComponent !== $component) {
            // Make sure parent exists in hierarchy
            if (!isset($hierarchy[$parentComponent])) {
                $parentDepth = determineComponentDepth($parentComponent);
                $hierarchy[$parentComponent] = [
                    'depth' => $parentDepth,
                    'parent' => null
                ];
            }
            
            // Set the parent
            $hierarchy[$component]['parent'] = $parentComponent;
        }
    }
}

// Ensure depth relationships make sense (parent should have lower depth than child)
foreach ($hierarchy as $component => $data) {
    $parent = $data['parent'];
    if ($parent && isset($hierarchy[$parent])) {
        // If parent has higher or equal depth, adjust parent's depth
        if ($hierarchy[$parent]['depth'] >= $data['depth']) {
            $hierarchy[$parent]['depth'] = $data['depth'] - 1;
            // Ensure depth doesn't go below 1
            $hierarchy[$parent]['depth'] = max(1, $hierarchy[$parent]['depth']);
        }
    }
}

// Prepare response data
$response = [
    'summary' => $summaryMetrics,
    'componentTimes' => $componentTimesChartData,
    'requestTypes' => $requestTypesChartData,
    'timeSeries' => $timeSeriesData,
    'actorTimes' => $actorTimesChartData,
    'systemMetrics' => $systemMetrics,
    'entrypoints' => $entrypointMetrics,
    'rawData' => $rawData,
    'filters' => [
        'requestTypes' => array_keys($uniqueRequestTypes),
        'actors' => array_keys($uniqueActors)
    ],
    'levelCounts' => $levelCounts,
    'hierarchy' => $hierarchy
];

// Return JSON response
echo json_encode($response); 