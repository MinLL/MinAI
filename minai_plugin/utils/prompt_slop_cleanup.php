<?php

/**
 * Cleans up various text patterns in the context data
 * @param array $contextData Array of context entries to clean
 * @return array Cleaned context data
 */
function cleanupSlop($contextData) {
    if (!is_array($contextData)) {
        return $contextData;
    }

    $cleaned = [];
    foreach ($contextData as $entry) {
        if (!isset($entry['content'])) {
            continue;
        }
        $originalContent = $entry['content'];
        $content = $entry['content'];

        // Remove any existing double parentheses that might interfere with our patterns
        $content = str_replace('))', ')', $content);
        $content = str_replace('((', '(', $content);

        // Pattern 1: Handle "thinking to self" pattern
        if (preg_match('/The Narrator:\s*(.*?)\s*\(talking to (.*?) is thinking to (him|her|them)self\)/', $content, $matches)) {
            $thought = $matches[1];
            $character = $matches[2];
            $pronoun = $matches[3];
            $content = "$character: $thought ($character is thinking to $pronoun" . "self)";
        }
        // Pattern 1b: Handle "reacting to physical sensations" pattern
        else if (preg_match('/The Narrator:\s*(.*?)\s*\(talking to (.*?) is reacting to physical sensations\)/', $content, $matches)) {
            $thought = $matches[1];
            $character = $matches[2];
            $content = "$character: $thought ($character is thinking to herself, reacting to physical sensations)";
        }

        // Pattern 2: Remove "(Talking to The Narrator)" from any character dialogue
        $content = preg_replace('/\s*\(Talking to The Narrator\)/', '', $content);

        // Pattern 5: Handle context messages that already have parentheses
        if (preg_match('/^The Narrator:\((.*?)\)$/', $content, $matches)) {
            $content = "($matches[1])";
        }
        // Pattern 6: Handle context messages without parentheses
        else if (strpos($content, 'The Narrator:') === 0) {
            $content = preg_replace('/^The Narrator:\s*(.*)$/', '($1)', $content);
        }

        // Post-processing: Remove lines containing only character names
        if (preg_match('/^[^:]+:\s*$/', $content)) {
            $content = '';
        }

        // Post-processing: Remove character stat lines
        if (preg_match('/^level:\d+,name:"[^"]+",race:"[^"]+"/', $content)) {
            $content = '';
        }

        // Post-processing: Remove standalone weapon/item references
        if (preg_match('/^\(with\s+[^)]+\)$/', $content)) {
            $content = '';
        }

        // Post-processing: Remove "Current followers" line
        if (preg_match('/^Current followers:\[\]$/', $content)) {
            $content = '';
        }

        // Clean up any remaining double parentheses that might have been created
        $content = str_replace('))', ')', $content);
        $content = str_replace('((', '(', $content);

        // Remove leading and trailing whitespace
        $content = trim($content);
        // Log original and replaced content if they differ
        if ($content !== $originalContent) {
            error_log("Cleaned up context - Original: " . $originalContent);
            error_log("Cleaned up context - Replaced: " . $content); 
        }
        else {
            error_log("No changes made to content: " . $content);
        }
        $entry['content'] = $content;
        $cleaned[] = $entry;
    }

    return $cleaned;
} 