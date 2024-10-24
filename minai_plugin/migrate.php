<?php

$path = getcwd().DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
require_once("customintegrations.php");


// Function to be executed if the version matches
function Beta395Migration() {
    error_log("minai: Executing update to beta39.5");
    // Clean up DB and perform migrations
    $GLOBALS["db"] = new sql();
    $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS custom_context");
    $GLOBALS["db"]->execQuery("DROP TABLE IF EXISTS custom_actions");
    CreateContextTableIfNotExists();
    CreateActionsTableIfNotExists();
    echo("Migration complete");
}


$versionFile = 'version.txt';

// Check if the version file exists
if (file_exists($versionFile)) {
    // Read the version from the file
    $versionInFile = trim(file_get_contents($versionFile));
    
    if ($versionInFile === "beta39.5") {
        Beta395Migration();
    } else {
        echo "Version mismatch. Current version is: $versionInFile";
    }
} else {
    echo "Version file not found.";
}
