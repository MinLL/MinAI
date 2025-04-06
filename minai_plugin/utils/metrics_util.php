<?php
/**
 * MinAI Metrics System
 * 
 * This utility provides functionality to track, store, and export performance metrics
 * for the MinAI plugin. It captures execution times, resource usage, and contextual 
 * information to help analyze system performance.
 */

require_once(__DIR__ . "/../logger.php");

class MinAIMetrics {
    private static $instance = null;
    private $metricsEnabled = true;
    private $metricsData = [];
    private $timers = [];
    private $metricsFile = "/var/www/html/HerikaServer/log/minai_metrics.jsonl";
    private $samplingRate = 1.0; // 1.0 = 100% sampling rate
    private $samplingThreshold = null;
    private $sessionStarted = false;
    private $requestType = 'unknown';
    private $actorName = 'unknown';
    private $sessionData = [];
    private $entryPointTimers = [
        'globals_php' => false,
        'preprocessing_php' => false,
        'prerequest_php' => false,
        'functions_php' => false,
        'context_php' => false,
        'pre_llm_total' => false
    ];
    private $activeTimers = []; // Track currently active timers for hierarchy tracking
    private $logRotated = false; // Flag to track if rotation has occurred in this request
    private $maxLogSizeBytes = 50 * 1024 * 1024; // 50MB default max log size
    private $maxLogFiles = 5; // Number of rotated log files to keep
    
    /**
     * Get the singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new MinAIMetrics();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize request type and actor name from globals
        if (isset($GLOBALS["gameRequest"][0])) {
            $this->requestType = $GLOBALS["gameRequest"][0];
        }
        
        if (isset($GLOBALS["HERIKA_NAME"])) {
            $this->actorName = $GLOBALS["HERIKA_NAME"];
        }
        
        // Initialize sampling threshold based on sampling rate
        $this->samplingThreshold = $this->samplingRate * getrandmax();
        
        // Load configuration from globals if available
        if (isset($GLOBALS['minai_metrics_enabled'])) {
            $this->metricsEnabled = (bool)$GLOBALS['minai_metrics_enabled'];
        }
        
        if (isset($GLOBALS['minai_metrics_file'])) {
            $this->metricsFile = $GLOBALS['minai_metrics_file'];
        }
        
        if (isset($GLOBALS['minai_metrics_sampling_rate'])) {
            $this->samplingRate = (float)$GLOBALS['minai_metrics_sampling_rate'];
            $this->samplingThreshold = $this->samplingRate * getrandmax();
        }
        
        // Register shutdown function to ensure metrics are saved
        register_shutdown_function([$this, 'saveMetrics']);
        
        // Record session start with context and system metrics
        $this->startSession();
    }
    
    /**
     * Determines if the current request should be sampled
     * 
     * @return bool Whether to sample this request
     */
    private function shouldSample() {
        // Always sample if rate is 1.0 (100%)
        if ($this->samplingRate >= 1.0) {
            return true;
        }
        
        // Skip sampling if rate is 0
        if ($this->samplingRate <= 0) {
            return false;
        }
        
        // Use consistent sampling for the same request
        if ($this->samplingThreshold === null) {
            $this->samplingThreshold = $this->samplingRate * getrandmax();
        }
        
        return (rand(0, getrandmax()) < $this->samplingThreshold);
    }
    
    /**
     * Records the session start with system metrics
     */
    private function startSession() {
        if (!$this->metricsEnabled || !$this->shouldSample()) {
            return;
        }
        
        // Record basic system metrics
        $systemMetrics = $this->getSystemMetrics();
        
        $this->sessionStarted = true;
        $this->logSessionEvent('session_start');
    }
    
    /**
     * Gets system metrics like memory, CPU, and disk usage
     * 
     * @return array System metrics data
     */
    private function getSystemMetrics() {
        $metrics = [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ];
        
        // Add CPU usage if available
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['cpu_load'] = $load[0];
        }
        
        // Get disk usage if able
        $logDir = dirname($this->metricsFile);
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            try {
                $metrics['disk_free'] = disk_free_space($logDir);
                $metrics['disk_total'] = disk_total_space($logDir);
                $metrics['disk_used_percent'] = 100 - ($metrics['disk_free'] / $metrics['disk_total'] * 100);
            } catch (Exception $e) {
                // Ignore if we can't get disk metrics
            }
        }
        
        return $metrics;
    }
    
    /**
     * Starts a timer for measuring execution time
     * 
     * @param string $name Name of the timer
     * @param string|null $parentComponent Name of the parent component (for hierarchy)
     */
    public function startTimer($name, $parentComponent = null) {
        if (!$this->metricsEnabled || !$this->shouldSample()) {
            return;
        }
        
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'parent' => $parentComponent
        ];
        
        // Add to active timers stack for automatic parent tracking
        $this->activeTimers[] = $name;
    }
    
    /**
     * Stops a timer and records the metrics
     * 
     * @param string $name Name of the timer
     * @param array $additionalData Additional data to record with the timer
     * @return float|null Duration in seconds or null if timer not found/disabled
     */
    public function stopTimer($name, $additionalData = []) {
        if (!$this->metricsEnabled || !$this->shouldSample() || !isset($this->timers[$name])) {
            return;
        }
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $timerData = $this->timers[$name];
        
        $duration = $endTime - $timerData['start'];
        $memoryDiff = $endMemory - $timerData['memory_start'];
        
        // Get request context
        $requestType = isset($GLOBALS["gameRequest"][0]) ? $GLOBALS["gameRequest"][0] : 'unknown';
        $actorName = isset($GLOBALS["HERIKA_NAME"]) ? $GLOBALS["HERIKA_NAME"] : 'unknown';
        
        // Get parent component if available
        $parentComponent = isset($timerData['parent']) ? $timerData['parent'] : null;
        
        // Record the metric
        $metricData = array_merge([
            'component' => $name,
            'duration' => $duration,
            'memory_diff' => $memoryDiff,
            'timestamp' => $endTime,
            'request_type' => $requestType,
            'actor_name' => $actorName
        ], $additionalData);
        
        // Add parent component if available
        if ($parentComponent !== null) {
            $metricData['parent_component'] = $parentComponent;
        }
        
        $this->recordMetric('timer', $metricData);
        
        // Remove the timer
        unset($this->timers[$name]);
        
        // Remove from active timers
        if (($key = array_search($name, $this->activeTimers)) !== false) {
            array_splice($this->activeTimers, $key, 1);
        }
        
        return $duration;
    }
    
    /**
     * Records a general metric
     * 
     * @param string $type Type of metric
     * @param array $data Metric data
     */
    public function recordMetric($type, $data) {
        if (!$this->metricsEnabled || !$this->shouldSample()) {
            return;
        }
        
        $this->metricsData[] = array_merge([
            'type' => $type,
            'timestamp' => microtime(true)
        ], $data);
    }
    
    /**
     * Saves the collected metrics to disk
     */
    public function saveMetrics() {
        if (!$this->metricsEnabled || empty($this->metricsData)) {
            return;
        }
        
        try {
            // Add final system metrics
            $this->recordMetric('session_end', [
                'timestamp' => microtime(true),
                'system' => $this->getSystemMetrics()
            ]);
            
            // Ensure directory exists
            $dir = dirname($this->metricsFile);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Check if log rotation is needed (only once per request)
            $this->checkAndRotateLog();
            
            // Append each metric as a JSON line
            $handle = fopen($this->metricsFile, 'a');
            if ($handle) {
                foreach ($this->metricsData as $metric) {
                    fwrite($handle, json_encode($metric) . "\n");
                }
                fclose($handle);
            }
            
            // Clear the metrics data
            $this->metricsData = [];
            
        } catch (Exception $e) {
            minai_log("error", "Failed to save metrics: " . $e->getMessage());
        }
    }
    
    /**
     * Checks if log rotation is needed and performs rotation if necessary.
     * This will run at most once per request.
     */
    private function checkAndRotateLog() {
        // Skip if already rotated in this request
        if ($this->logRotated) {
            return;
        }
        
        // Check if file exists and exceeds max size
        if (file_exists($this->metricsFile) && filesize($this->metricsFile) > $this->maxLogSizeBytes) {
            $this->rotateLogFile();
            $this->logRotated = true;
        }
    }
    
    /**
     * Rotates the log file by renaming existing files and creating a new one.
     * Ensures metrics in rotated files are sorted by timestamp for efficient querying.
     */
    private function rotateLogFile() {
        $baseFile = $this->metricsFile;
        $dir = dirname($baseFile);
        $filename = basename($baseFile);
        
        // Sort the metrics in the current file by timestamp before rotation
        // This ensures that when reading from oldest to newest files, we get proper chronological order
        if (file_exists($baseFile) && filesize($baseFile) > 0) {
            try {
                // Read the current file
                $metrics = [];
                $handle = fopen($baseFile, 'r');
                if ($handle) {
                    while (($line = fgets($handle)) !== false) {
                        $metric = json_decode($line, true);
                        if ($metric) {
                            $metrics[] = $metric;
                        }
                    }
                    fclose($handle);
                    
                    // Sort metrics by timestamp (newest first)
                    usort($metrics, function($a, $b) {
                        if (!isset($a['timestamp']) || !isset($b['timestamp'])) {
                            return 0;
                        }
                        return $b['timestamp'] <=> $a['timestamp']; // Descending order
                    });
                    
                    // Write back to a temporary file
                    $tempFile = $baseFile . '.tmp';
                    $tempHandle = fopen($tempFile, 'w');
                    if ($tempHandle) {
                        foreach ($metrics as $metric) {
                            fwrite($tempHandle, json_encode($metric) . "\n");
                        }
                        fclose($tempHandle);
                        
                        // Replace the original file with the sorted one
                        @rename($tempFile, $baseFile);
                    }
                }
            } catch (Exception $e) {
                // Log error but continue with rotation anyway
                minai_log("error", "Error sorting metrics before rotation: " . $e->getMessage());
            }
        }
        
        // Remove oldest log file if it exists
        $oldestLog = $dir . '/' . $filename . '.' . $this->maxLogFiles;
        if (file_exists($oldestLog)) {
            @unlink($oldestLog);
        }
        
        // Shift existing log files
        for ($i = $this->maxLogFiles - 1; $i >= 1; $i--) {
            $oldFile = $dir . '/' . $filename . '.' . $i;
            $newFile = $dir . '/' . $filename . '.' . ($i + 1);
            if (file_exists($oldFile)) {
                @rename($oldFile, $newFile);
            }
        }
        
        // Rename current log file
        if (file_exists($baseFile)) {
            @rename($baseFile, $dir . '/' . $filename . '.1');
        }
    }
    
    /**
     * Configure the log rotation settings.
     * 
     * @param int $maxSizeMB Maximum log file size in MB before rotation
     * @param int $maxFiles Maximum number of rotated log files to keep
     */
    public function configureLogRotation($maxSizeMB = 10, $maxFiles = 5) {
        $this->maxLogSizeBytes = max(1, $maxSizeMB) * 1024 * 1024; // Convert MB to bytes, minimum 1MB
        $this->maxLogFiles = max(1, $maxFiles); // At least 1 rotated log file
    }
    
    /**
     * Enable or disable metrics collection
     * 
     * @param bool $enabled Whether metrics should be enabled
     */
    public function setEnabled($enabled) {
        $this->metricsEnabled = (bool)$enabled;
    }
    
    /**
     * Check if metrics collection is enabled
     * 
     * @return bool Whether metrics are enabled
     */
    public function isEnabled() {
        return $this->metricsEnabled;
    }
    
    /**
     * Log session start or end events with system metrics.
     * 
     * @param string $eventType The type of event (session_start or session_end).
     */
    private function logSessionEvent($eventType) {
        if (!$this->sessionStarted) {
            return;
        }
        
        $metric = [
            'type' => $eventType,
            'timestamp' => time(),
            'request_type' => $this->requestType,
            'actor_name' => $this->actorName,
            'system' => $this->getSystemMetrics()
        ];
        
        $this->logMetric($metric);
    }
    
    /**
     * Write a metric to the metrics file.
     * 
     * @param array $metric The metric to write.
     */
    private function logMetric($metric) {
        if (!$this->metricsEnabled || !$this->sessionStarted) {
            return;
        }
        
        try {
            $jsonLine = json_encode($metric) . "\n";
            file_put_contents($this->metricsFile, $jsonLine, FILE_APPEND);
        } catch (Exception $e) {
            // If there's an error, we don't want to disrupt the main application
            // So just silently fail
        }
    }
    
    /**
     * Configure the metrics utility with settings.
     * 
     * @param boolean $enabled Whether metrics collection is enabled.
     * @param float $samplingRate The rate at which to sample metrics (0.0 to 1.0).
     * @param string $metricsFile The file to which metrics will be written.
     * @param int $maxLogSizeMB Maximum log file size in MB before rotation
     * @param int $maxLogFiles Maximum number of rotated log files to keep
     */
    public function configure($enabled = true, $samplingRate = 1.0, $metricsFile = null, $maxLogSizeMB = 10, $maxLogFiles = 5) {
        $this->metricsEnabled = $enabled;
        $this->samplingRate = max(0.0, min(1.0, $samplingRate)); // Clamp between 0 and 1
        
        if ($metricsFile !== null) {
            $this->metricsFile = $metricsFile;
        } else {
            $this->metricsFile = "/var/www/html/HerikaServer/log/minai_metrics.jsonl";
        }
        
        // Configure log rotation
        $this->configureLogRotation($maxLogSizeMB, $maxLogFiles);
        
        // Decide if we should sample this request based on the sampling rate
        if ($this->metricsEnabled && mt_rand() / mt_getrandmax() <= $this->samplingRate) {
            $this->sessionStarted = true;
            $this->logSessionEvent('session_start');
        }
    }
    
    /**
     * Set the request type for this session.
     * 
     * @param string $requestType The type of request.
     */
    public function setRequestType($requestType) {
        $this->requestType = $requestType;
    }
    
    /**
     * Set the actor name for this session.
     * 
     * @param string $actorName The name of the actor.
     */
    public function setActorName($actorName) {
        $this->actorName = $actorName;
    }
  
    /**
     * Log session data that will be included in the session_end event.
     * 
     * @param string $key The key for the session data.
     * @param mixed $value The value for the session data.
     */
    public function logSessionData($key, $value) {
        if (!$this->sessionStarted) {
            return;
        }
        
        $this->sessionData[$key] = $value;
    }
    
    /**
     * End the metrics session and log total execution time.
     */
    public function endSession() {
        if (!$this->sessionStarted) {
            return;
        }
        
        $this->logSessionEvent('session_end');
        $this->sessionStarted = false;
        $this->sessionData = [];
    }
    
    /**
     * Get the current active parent component (last started timer)
     * 
     * @return string|null Current parent component or null if none
     */
    public function getCurrentParent() {
        if (empty($this->activeTimers)) {
            return null;
        }
        return end($this->activeTimers);
    }
}

/**
 * Helper function to start a timer
 * 
 * @param string $name Name of the timer
 * @param string|null $parentComponent Name of the parent component (for hierarchy)
 */
function minai_start_timer($name, $parentComponent = null) {
    // If parent component is not explicitly set but we're inside another timer,
    // use the current active timer as parent
    if ($parentComponent === null) {
        $parentComponent = MinAIMetrics::getInstance()->getCurrentParent();
    }
    MinAIMetrics::getInstance()->startTimer($name, $parentComponent);
}

/**
 * Helper function to stop a timer and record metrics
 * 
 * @param string $name Name of the timer
 * @param array $additionalData Additional data to record
 * @return float|null Duration in seconds or null if timer not found/disabled
 */
function minai_stop_timer($name, $additionalData = []) {
    return MinAIMetrics::getInstance()->stopTimer($name, $additionalData);
}

/**
 * Helper function to record a metric
 * 
 * @param string $type Type of metric
 * @param array $data Metric data
 */
function minai_record_metric($type, $data) {
    MinAIMetrics::getInstance()->recordMetric($type, $data);
}

/**
 * Helper function to enable/disable metrics
 * 
 * @param bool $enabled Whether metrics should be enabled
 */
function minai_set_metrics_enabled($enabled) {
    MinAIMetrics::getInstance()->setEnabled($enabled);
}

/**
 * Helper function to check if metrics are enabled
 * 
 * @return bool Whether metrics are enabled
 */
function minai_is_metrics_enabled() {
    return MinAIMetrics::getInstance()->isEnabled();
}

/**
 * Helper function to configure log rotation settings
 * 
 * @param int $maxSizeMB Maximum log file size in MB before rotation
 * @param int $maxFiles Maximum number of rotated log files to keep
 */
function minai_configure_log_rotation($maxSizeMB = 10, $maxFiles = 5) {
    MinAIMetrics::getInstance()->configureLogRotation($maxSizeMB, $maxFiles);
}

/**
 * Class that automatically starts a timer when constructed and stops it when destroyed
 * 
 * Usage:
 * {
 *     $timer = new MinAITimerScope('operation_name');
 *     // Code to measure goes here
 *     // Timer automatically stops when $timer goes out of scope
 * }
 */
class MinAITimerScope {
    private $timerName;
    private $additionalData;
    
    /**
     * Constructor - automatically starts the timer
     * 
     * @param string $timerName Name of the timer
     * @param string|null $parentComponent Name of the parent component
     * @param array $additionalData Additional data to include when timer stops
     */
    public function __construct($timerName, $parentComponent = null, $additionalData = []) {
        $this->timerName = $timerName;
        $this->additionalData = $additionalData;
        
        minai_start_timer($timerName, $parentComponent);
    }
    
    /**
     * Add or update additional data to include when timer stops
     * 
     * @param string $key Data key
     * @param mixed $value Data value
     * @return $this For method chaining
     */
    public function addData($key, $value) {
        $this->additionalData[$key] = $value;
        return $this;
    }
    
    /**
     * Merge additional data with existing data
     * 
     * @param array $data Data to merge
     * @return $this For method chaining
     */
    public function mergeData($data) {
        $this->additionalData = array_merge($this->additionalData, $data);
        return $this;
    }
    
    /**
     * Manually stop the timer before destruction if needed
     * 
     * @return float|null Duration or null if timer was already stopped
     */
    public function stop() {
        if ($this->timerName !== null) {
            $duration = minai_stop_timer($this->timerName, $this->additionalData);
            $this->timerName = null;
            return $duration;
        }
        return null;
    }
    
    /**
     * Destructor - automatically stops the timer if not stopped manually
     */
    public function __destruct() {
        $this->stop();
    }
}


