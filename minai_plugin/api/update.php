<?php

header('Content-Type: application/json');
$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");

// Get the branch from the request
$branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

// Define the directory where your Git repository is located
$repoDir = __DIR__ . '/../';  // Adjust the path based on your folder structure
$tempDir = __DIR__ . '/minai_temp_clone'; // Temporary location for cloning
$repoUrl = 'https://github.com/MinLL/MinAI.git'; // Your GitHub repository URL

// Ensure that the target directory is writable
if (!is_writable($repoDir)) {
    echo json_encode([
        'status' => 'error',
        'message' => "The target directory $repoDir is not writable."
    ]);
    exit;
}

// Ensure that the temp directory exists and is clean
if (is_dir($tempDir)) {
    shell_exec("rm -rf $tempDir"); // Clean up previous clone if it exists
}
mkdir($tempDir);

// Change to the temporary directory and clone the repository
$cloneCmd = "git clone --branch $branch $repoUrl $tempDir 2>&1";
$output = [];
$returnVar = 0;
exec($cloneCmd, $output, $returnVar);
error_log("Clone output: " . implode("\n", $output)); // Log the clone output

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to clone repository from branch $branch.",
        'output' => $output
    ]);
    exit;
}

// Define the path to the minai_plugin folder in the cloned repository
$pluginFolder = "$tempDir/minai_plugin";

// Check if the minai_plugin folder exists in the cloned repo
if (!is_dir($pluginFolder)) {
    echo json_encode([
        'status' => 'error',
        'message' => "The minai_plugin folder was not found in the cloned repository.",
        'output' => $output
    ]);
    exit;
}

// Update permissions for the minai_plugin folder
$chmodCmd = "chmod -R 775 $pluginFolder 2>&1";
exec($chmodCmd, $output, $returnVar);
error_log("Chmod output: " . implode("\n", $output)); // Log the chmod output

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to update permissions.",
        'output' => $output
    ]);
    exit;
}

// Copy the contents of minai_plugin to the target directory, using -Rf to force overwriting files
$copyCmd = "cp -Rf $pluginFolder/* $repoDir 2>&1";
exec($copyCmd, $output, $returnVar);
error_log("Copy output: " . implode("\n", $output)); // Log the copy output

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to copy files from minai_plugin folder.",
        'output' => $output
    ]);
    exit;
}

// Clean up the temp directory
shell_exec("rm -rf $tempDir");

// Clean up DB and perform migrations
$db = new sql();
$db->execQuery("DROP TABLE IF EXISTS custom_context");
$db->execQuery("DROP TABLE IF EXISTS custom_actions");

// Run migrate script
$migrateScript = "..".DIRECTORY_SEPARATOR."migrate.php";
if (file_exists($migrateScript))
    include($migrateScript);

// If successful, return a success message
echo json_encode([
    'status' => 'success',
    'message' => "Repository successfully updated from the $branch branch."
]);

?>
