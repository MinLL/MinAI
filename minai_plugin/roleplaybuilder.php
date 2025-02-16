<?php
require_once("/var/www/html/HerikaServer/lib/data_functions.php");
function convertToFirstPerson($text, $name, $pronouns) {
    if (empty($text)) {
        return "";
    }

    // Basic name replacements
    $text = str_replace([
        "{$name} is",
        "{$name} has",
        "{$name}'s",
    ], [
        "You are",
        "You have",
        "Your",
    ], $text);

    // Pronoun replacements
    $text = str_replace([
        " {$pronouns['subject']} is",
        " {$pronouns['subject']} has",
        " {$pronouns['subject']} ",
        " {$pronouns['object']} ",
        " {$pronouns['possessive']} ",
    ], [
        " you are",
        " you have",
        " you ",
        " you ",
        " your ",
    ], $text);

    // Handle capitalized versions
    $text = str_replace([
        "Her ",
        "His ",
        "Their ",
        "She ",
        "He ",
        "They ",
    ], [
        "Your ",
        "Your ",
        "Your ",
        "You ",
        "You ",
        "You ",
    ], $text);

    // Clean up any double spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

function interceptRoleplayInput() {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && (isPlayerInput() || $GLOBALS["gameRequest"][0] == "minai_roleplay")) {
        if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
            error_log("minai: Intercepting minai_roleplay.");
        }
        else {
            error_log("minai: Intercepting dialogue input for Translation. Original input: " . $GLOBALS["gameRequest"][3]);
        }
        
        // Initialize local variables with global defaults
        $PLAYER_NAME = $GLOBALS["PLAYER_NAME"];
        $PLAYER_BIOS = $GLOBALS["PLAYER_BIOS"];
        $HERIKA_NAME = $GLOBALS["HERIKA_NAME"];
        $HERIKA_PERS = $GLOBALS["HERIKA_PERS"];
        $CONNECTOR = $GLOBALS["CONNECTOR"];
        $HERIKA_DYNAMIC = $GLOBALS["HERIKA_DYNAMIC"];

        // Disable roleplay mode after processing begins
        SetEnabled($PLAYER_NAME, "isRoleplaying", false);

        // Import narrator profile which may override the above variables
        if (file_exists(GetNarratorConfigPath())) {
            error_log("minai: Using Narrator Profile");
            $path = GetNarratorConfigPath();    
            include($path);
        }
        
        SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
        $settings = $GLOBALS['roleplay_settings'];
        // Get the original input and strip the player name prefix if it exists
        $originalInput = $GLOBALS["gameRequest"][3];
        
        // Extract any context information that's in parentheses
        $contextPrefix = "";
        if (preg_match('/^\((.*?)\)/', $originalInput, $matches)) {
            $contextPrefix = $matches[0];
            $originalInput = trim(substr($originalInput, strlen($contextPrefix)));
        }
        
        // Strip player name prefix if it exists
        $originalInput = preg_replace('/^' . preg_quote($PLAYER_NAME . ':') . '\s*/', '', $originalInput);
        $originalInput = trim($originalInput);

        // Get recent context - use configured value for context messages
        $contextMessages = $settings['CONTEXT_MESSAGES'];
        $contextDataHistoric = DataLastDataExpandedFor("", $contextMessages * -1);
        
        // Get info about location and NPCs
        $contextDataWorld = DataLastInfoFor("", -2);
        
        // Get lists of valid names and locations
        $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange())));
        $possibleLocations = DataPosibleLocationsToGo();
        
        // Combine contexts
        $contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);
        
        // Build messages array using config settings
        $messages = [];
        
        // Get player pronouns
        $playerPronouns = GetActorPronouns($PLAYER_NAME);
        
        // Get contexts and convert to first person
        $physDesc = convertToFirstPerson(GetPhysicalDescription($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $arousalStatus = convertToFirstPerson(GetArousalContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $survivalStatus = convertToFirstPerson(GetSurvivalContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $clothingStatus = convertToFirstPerson(GetClothingContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $devicesStatus = convertToFirstPerson(GetDDContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $fertilityStatus = convertToFirstPerson(GetFertilityContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        // Replace variables in system prompt and request
        $variableReplacements = [
            'PLAYER_NAME' => $PLAYER_NAME,
            'PLAYER_BIOS' => replaceVariables($PLAYER_BIOS, ['PLAYER_NAME' => $PLAYER_NAME]),
            'NEARBY_ACTORS' => implode(", ", $nearbyActors),
            'NEARBY_LOCATIONS' => implode(", ", $possibleLocations),
            'RECENT_EVENTS' => implode("\n", array_map(function($ctx) { 
                return $ctx['content']; 
            }, array_slice($contextDataFull, -$settings['context_messages']))),
            'PLAYER_SUBJECT' => $playerPronouns['subject'],
            'PLAYER_OBJECT' => $playerPronouns['object'],
            'PLAYER_POSSESSIVE' => $playerPronouns['possessive'],
            'HERIKA_DYNAMIC' => $HERIKA_DYNAMIC,
            'ORIGINAL_INPUT' => $originalInput,
            'INSTRUCTIONS' => $settings['INSTRUCTIONS'],
            'PHYSICAL_DESCRIPTION' => $physDesc,
            'AROUSAL_STATUS' => $arousalStatus,
            'SURVIVAL_STATUS' => $survivalStatus,
            'CLOTHING_STATUS' => $clothingStatus,
            'DEVICES_STATUS' => $devicesStatus,
            'FERTILITY_STATUS' => $fertilityStatus
        ];

        // Apply replacements to system prompt
        $systemPrompt = replaceVariables(
            $GLOBALS["gameRequest"][0] == "minai_roleplay" 
                ? $settings['ROLEPLAY_SYSTEM_PROMPT']
                : $settings['SYSTEM_PROMPT'],
            $variableReplacements
        );

        // Sort sections by their order if it exists, otherwise maintain config file order
        $sections = $settings['sections'];
        if (isset(reset($sections)['order'])) {
            uasort($sections, function($a, $b) {
                return ($a['order'] ?? 0) - ($b['order'] ?? 0);
            });
        }

        // Build the context message
        $contextMessage = '';
        foreach ($sections as $sectionName => $section) {
            if (!$section['enabled']) continue;

            if ($contextMessage !== '') {
                $contextMessage .= "\n\n";
            }

            $content = $section['header'] . "\n";
            $content .= replaceVariables($section['content'], $variableReplacements);

            $contextMessage .= $content;
        }

        // Build the messages array with proper spacing
        $messages = [
            ['role' => 'system', 'content' => replaceVariables($systemPrompt, $variableReplacements) . "\n\n"],
            ['role' => 'system', 'content' => replaceVariables($contextMessage, $variableReplacements)],
            ['role' => 'user', 'content' => "\n" . replaceVariables(
                $GLOBALS["gameRequest"][0] == "minai_roleplay" 
                    ? $settings['roleplay_request']
                    : $settings['translation_request'],
                $variableReplacements
            )]
        ];

        // Debug log the messages being sent to LLM
        error_log("minai: Messages being sent to LLM: " . json_encode($messages, JSON_PRETTY_PRINT));

        // Call LLM with specific parameters for dialogue generation
        $response = callLLM($messages, $CONNECTOR["openrouter"]["model"], [
            'temperature' => $CONNECTOR["openrouter"]["temperature"],
            'max_tokens' => 150
        ]);

        if ($response !== null) {
            // Clean up the response - remove quotes and ensure it's dialogue-ready
            $response = trim($response, "\"' \n");
            
            // Remove any character name prefixes the LLM might have added
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME . ':') . '\s*/', '', $response);
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME) . ':\s*/', '', $response);
            
            error_log("minai: Roleplay input transformed from \"{$originalInput}\" to \"{$response}\"");
            
            if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
                // rewrite as player input
                $GLOBALS["gameRequest"][0] = "inputtext";
                $GLOBALS["gameRequest"][3] = $response;
            }
            else {
                // Format the response with a single character name prefix
                $GLOBALS["gameRequest"][3] = $contextPrefix . $PLAYER_NAME . ": " . $response;
            }
            # error_log("minai: Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            error_log("minai: Failed to generate roleplay response, using original input");
        }

    }
}