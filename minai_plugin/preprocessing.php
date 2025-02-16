<?php

require_once("util.php");
// TODO: Add an actual install routine to the HerikaServer proper to not do this every request.
InitiateDBTables();

function interceptRoleplayInput() {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && isPlayerInput() ) {
        error_log("minai: Intercepting dialogue input for Roleplay");
        error_log("minai: Original input: " . $GLOBALS["gameRequest"][3]);
        
        // Initialize local variables with global defaults
        $PLAYER_NAME = $GLOBALS["PLAYER_NAME"];
        $PLAYER_BIOS = $GLOBALS["PLAYER_BIOS"];
        $HERIKA_NAME = $GLOBALS["HERIKA_NAME"];
        $HERIKA_PERS = $GLOBALS["HERIKA_PERS"];
        $CONNECTOR = $GLOBALS["CONNECTOR"];
        $HERIKA_DYNAMIC = $GLOBALS["HERIKA_DYNAMIC"];

        // Import narrator profile which may override the above variables
        if (file_exists(GetNarratorConfigPath())) {
            error_log("minai: Using Narrator Profile");
            $path = GetNarratorConfigPath();    
            include($path);
        }
        
        SetEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying", false);
        
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

        // Get recent context - limit to last 5 exchanges for more focused context
        $lastNDataForContext = 10;
        $contextDataHistoric = DataLastDataExpandedFor("", $lastNDataForContext * -1);
        
        // Get info about location and NPCs
        $contextDataWorld = DataLastInfoFor("", -2);
        
        // Get lists of valid names and locations
        $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange())));
        $possibleLocations = DataPosibleLocationsToGo();
        
        // Combine contexts
        $contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);
        
        // Build messages array using config settings
        $messages = [];
        $settings = $GLOBALS['roleplay_settings'];
        
        // Get player pronouns
        $playerPronouns = GetActorPronouns($PLAYER_NAME);
        
        // Replace variables in system prompt
        $systemPrompt = str_replace(
            ['#player_name#', '#player_subject#', '#player_object#', '#player_possessive#'],
            [$PLAYER_NAME, $playerPronouns['subject'], $playerPronouns['object'], $playerPronouns['possessive']],
            $settings['system_prompt']
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

            // Add spacing before each section (except the first one)
            if ($contextMessage !== '') {
                $contextMessage .= "\n\n";
            }

            $content = $section['header'] . "\n";
            
            $content .= str_replace(
                [
                    '#player_name#',
                    '#player_bios#',
                    '#nearby_actors#',
                    '#nearby_locations#',
                    '#recent_events#',
                    '#player_subject#',
                    '#player_object#',
                    '#player_possessive#',
                    '#herika_dynamic#',
                    '#original_input#'
                ],
                [
                    $PLAYER_NAME,     // Add corresponding replacement
                    $PLAYER_BIOS,
                    implode(", ", $nearbyActors),
                    implode(", ", $possibleLocations),
                    implode("\n", array_map(function($ctx) { 
                        return $ctx['content']; 
                    }, array_slice($contextDataFull, -$settings['context_messages']))),
                    $playerPronouns['subject'],
                    $playerPronouns['object'],
                    $playerPronouns['possessive'],
                    $HERIKA_DYNAMIC,
                    $originalInput
                ],
                $section['content']
            );

            $contextMessage .= $content;
        }

        // Build the messages array with proper spacing
        $messages = [
            // System prompt for identity
            ['role' => 'system', 'content' => $systemPrompt . "\n\n"],
            
            // Context as system message
            ['role' => 'system', 'content' => $contextMessage],
            
            // The actual request as user message
            ['role' => 'user', 'content' => "\n" . str_replace(
                '#original_input#',
                $originalInput,
                $settings['translation_request']
            )]
        ];

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
            
            // Format the response with a single character name prefix
            $GLOBALS["gameRequest"][3] = $contextPrefix . $PLAYER_NAME . ": " . $response;
            error_log("minai: Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            error_log("minai: Failed to generate roleplay response, using original input");
        }

        // Disable roleplay mode after processing
        SetEnabled($PLAYER_NAME, "isRoleplaying", false);
    }
}

interceptRoleplayInput();

