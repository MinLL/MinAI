<?php

header('Content-Type: application/json');

// Get the branch from the request
$branch = isset($_GET['branch']) ? $_GET['branch'] : 'main';

// Define the directory where your Git repository is located
$repoDir = __DIR__ . '/../';  // Adjust the path based on your folder structure

// Change to the repository directory
chdir($repoDir);

$safeDirCmd = "HOME=/tmp git config --global --add safe.directory /var/www/html/HerikaServer/ext/minai_plugin";
$output = [];
$returnVar = 0;
exec($safeDirCmd, $output, $returnVar);

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to checkout branch $branch",
        'output' => $output
    ]);
    exit;
}

// Checkout the desired branch
$checkoutCmd = "HOME=/tmp git checkout $branch 2>&1";
$output = [];
$returnVar = 0;
exec($checkoutCmd, $output, $returnVar);

if ($returnVar !== 0) {
    echo json_encode([
        'status' => 'error',
        'message' => "Failed to checkout branch $branch",
        'output' => $output
    ]);
    exit;
}

// Run the git pull command
$pullCmd = 'HOME=/tmp git pull 2>&1';
exec($pullCmd, $output, $returnVar);

if ($returnVar === 0) {
    echo json_encode(['status' => 'success', 'message' => 'Repository updated successfully']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update repository',
        'output' => $output
    ]);
}
?>
