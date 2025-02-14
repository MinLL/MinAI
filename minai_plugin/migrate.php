<?php

$pluginPath = str_replace("/api","", getcwd());
$path = str_replace("/ext/minai_plugin","", $pluginPath);
require_once($path . "/conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "/lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
require_once("customintegrations.php");


// Function to add victim_actors column if it doesn't exist
function AddVictimActorsColumn() {
    error_log("minai: Checking for victim_actors column");
    $db = $GLOBALS["db"];
    
    // Check if column exists
    $result = $db->execQuery("SELECT column_name 
                             FROM information_schema.columns 
                             WHERE table_name='minai_threads' 
                             AND column_name='victim_actors'");
    
    if (!$result || count($result) === 0) {
        error_log("minai: Adding victim_actors column to minai_threads table");
        $db->execQuery("ALTER TABLE minai_threads ADD COLUMN victim_actors text");
    }
}

// Function to be executed if the version matches
function Beta395Migration() {
    error_log("minai: Executing update to beta39.5");
    // Clean up DB and perform migrations
    $GLOBALS["db"] = new sql();
    $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS custom_context");
    $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS custom_actions");
    CreateContextTableIfNotExists();
    CreateActionsTableIfNotExists();
    error_log("minai: Beta39.5 Migration complete");
}

// Function to be executed for version 1.0.7
function Version107Migration() {
    error_log("minai: Executing update to 1.0.7");
    $GLOBALS["db"] = new sql();
    AddVictimActorsColumn();
    Beta395Migration();
    error_log("minai: 1.0.7 Migration complete");
}

$versionFile = "$pluginPath/version.txt";

// Check if the version file exists
if (file_exists($versionFile)) {
    // Read the version from the file
    $versionInFile = trim(file_get_contents($versionFile));
    
    if ($versionInFile === "beta39.5") {
        Beta395Migration();
    } else if (strpos($versionInFile, "1.0.7") !== false) {
        Version107Migration();
    } else {
        // No migration necessary
    }
} else {
    echo "Version file not found.";
}
