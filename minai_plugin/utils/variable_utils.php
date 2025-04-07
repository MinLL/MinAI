<?php
function GetActorPronouns($name) {
    // Default to "they/them/their" if we can't determine gender
    $pronouns = [
        "subject" => "they",
        "object" => "them", 
        "possessive" => "their"
    ];
    
    // Try to determine gender from keywords
    if (IsMale($name)) {
        $pronouns = [
            "subject" => "he",
            "object" => "him",
            "possessive" => "his"
        ];
    } else if (IsFemale($name)) {
        $pronouns = [
            "subject" => "she",
            "object" => "her",
            "possessive" => "her"
        ];
    }
    
    return $pronouns;
}
function replaceVariables($content, $replacements, $depth = 0) {
    if (empty($content) || $depth > 10) { // Prevent infinite recursion
        return $content;
    }
    
    // Ensure all values are strings
    $stringReplacements = array_map(function($value) {
        return (string)$value;
    }, $replacements);
    
    // Create search array with #variable# format
    $search = array_map(function($key) {
        return "#{$key}#";
    }, array_keys($stringReplacements));
    
    // Do the initial replacement
    $result = str_replace($search, array_values($stringReplacements), $content);
    
    // Look for any remaining #VARIABLE# patterns
    while (strpos($result, '#') !== false && preg_match_all('/#([A-Z_]+)#/', $result, $matches)) {
        $hasReplacement = false;
        foreach ($matches[0] as $match) {
            if (isset($replacements[trim($match, '#')])) {
                $hasReplacement = true;
                break;
            }
        }
        // Only continue if we found a replaceable variable
        if ($hasReplacement) {
            $result = replaceVariables($result, $replacements, $depth + 1);
        } else {
            break; // No more replaceable variables found
        }
    }
    
    return $result;
}


function ExpandPromptVariables($prompt) {
    // Get pronouns for target, Herika, and player from globals
    $targetPronouns = $GLOBALS["target_pronouns"];
    $herikaPronouns = $GLOBALS["herika_pronouns"];
    $playerPronouns = $GLOBALS["player_pronouns"];
    
    $variables = array(
        '#target#' => $GLOBALS["target"],
        '#player_name#' => $GLOBALS["PLAYER_NAME"],
        '#PLAYER_NAME#' => $GLOBALS["PLAYER_NAME"],
        '#herika_name#' => $GLOBALS["HERIKA_NAME"],
        '#HERIKA_NAME#' => $GLOBALS["HERIKA_NAME"],
        // Add target pronoun variables
        '#target_subject#' => $targetPronouns["subject"],
        '#target_object#' => $targetPronouns["object"], 
        '#target_possessive#' => $targetPronouns["possessive"],
        // Add Herika pronoun variables
        '#herika_subject#' => $herikaPronouns["subject"],
        '#herika_object#' => $herikaPronouns["object"],
        '#herika_possessive#' => $herikaPronouns["possessive"],
        // Add player pronoun variables
        '#player_subject#' => $playerPronouns["subject"],
        '#player_object#' => $playerPronouns["object"],
        '#player_possessive#' => $playerPronouns["possessive"]
    );
    
    return str_replace(array_keys($variables), array_values($variables), $prompt);
}