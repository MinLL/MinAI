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

    // General name replacement - replace all occurrences of the name
    $text = preg_replace('/\b' . preg_quote($name, '/') . '\b(?!\')/', 'you', $text);
    
    // Capitalize 'you' if it appears at the beginning of a sentence
    $text = preg_replace('/([.!?]\s+)you\b/', '$1You', $text);
    $text = preg_replace('/^you\b/', 'You', $text);

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

function GetNameFromProfile() {
    if (isset($_GET["profile"])) {
        $configPath = "/var/www/html/HerikaServer/conf".DIRECTORY_SEPARATOR."conf_{$_GET["profile"]}.php";
        minai_log("info", "Looking for profile at: " . $configPath);
        
        if (file_exists($configPath)) {
            // Read the file contents
            $contents = file_get_contents($configPath);
            if ($contents === false) {
                minai_log("error", "Failed to read profile file: " . $configPath);
                return $GLOBALS["HERIKA_NAME"];
            }
            
            // Find all matches of HERIKA_NAME assignments
            if (preg_match_all('/\$HERIKA_NAME\s*=\s*([\'"])((?:(?!\1).|\\\1)*)\1/', $contents, $matches, PREG_SET_ORDER)) {
                // Get the last match
                $lastMatch = end($matches);
                $name = stripslashes($lastMatch[2]); // Remove any escape characters
                minai_log("info", "Found last name assignment in profile: " . $name);
                return $name;
            } else {
                minai_log("warning", "Could not find any HERIKA_NAME assignments in profile file");
            }
        } else {
            minai_log("warning", "Profile file does not exist: " . $configPath);
        }
    } else {
        minai_log("info", "No profile specified, using default profile.");
    }
    
    minai_log("info", "Returning default HERIKA_NAME: " . $GLOBALS["HERIKA_NAME"]);
    return $GLOBALS["HERIKA_NAME"];
}

function getGaggedSpeech($name) {
    // Check for any type of gag
    if (!HasKeyword($name, "zad_DeviousGag") && 
        !HasKeyword($name, "zad_DeviousGagPanel") && 
        !HasKeyword($name, "zad_DeviousGagLarge")) {
        return "";
    }
    
    // Add gag context to the global roleplay settings
    if (HasKeyword($name, "zad_DeviousGagLarge")) {
        return "\nThe player is wearing a large ball gag and can only communicate through muffled sounds like 'mmph', 'nngh', and similar gagged noises. Keep their gagged speech short and clearly muffled.";
    } else if (HasKeyword($name, "zad_DeviousGagPanel")) {
        return "\nThe player is wearing a panel gag and can only speak in muffled, restricted sounds. Their speech should be short and clearly impeded.";
    } else {
        return "\nThe player is gagged and can only communicate through muffled sounds. Keep their speech short and obviously restricted.";
    }
}

function interceptRoleplayInput() {
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && (isPlayerInput() || $GLOBALS["gameRequest"][0] == "minai_roleplay")) {
        if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
            minai_log("info", "Intercepting minai_roleplay.");
        }
        else {
            minai_log("info", "Intercepting dialogue input for Translation. Original input: " . $GLOBALS["gameRequest"][3]);
        }
        
        // Initialize local variables with global defaults
        $PLAYER_NAME = $GLOBALS["PLAYER_NAME"];
        $PLAYER_BIOS = $GLOBALS["PLAYER_BIOS"];
        $HERIKA_NAME = GetNameFromProfile();
        $originalHerikaName = $HERIKA_NAME;
        $HERIKA_PERS = $GLOBALS["HERIKA_PERS"];
        $CONNECTOR = $GLOBALS["CONNECTOR"];
        $HERIKA_DYNAMIC = $GLOBALS["HERIKA_DYNAMIC"];

        // Disable roleplay mode after processing begins
        SetEnabled($PLAYER_NAME, "isRoleplaying", false);

        // Import narrator profile which may override the above variables
        if (file_exists(GetNarratorConfigPath())) {
            minai_log("info", "Using Narrator Profile");
            $path = GetNarratorConfigPath();    
            include($path);
        }
        minai_log("info", "HERIKA_NAME: " . $HERIKA_NAME);
        $HERIKA_NAME = $originalHerikaName;
        
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
        $contextMessages = $settings['context_messages'];
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
        $clothingStatus = convertToFirstPerson(GetUnifiedEquipmentContext($PLAYER_NAME, true), $PLAYER_NAME, $playerPronouns);
        
        $fertilityStatus = convertToFirstPerson(GetFertilityContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        $mindState = convertToFirstPerson(GetMindInfluenceContext(GetMindInfluenceState($PLAYER_NAME)), $PLAYER_NAME, $playerPronouns);
        $tattooStatus = convertToFirstPerson(GetTattooContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        // Add crime context
        $bountyStatus = convertToFirstPerson(GetBountyContext($PLAYER_NAME), $PLAYER_NAME, $playerPronouns);
        
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
            'PHYSICAL_DESCRIPTION' => $physDesc,
            'AROUSAL_STATUS' => $arousalStatus,
            'SURVIVAL_STATUS' => $survivalStatus,
            'CLOTHING_STATUS' => $clothingStatus,
            'FERTILITY_STATUS' => $fertilityStatus,
            'TATTOO_STATUS' => $tattooStatus,
            'BOUNTY_STATUS' => $bountyStatus,
            'HERIKA_NAME' => $HERIKA_NAME,
            'HERIKA_PERS' => $HERIKA_PERS,
            'MIND_STATE' => $mindState
        ];

        // Determine scene context
        $isExplicit = IsExplicitScene();
        $inCombat = IsEnabled($PLAYER_NAME, "inCombat");

        // Select appropriate prompts based on context
        if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
            // Roleplay mode
            $systemPrompt = $settings['roleplay_system_prompt'];
            $requestFormat = $settings['roleplay_request'];
            
            if ($isExplicit) {
                $systemPrompt = $settings['roleplay_system_prompt_explicit'];
                $requestFormat = $settings['roleplay_request_explicit'];
            } else if ($inCombat) {
                $systemPrompt = $settings['roleplay_system_prompt_combat'];
                $requestFormat = $settings['roleplay_request_combat'];
            }
        } else {
            // Translation mode
            $systemPrompt = $settings['system_prompt'];
            $requestFormat = $settings['translation_request'];
            
            if ($isExplicit) {
                $systemPrompt = $settings['system_prompt_explicit'];
                $requestFormat = $settings['translation_request_explicit'];
            } else if ($inCombat) {
                $systemPrompt = $settings['system_prompt_combat'];
                $requestFormat = $settings['translation_request_combat'];
            }
        }

        // Add mind control state
        $mindState = GetMindInfluenceState($PLAYER_NAME);
        $mindPrompt = GetMindInfluencePrompt($mindState, $isExplicit ? "explicit" : ($inCombat ? "combat" : "default"));
        $mindFormat = GetMindInfluenceRequestFormat($mindState, $isExplicit ? "explicit" : ($inCombat ? "combat" : "default"));
        if ($mindPrompt) {
            $systemPrompt .= " " . $mindPrompt;
        }

        // Add the request format addition if it exists
        if ($mindFormat) {
            $requestFormat .= " " . $mindFormat;
        }

        // Apply variable replacements to the selected prompts
        $systemPrompt = replaceVariables($systemPrompt, $variableReplacements);

        // Sort sections by their order if it exists
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
        // Convert to gagged speech if player is gagged
        $requestFormat .= getGaggedSpeech($PLAYER_NAME);
        // Build the messages array with proper spacing
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\n\n"],
            ['role' => 'system', 'content' => replaceVariables($contextMessage, $variableReplacements)],
            ['role' => 'user', 'content' => "\n" . replaceVariables($requestFormat, $variableReplacements)]
        ];

        // Debug log the messages being sent to LLM
        minai_log("info", "Messages being sent to LLM: " . json_encode($messages, JSON_PRETTY_PRINT));

        // Call LLM with specific parameters for dialogue generation
        $response = callLLM($messages, $CONNECTOR["openrouter"]["model"], [
            'temperature' => floatval($CONNECTOR["openrouter"]["temperature"]),
            'max_tokens' => 2048
        ]);

        if ($response !== null) {
            // Clean up the response - remove quotes and ensure it's dialogue-ready
            $response = trim($response, "\"' \n");
            
            // Remove any character name prefixes the LLM might have added
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME . ':') . '\s*/', '', $response);
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME) . ':\s*/', '', $response);
            
            // Strip all quotes from the response
            $response = str_replace(["", '"'], '', $response);
            
            
            minai_log("info", "Roleplay input transformed from \"{$originalInput}\" to \"{$response}\"");
            
            if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
                // rewrite as player input
                $GLOBALS["gameRequest"][0] = "inputtext";
                $GLOBALS["gameRequest"][3] = $response;
            }
            else {
                // Format the response with a single character name prefix
                $GLOBALS["gameRequest"][3] = $PLAYER_NAME . ": " . $response;
            }
            # minai_log("info", "Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            minai_log("info", "Failed to generate roleplay response, using original input");
        }

    }
}