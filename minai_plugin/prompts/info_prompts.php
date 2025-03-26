<?php
// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

/**
 * Information Prompts for Device Actions
 * 
 * This file serves as an entry point for all prompts,
 * using the new registry system.
 */

// Include the prompts registry which handles all prompt definitions
//require_once(dirname(__FILE__) . "/registry.php");

// This file now acts as a simple entry point to the new registry system.
// All prompt definitions have been moved to the 'definitions' directory and
// are registered through the PromptRegistry system.

// Require all prompt files
// Get all PHP files in current directory
$files = glob(dirname(__FILE__) . "/info_*.php");

// Import all info_*.php files except this one
foreach ($files as $file) {
    $basename = basename($file);
    if ($basename !== "info_prompts.php") {
        require_once($file);
    }
}

// For any code that directly accesses the old prompts, we can map them here
// But new code should use the PromptRegistry methods:
// - PromptRegistry::get($id)
// - PromptRegistry::generate($id, $context)
// - GetPromptsByTarget($target)
// - GeneratePrompt($promptId, $context)
