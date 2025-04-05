<?php
/**
 * Format Utility Class
 * 
 * This class provides shared formatting functionality for system prompts
 * and roleplay messages.
 */
class FormatUtil {
    /**
     * Format context content according to formatting rules
     * 
     * @param string $context The context string to format
     * @return string Formatted context
     */
    public static function formatContext($context) {
        if (empty($context)) {
            return '';
        }
        
        // Add debug logging
        // minai_log("debug", "Original context: " . str_replace("\n", "\\n", $context));
        
        // Split into lines for processing
        $lines = explode("\n", $context);
        $result = [];
        $in_key_points = false;
        
        foreach ($lines as $line) {
            $line = rtrim($line);  // Keep left indentation but remove trailing whitespace
            
            // Skip empty lines
            if (empty(trim($line))) {
                continue;
            }
            
            // Special case for "Key Points:" - exact pattern match
            if (preg_match('/^(?:[-•*]\s*)?Key\s+Points:$/', trim($line))) {
                $in_key_points = true;
                $result[] = "- Key Points:";
                continue;
            }
            
            // Handle bullet points with "•" character - key points section
            if ($in_key_points && preg_match('/^(?:[-•*]\s*)?\s*[•]\s+(.+)$/', trim($line), $matches)) {
                $result[] = "  - " . $matches[1];
                continue;
            }
            
            // Handle normal bullet points (any level) - key points section
            if ($in_key_points && preg_match('/^(?:[-•*]\s*)+(.+)$/', trim($line), $matches)) {
                $result[] = "  - " . $matches[1];
                continue;
            }
            
            // Regular bullet point handling (outside key points)
            if (preg_match('/^(\s*)(?:[-•*]\s+)(.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $content = $matches[2];
                
                // Calculate indentation level
                $level = floor(strlen($indent) / 2);
                $prefix = str_repeat("  ", $level);
                
                $result[] = $prefix . "- " . $content;
                
                // End key points section if we're processing regular items
                if ($level === 0) {
                    $in_key_points = false;
                }
                continue;
            }
            
            // Ensure any line starting with # has at least 4 of them
            // But don't modify hashtag objects like #player_name#
            if (preg_match('/^#(?![a-zA-Z0-9_]+#)/', $line)) {
                // Count existing # at start
                preg_match('/^#+/', $line, $matches);
                $hashCount = strlen($matches[0]);
                
                if ($hashCount < 4) {
                    // Add additional # to reach minimum of 4
                    $line = str_repeat('#', 4 - $hashCount) . $line;
                }
                $result[] = trim($line);
                $in_key_points = false;
            }
            else {
                // If it doesn't match any bullet pattern, make it a standard list item
                // Also terminates any key points section
                $in_key_points = false;
                if (count($lines) > 1) {
                    $result[] = "- " . trim($line);
                } else {
                    $result[] = trim($line);
                }
            }
        }
        
        $formatted = implode("\n", $result);
        
        // Add debug logging for the result
        // minai_log("debug", "Formatted context: " . str_replace("\n", "\\n", $formatted));
        
        return $formatted;
    }
} 