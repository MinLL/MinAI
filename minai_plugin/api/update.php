<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

require_once("../logger.php");

function send_message($status, $message) {
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]) . "\n";
    ob_flush();
    flush();
}

$path = "..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
require_once($path . "conf".DIRECTORY_SEPARATOR."conf.php");
require_once($path. "lib" .DIRECTORY_SEPARATOR."{$GLOBALS["DBDRIVER"]}.class.php");
$GLOBALS["db"] = new sql();
// Get the branch from the request
$branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

// Define the directory where your Git repository is located
$repoDir = __DIR__ . '/../';  // Adjust the path based on your folder structure
$tempDir = __DIR__ . '/minai_temp_clone'; // Temporary location for cloning
$repoUrl = 'https://github.com/MinLL/MinAI.git'; // Your GitHub repository URL

// Initial status message
send_message('progress', "🚀 Starting update process for branch: $branch");

// Ensure that the target directory is writable
if (!is_writable($repoDir)) {
    send_message('error', "❌ The target directory $repoDir is not writable.");
    exit;
}

// Ensure that the temp directory exists and is clean
if (is_dir($tempDir)) {
    send_message('progress', "🧹 Cleaning up previous temporary files...");
    shell_exec("rm -rf $tempDir");
}
send_message('progress', "📁 Creating temporary directory...");
mkdir($tempDir);

// Change to the temporary directory and clone the repository
send_message('progress', "⬇️ Cloning repository from $branch branch...");
$cloneCmd = "git clone --branch $branch $repoUrl $tempDir 2>&1";
$output = [];
$returnVar = 0;
exec($cloneCmd, $output, $returnVar);
minai_log("info", "Clone output: " . implode("\n", $output));

if ($returnVar !== 0) {
    send_message('error', "❌ Failed to clone repository from branch $branch:\n" . implode("\n", $output));
    exit;
}
send_message('progress', "✅ Repository cloned successfully");

// Define the path to the minai_plugin folder in the cloned repository
$pluginFolder = "$tempDir/minai_plugin";

// Check if the minai_plugin folder exists in the cloned repo
if (!is_dir($pluginFolder)) {
    send_message('error', "❌ The minai_plugin folder was not found in the cloned repository");
    exit;
}
send_message('progress', "✅ Plugin folder found");

// Update permissions for the minai_plugin folder
send_message('progress', "🔒 Updating file permissions...");
$chmodCmd = "chmod -R 0775 $pluginFolder 2>&1";
exec($chmodCmd, $output, $returnVar);
minai_log("info", "Chmod output: " . implode("\n", $output));

if ($returnVar !== 0) {
    send_message('error', "❌ Failed to update permissions:\n" . implode("\n", $output));
    exit;
}
send_message('progress', "✅ Permissions updated successfully");

// Copy the contents of minai_plugin to the target directory
send_message('progress', "📋 Copying new files...");
$copyCmd = "cp -Rf $pluginFolder/* $repoDir 2>&1";
exec($copyCmd, $output, $returnVar);
minai_log("info", "Copy output: " . implode("\n", $output));

if ($returnVar !== 0) {
    send_message('error', "❌ Failed to copy files:\n" . implode("\n", $output));
    exit;
}
send_message('progress', "✅ Files copied successfully");

// Clean up the temp directory
send_message('progress', "🧹 Cleaning up temporary files...");
shell_exec("rm -rf $tempDir");
send_message('progress', "✅ Cleanup completed");

// Run migrate script
$migrateScript = "..".DIRECTORY_SEPARATOR."migrate.php";
if (file_exists($migrateScript)) {
    send_message('progress', "🔄 Running database migrations...");
    include($migrateScript);
    send_message('progress', "✅ Migrations completed");
}

// Final success message
send_message('progress', "🎉 All steps completed successfully!");
send_message('success', "✨ Repository successfully updated from the $branch branch");

