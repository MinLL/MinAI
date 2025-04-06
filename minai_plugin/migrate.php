<?php

$pluginPath = str_replace("/api","", getcwd());
$path = str_replace("/ext/minai_plugin","", $pluginPath);
require_once($path . "/conf".DIRECTORY_SEPARATOR."conf.php");
require_once("logger.php");
require_once("db_utils.php");
require_once("$path/lib/{$GLOBALS["DBDRIVER"]}.class.php");
if (!isset($GLOBALS["db"])) {
    $GLOBALS["db"] = new sql();
}

// Function to add victim_actors column if it doesn't exist
function AddVictimActorsColumn() {
    minai_log("info", "Checking for victim_actors column");
    $db = $GLOBALS["db"];
    
    // Check if column exists
    $result = $db->fetchAll("SELECT column_name 
                             FROM information_schema.columns 
                             WHERE table_name='minai_threads' 
                             AND column_name='victim_actors'");
    
    if (!$result || count($result) === 0) {
        minai_log("info", "Adding victim_actors column to minai_threads table");
        $db->execQuery("ALTER TABLE minai_threads ADD COLUMN victim_actors text");
    }
}


// Function to be executed for version 1.0.7
function Version107Migration() {
    minai_log("info", "Executing update to 1.0.7");
    AddVictimActorsColumn();
    minai_log("info", "1.0.7 Migration complete");
}

function Version210Migration() {
    minai_log("info", "Executing update to 2.1.0");    
    // Check if columns exist in equipment_description table
    $db = $GLOBALS["db"];
    
    // First check if the table exists
    $tableExists = $db->fetchAll("SELECT to_regclass('equipment_description') AS exists");
    minai_log("info", "tableExists: " . json_encode($tableExists));
    if (empty($tableExists) || $tableExists[0]['exists'] === null) {
        minai_log("info", "equipment_description table does not exist, creating it");
        CreateEquipmentDescriptionTableIfNotExist();
    } else {
        // PostgreSQL specific query to check for columns
        $query = "SELECT column_name 
                 FROM information_schema.columns 
                 WHERE table_name='equipment_description' 
                 AND column_name IN ('is_restraint', 'hidden_by')";
        
        $result = $db->fetchAll($query);
        
        // PostgreSQL will return one row per column that exists
        $columnCount = is_array($result) ? count($result) : 0;
        minai_log("info", "Found $columnCount required columns in equipment_description table");
        
        // If the columns don't exist or we don't have both of them
        if ($columnCount < 2) {
            minai_log("info", "Required columns missing in equipment_description table. Recreating table.");
            
            // Drop the table
            $db->execQuery("DROP TABLE IF EXISTS equipment_description");
            
            // Recreate the table using the function from db_utils.php
            CreateEquipmentDescriptionTableIfNotExist();
            
            minai_log("info", "equipment_description table recreated with all required columns");
        } else {
            minai_log("info", "equipment_description table already has required columns");
        }
    }
    
    minai_log("info", "2.1.0 Migration complete");
}

// Function to parse version string into components
function parseVersion($version) {
    $result = ['major' => 0, 'minor' => 0, 'hotfix' => 0, 'tag' => '', 'tagName' => '', 'tagVersion' => 0];
    
    // Handle tag part (if exists)
    $parts = explode('-', $version);
    $versionNumbers = $parts[0];
    
    if (isset($parts[1])) {
        $result['tag'] = $parts[1];
        
        // Parse the tag to extract name and version number (e.g., "dev4" -> name="dev", version=4)
        preg_match('/([a-zA-Z]+)(\d*)/', $parts[1], $matches);
        if (count($matches) >= 2) {
            $result['tagName'] = $matches[1];
            $result['tagVersion'] = isset($matches[2]) && is_numeric($matches[2]) ? intval($matches[2]) : 0;
        }
    }
    
    // Parse version numbers
    $numbers = explode('.', $versionNumbers);
    if (isset($numbers[0])) $result['major'] = intval($numbers[0]);
    if (isset($numbers[1])) $result['minor'] = intval($numbers[1]);
    if (isset($numbers[2])) $result['hotfix'] = intval($numbers[2]);
    
    return $result;
}

// Function to compare versions
function compareVersions($version1, $version2) {
    $v1 = parseVersion($version1);
    $v2 = parseVersion($version2);
    
    // Compare major version
    if ($v1['major'] != $v2['major']) {
        return $v1['major'] - $v2['major'];
    }
    
    // Compare minor version
    if ($v1['minor'] != $v2['minor']) {
        return $v1['minor'] - $v2['minor'];
    }
    
    // Compare hotfix version
    if ($v1['hotfix'] != $v2['hotfix']) {
        return $v1['hotfix'] - $v2['hotfix'];
    }
    
    // If all version numbers are equal, compare tags
    // If only one version has a tag
    if (empty($v1['tag']) && !empty($v2['tag'])) {
        return 1; // Version without tag is considered higher than version with tag
    }
    if (!empty($v1['tag']) && empty($v2['tag'])) {
        return -1; // Version without tag is considered higher than version with tag
    }
    
    // If both versions have tags
    if (!empty($v1['tag']) && !empty($v2['tag'])) {
        // If tags have different names
        if ($v1['tagName'] !== $v2['tagName']) {
            // You can define tag priority here if needed (e.g., 'beta' > 'alpha' > 'dev')
            $tagPriority = ['dev' => 1, 'alpha' => 2, 'beta' => 3, 'rc' => 4];
            $priority1 = isset($tagPriority[strtolower($v1['tagName'])]) ? $tagPriority[strtolower($v1['tagName'])] : 0;
            $priority2 = isset($tagPriority[strtolower($v2['tagName'])]) ? $tagPriority[strtolower($v2['tagName'])] : 0;
            return $priority1 - $priority2;
        }
        
        // If tags have the same name, compare their version numbers
        return $v1['tagVersion'] - $v2['tagVersion'];
    }
    
    // If all are equal
    return 0;
}

// Function to run all migrations needed to reach target version
function runMigrations($currentVersion) {
    minai_log("info", "Running migrations for version: $currentVersion");
    
    // Define version breakpoints and their migration functions
    $migrations = [
        '1.0.7' => 'Version107Migration',
        '2.1.0' => 'Version210Migration'
    ];
    
    // Convert legacy versions to semantic format for comparison
    $versionMap = [
        '1.0.7' => '1.0.7',
        '2.1.0' => '2.1.0'
    ];
    
    // Sort migrations by version
    uksort($versionMap, 'compareVersions');
    
    // Run migrations for versions higher than the current version
    foreach ($versionMap as $migrationVersion => $semanticVersion) {
        if (compareVersions($currentVersion, $semanticVersion) < 0) {
            $migrationFunction = $migrations[$migrationVersion];
            if (function_exists($migrationFunction)) {
                minai_log("info", "Running migration function for version: $migrationVersion");
                call_user_func($migrationFunction);
            }
        }
    }
    
    minai_log("info", "All migrations completed");
}

$versionFile = "$pluginPath/version.txt";

// Check if the version file exists
if (file_exists($versionFile)) {
    // Read the version from the file
    $versionInFile = trim(file_get_contents($versionFile));
    
    // Run all necessary migrations based on the current version
    runMigrations($versionInFile);
} else {
    echo "Version file not found.";
}
