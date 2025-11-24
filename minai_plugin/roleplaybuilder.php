<?php
require_once("/var/www/html/HerikaServer/lib/data_functions.php");
// Add the system prompt context builder include
require_once(__DIR__ . "/contextbuilders/system_prompt_context.php");
require_once("util.php");
require_once(__DIR__ . "/utils/format_util.php");
require_once(__DIR__ . "/utils/prompt_slop_cleanup.php");

function convertRelationshipStatus($targetActor) {
    $relationshipRank = GetActorValue($targetActor, "relationshipRank");
    if ($relationshipRank == 0) {
        return "a stranger";
    } else if ($relationshipRank < 1) {
        return "someone you dislike";
    } else {
        return "someone you are fond of";
    }
}

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

    // Replace "has you" with "have your"
    $text = str_replace(" has you ", " have your ", $text);
    $text = str_replace("Has you ", "Have your ", $text);

    // Replace "you has" with "you have"
    $text = str_replace(" you has ", " you have ", $text);
    $text = str_replace("You has ", "You have ", $text);
    // Clean up any double spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

function GetNameFromProfile() {
    if (isset($_GET["profile"])) {
        $configPath = "/var/www/html/HerikaServer/conf/conf_{$_GET["profile"]}.php";
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
    if (!HasEquipmentKeyword($name, "zad_DeviousGag") && 
        !HasEquipmentKeyword($name, "zad_DeviousGagPanel") && 
        !HasEquipmentKeyword($name, "zad_DeviousGagLarge")) {
        return "";
    }
    
    // Add gag context to the global roleplay settings
    if (HasEquipmentKeyword($name, "zad_DeviousGagLarge")) {
        return "\nThe player is wearing a large ball gag and can only communicate through muffled sounds like 'mmph', 'nngh', and similar gagged noises. Keep their gagged speech short and clearly muffled.";
    } else if (HasEquipmentKeyword($name, "zad_DeviousGagPanel")) {
        return "\nThe player is wearing a panel gag and can only speak in muffled, restricted sounds. Their speech should be short and clearly impeded.";
    } else {
        return "\nThe player is gagged and can only communicate through muffled sounds. Keep their speech short and obviously restricted.";
    }
}

function interceptRoleplayInput() {
    minai_start_timer('interceptRoleplayInput', 'preprocessing_php');
    if (IsEnabled($GLOBALS["PLAYER_NAME"], "isRoleplaying") && (isPlayerInput() || $GLOBALS["gameRequest"][0] == "minai_roleplay")) {
        $b_roleplay = false;
        if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
            $b_roleplay = true;
            minai_log("info", "Intercepting minai_roleplay.");
        }
        
        $HERIKA_PERS00 = "";
        $HERIKA_DYNAMIC00 = "";

        // Initialize local variables with global defaults
        $CONNECTOR = $GLOBALS["CONNECTOR"];

        $HERIKA_NAME = GetNameFromProfile();
        //$HERIKA_NAME = $GLOBALS["RP_TARGET"] ?? GetOriginalHerikaName(); //GetNameFromProfile();
        $originalHerikaName = $HERIKA_NAME;
        $b_narrator = (($HERIKA_NAME == "The Narrator") ? true : false);
        
        $PLAYER_NAME = $GLOBALS["PLAYER_NAME"] ?? "";
        $PLAYER_BIOS = ($GLOBALS["PLAYER_BIOS"] ?? "");
        $PLAYER_SPEECH_STYLE = ($GLOBALS["PLAYER_SPEECH_STYLE"] ?? "");

        if (!$b_roleplay) { // check for tap errors or empty input and convert them from translation to roleplay 
            $s_msg1 = ($GLOBALS["gameRequest"][3] ?? "");
            $s_msg2 = "";
            if (strlen($s_msg1) > 0) {
                $s_msg2 = trim(str_ireplace([$PLAYER_NAME,':'], ['',''], $s_msg1)); 
            }
            if (strlen($s_msg2) > 0) {
                minai_log("info", "Intercepting dialogue input for Translation. Original input={$s_msg1}");
            } else {
                $b_roleplay = true;
                minai_log("info", "Empty input for Translation, consider Roleplay. Original input={$s_msg1}");
            }
        }

        if (!isset($GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"])) {
            $playerName = $PLAYER_NAME;
            $pr_player = GetActorPronouns($playerName);
            //include("dialogue_prompt.php");
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$playerName} would naturally respond.
Evaluate at least five plausible responses {$playerName} would naturally give to {$pr_player["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pr_player["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it as your final response in plain text.
Do not mention the probability and how you decided to choose the answer.
</verbalized_sampling>";
        }
        $vsampling = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"] ?? "";
        //$output_format = $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"] ?? "";

        $prompt_head = ($GLOBALS["PROMPT_HEAD"] ?? "");
        $use_prompt_head_override = $GLOBALS['use_prompt_head_override'] ?? false;
        if ($use_prompt_head_override) {
            $prompt_head_override = $GLOBALS['PROMPT_HEAD_OVERRIDE'] ?? "";
            if (strlen(trim($prompt_head_override)) > 1) {
                $prompt_head = $prompt_head_override;
            }
        }

        // Disable roleplay mode after processing begins
        SetEnabled($PLAYER_NAME, "isRoleplaying", false);

        // Import narrator profile which may override the above variables
        if (($b_narrator) && (file_exists(GetNarratorConfigPath()))) {
            minai_log("info", "Using Narrator Profile");
            $path = GetNarratorConfigPath();    
            include($path);
        } else { // not narrator, read bio from config

            if (isset($_GET["profile"])) {
                $configPath = "/var/www/html/HerikaServer/conf/conf_{$_GET["profile"]}.php";
                //minai_log("info", "Looking for profile at: " . $configPath);
                if (file_exists($configPath)) {
                    // Read the file contents
                    $contents = file_get_contents($configPath);
                    if ($contents === false) {
                        minai_log("error", "Failed to read profile file: " . $configPath);
                    } else {
                        include($configPath);
                        //error_log(" HERIKA_PERS from config: " . $HERIKA_PERS);

                        $HERIKA_PERS00 = $HERIKA_PERS ?? "";
                        $HERIKA_DYNAMIC00 = ($HERIKA_DYNAMIC ?? "") . " \n" .  
                            ($HERIKA_BACKGROUND ?? "") . " \n" . 
                            ($HERIKA_PERSONALITY ?? "") .  " \n" . 
                            ($HERIKA_APPEARANCE ?? "") .  " \n" . 
                            ($HERIKA_RELATIONSHIPS ?? "") .  " \n" . 
                            ($HERIKA_OCCUPATION ?? "") .  " \n" . 
                            ($HERIKA_SKILLS ?? "") . " \n" . 
                            ($HERIKA_GOALS ?? "");
                    }
                } else {
                    minai_log("warning", "Profile file does not exist: " . $configPath);
                }
            } else {
                minai_log("info", "No GET profile specified");
            }
        } 
        
        
        minai_log("info", "roleplay interlocutor HERIKA_NAME: " . $HERIKA_NAME);
        //error_log("roleplay interlocutor herika name: $HERIKA_NAME " ); // debug
        
        $HERIKA_NAME = $originalHerikaName;
        
        $b_narrator = (($HERIKA_NAME == "The Narrator") ? true : false);
        
        if (!$b_narrator) {
            $HERIKA_PERS = $HERIKA_PERS00;
            $HERIKA_DYNAMIC = $HERIKA_DYNAMIC00;
            //error_log("roleplay interlocutor herika: $HERIKA_NAME - pers: $HERIKA_PERS " ); // debug
        } else {
            $HERIKA_PERS = "";
            $HERIKA_DYNAMIC = "";
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
        minai_start_timer('getContextData', 'interceptRoleplayInput');
        $contextMessages = $settings['context_messages'] ?? 21;
        $contextDataHistoric = GetRecentContext("", ($contextMessages * 2));
        
        // Get info about location and NPCs
        $contextDataWorld = DataLastInfoFor("", -5);
        
        // Get lists of valid names and locations
        $nearbyActors = array_filter(array_map('trim', explode('|', DataBeingsInRange())));
        $possibleLocations = DataPosibleLocationsToGo();
        
        // Combine contexts
        $contextDataFull = array_merge($contextDataWorld, $contextDataHistoric);
        $contextDataFull = cleanupSlop($contextDataFull);
        minai_stop_timer('getContextData');
        
        // Build messages array using config settings
        $messages = [];
        
        // Get player pronouns
        $playerPronouns = GetActorPronouns($PLAYER_NAME);
        
        // Get contexts and convert to first person
        minai_start_timer('buildPlayerContext', 'interceptRoleplayInput');
        $params = ['herika_name' => "The Narrator", 'player_name' => $PLAYER_NAME];
        $physDesc = convertToFirstPerson(callContextBuilder('physical_description', $params), $PLAYER_NAME, $playerPronouns);
        $arousalStatus = convertToFirstPerson(callContextBuilder('arousal', $params), $PLAYER_NAME, $playerPronouns);
        $survivalStatus = convertToFirstPerson(callContextBuilder('survival', $params), $PLAYER_NAME, $playerPronouns);
        $clothingStatus = convertToFirstPerson(GetUnifiedEquipmentContext($PLAYER_NAME, true), $PLAYER_NAME, $playerPronouns);
        
        $fertilityStatus = convertToFirstPerson(callContextBuilder('fertility', $params), $PLAYER_NAME, $playerPronouns);
        $mindState = convertToFirstPerson(callContextBuilder('mind_influence', $params), $PLAYER_NAME, $playerPronouns);
        $tattooStatus = convertToFirstPerson(callContextBuilder('tattoos', $params), $PLAYER_NAME, $playerPronouns);
        // Add crime context
        $bountyStatus = convertToFirstPerson(callContextBuilder('bounty', $params), $PLAYER_NAME, $playerPronouns);
        $relationshipStatus = convertRelationshipStatus($HERIKA_NAME);
        $weather = convertToFirstPerson(callContextBuilder('weather', $params), $PLAYER_NAME, $playerPronouns);
        $vitals = convertToFirstPerson(callContextBuilder('vitals', $params), $PLAYER_NAME, $playerPronouns);
        minai_stop_timer('buildPlayerContext');
        
        $PLAYER_BIOS = ($GLOBALS["PLAYER_BIOS"] ?? "");
        $PLAYER_SPEECH_STYLE = ($GLOBALS["PLAYER_SPEECH_STYLE"] ?? "");

        //error_log("p speech:".$PLAYER_BIOS); // debug

        // Replace variables in system prompt and request
        $variableReplacements = [
            'PROMPT_HEAD' => $prompt_head,
            'PLAYER_BIOS' => replaceVariables(($PLAYER_BIOS . "\n# Speech style\n" .$PLAYER_SPEECH_STYLE), ['PLAYER_NAME' => $PLAYER_NAME]),
            'NEARBY_ACTORS' => implode(", ", $nearbyActors),
            'NEARBY_LOCATIONS' => implode(", ", $possibleLocations),
            'RECENT_EVENTS' => implode("\n", array_map(function($ctx) { 
                return $ctx['content']; 
            }, array_slice($contextDataFull, -$contextMessages))),
            'PLAYER_SUBJECT' => $playerPronouns['subject'],
            'PLAYER_OBJECT' => $playerPronouns['object'],
            'PLAYER_POSSESSIVE' => $playerPronouns['possessive'],
            'ORIGINAL_INPUT' => $originalInput,
            'PHYSICAL_DESCRIPTION' => $physDesc,
            'AROUSAL_STATUS' => $arousalStatus,
            'SURVIVAL_STATUS' => $survivalStatus,
            'CLOTHING_STATUS' => $clothingStatus,
            'FERTILITY_STATUS' => $fertilityStatus,
            'TATTOO_STATUS' => $tattooStatus,
            'BOUNTY_STATUS' => $bountyStatus,
            'MIND_STATE' => $mindState,
            'WEATHER' => $weather,
            'VITALS' => $vitals,

            'RELATIONSHIP_STATUS' => $relationshipStatus,
            'HERIKA_PERS' => ($b_narrator ? "" : $HERIKA_PERS),
            'HERIKA_DYNAMIC' => ($b_narrator ? "" : $HERIKA_DYNAMIC),
            'HERIKA_NAME' => $HERIKA_NAME,
            'PLAYER_NAME' => $PLAYER_NAME,
            //'OUTPUT_FORMATTING' => $output_format,
            'VSAMPLING_PLAYER' => $vsampling, //replaceVariables(($GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"] ?? ""), ['PLAYER_NAME' => $PLAYER_NAME]),
            'DEVICE_STATUS' => '' // Remove old device status string
        ];

        // Determine scene context
        minai_start_timer('determineContext', 'interceptRoleplayInput');
        $isExplicit = IsExplicitScene();
        $inCombat = IsEnabled($PLAYER_NAME, "inCombat");

        //error_log(" request: ".$GLOBALS["gameRequest"][0]); // debug

        // Select appropriate prompts based on context
        //if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
        if ($b_roleplay) { // Roleplay mode
            $systemPrompt = $settings['roleplay_system_prompt'];
            $requestFormat = $settings['roleplay_request'];
            
            if ($isExplicit) {
                $systemPrompt = $settings['roleplay_system_prompt_explicit'];
                $requestFormat = $settings['roleplay_request_explicit'];
            } else if ($inCombat) {
                $systemPrompt = $settings['roleplay_system_prompt_combat'];
                $requestFormat = $settings['roleplay_request_combat'];
            }
        } else {  // Translation mode
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

        //error_log(" prompt: ".$systemPrompt); // debug

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
        minai_stop_timer('determineContext');

        // Apply variable replacements to the selected prompts
        minai_start_timer('promptFormatting', 'interceptRoleplayInput');
        $sectionMap = [
            "HERIKA_PERS" => "## PERSONALITY",
            "DYNAMIC_STATE" => "## CURRENT STATE",
            "PHYSICAL_DESCRIPTION" => "## PHYSICAL APPEARANCE",
            "CLOTHING_STATUS" => "## EQUIPMENT",
            "TATTOO_STATUS" => "## TATTOOS",
            "AROUSAL_STATUS" => "## AROUSAL STATUS",
            "FERTILITY_STATUS" => "## FERTILITY STATUS",
            "SURVIVAL_STATUS" => "## SURVIVAL STATUS",
            "MIND_STATE" => "## MENTAL STATE",
            "WEATHER" => "## WEATHER",
            "NEARBY_ACTORS" => "## NEARBY CHARACTERS",
            "PLAYER_BIOS" => "## YOUR BACKGROUND",
            "BOUNTY_STATUS" => "## BOUNTY STATUS"
        ];

        // Helper function to replace variables with section headers
        function inflatePrompt($text, $replacements) {
            global $sectionMap;
            return preg_replace_callback('/\{([^}]+)\}/', function($matches) use ($replacements, $sectionMap) {
                $key = $matches[1];
                if (isset($replacements[$key])) {
                    // If the key exists in sectionMap, prepend the section header
                    $value = $replacements[$key];
                    if (isset($sectionMap[$key])) {
                        return $sectionMap[$key] . "\n" . $value;
                    }
                    return $value;
                }
                return $matches[0];
            }, $text);
        }

        $systemPrompt = replaceVariables(inflatePrompt($systemPrompt, $variableReplacements), $variableReplacements);
        
        // Sort sections by their order if it exists
        $sections = $settings['sections'];
        if (isset(reset($sections)['order'])) {
            uasort($sections, function($a, $b) {
                return ($a['order'] ?? 0) - ($b['order'] ?? 0);
            });
        }

        //"YOUR_INTERLOCUTOR"
        if ($b_narrator) {
            if (isset($sections["YOUR_INTERLOCUTOR"]["enabled"]))
                $sections["YOUR_INTERLOCUTOR"]["enabled"] = false; // disable "YOUR_INTERLOCUTOR" when target is narrator
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
            
            // Format the section content using the shared formatter
            //$content = FormatUtil::formatContext($content);

            $contextMessage .= $content;
        }
        //$contextMessage = str_replace("<\/","</",$contextMessage);
        
        // Convert to gagged speech if player is gagged
        $requestFormat .= getGaggedSpeech($PLAYER_NAME);
        
        // Build the messages array with proper spacing
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\n\n"],
            ['role' => 'system', 'content' => replaceVariables($contextMessage, $variableReplacements)],
            ['role' => 'user', 'content' => "\n\n" . replaceVariables($requestFormat, $variableReplacements)]
        ];
        minai_stop_timer('promptFormatting');

        // Get prompt sizes for telemetry
        //$systemPromptSize = strlen($messages[0]['content']);
        //$contextMessageSize = strlen($messages[1]['content']);
        //$userPromptSize = strlen($messages[2]['content']);
        //$totalPromptSize = $systemPromptSize + $contextMessageSize + $userPromptSize;
        

        // Debug log the messages being sent to LLM
        minai_log("info", "Messages being sent to LLM: " . json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Call LLM with specific parameters for dialogue generation
        minai_start_timer('callLLM', 'interceptRoleplayInput');
        
        /*$response = callLLM($messages, $CONNECTOR["openrouter"]["model"], [
            'temperature' => floatval($CONNECTOR["openrouter"]["temperature"]),
            'max_tokens' => 2048
        ]);*/
        $response = callLLM($messages, null, []);

        minai_stop_timer('callLLM');
        
        minai_start_timer('processResponse', 'interceptRoleplayInput');
        if ($response !== null) {
            $responseSize = strlen($response);
            minai_log("info", "Response size: " . $responseSize . " characters");
            
            // Clean up the response - remove quotes and ensure it's dialogue-ready
            $response = trim($response, "\"' \n");
            
            // Remove any character name prefixes the LLM might have added
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME . ':') . '\s*/', '', $response);
            $response = preg_replace('/^' . preg_quote($PLAYER_NAME) . ':\s*/', '', $response);
            
            // Strip all quotes from the response
            //$response = str_replace(["", '"'], '', $response);

            // replace nasty Unicode characters
            $response = str_replace(
                ["\0", "‐", "‑", " — ", "—",   "‘", "’", "‚", "‛", "。。。", "…",   '"' ],
                [''  , "-", "-", " - ", " - ", "'", "'", "'", "'", "...", "...", ''  ],
                $response);
            
            $processedResponseSize = strlen($response);
            minai_log("info", "Processed response size: " . $processedResponseSize . " characters");
            minai_log("info", "Roleplay input transformed from \"{$originalInput}\" to \"{$response}\"");
            
            if ($GLOBALS["gameRequest"][0] == "minai_roleplay") {
                // rewrite as player input
                $GLOBALS["gameRequest"][0] = "inputtext";
                $GLOBALS["gameRequest"][3] = $PLAYER_NAME . ": " . $response;
            }
            else {
                // Format the response with a single character name prefix
                $GLOBALS["gameRequest"][3] = $PLAYER_NAME . ": " . $response;
            }
            # minai_log("info", "Final gameRequest[3]: " . $GLOBALS["gameRequest"][3]);
        } else {
            minai_log("info", "Failed to generate roleplay response, using original input");
        }
        minai_stop_timer('processResponse');

    }
    minai_stop_timer('interceptRoleplayInput');
}