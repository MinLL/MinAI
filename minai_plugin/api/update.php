<?php

header('Content-Type: application/json');

// Get the branch from the request
$branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

// Define the directory where your Git repository is located
$repoDir = __DIR__ . '/../';  // Adjust the path based on your folder structure
$tempDir = __DIR__ . '/minai_temp_clone'; // Temporary location for cloning
$repoUrl = 'https://github.com/MinLL/MinAI.git'; // Your GitHub repository URL

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

// Update perms
$chmodCmd = "chmod -R 775 $pluginFolder 2>&1";
exec($chmodCmd, $output, $returnVar);

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to update permissions.",
        'output' => $output
    ]);
    exit;
}

// Copy the contents of minai_plugin to the target directory
$copyCmd = "cp -r $pluginFolder/* $repoDir 2>&1";
exec($copyCmd, $output, $returnVar);

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

// If successful, return a success message
echo json_encode([
    'status' => 'success',
    'message' => "Repository successfully updated from the $branch branch."
]);

?>
 
