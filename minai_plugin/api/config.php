<?php

header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}

// Define the directory where the config file is located
$configFile = "$pluginPath/config.php";

// Read config data from the file (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_once($configFile);

    // Prepare the response by extracting all $GLOBALS values
    $configData = array(
        // Basic settings
        "PROMPT_HEAD_OVERRIDE" => isset($GLOBALS["PROMPT_HEAD_OVERRIDE"]) ? $GLOBALS["PROMPT_HEAD_OVERRIDE"] : "",
        "use_narrator_profile" => isset($GLOBALS["use_narrator_profile"]) ? $GLOBALS["use_narrator_profile"] : false,
        "enforce_short_responses" => isset($GLOBALS["enforce_short_responses"]) ? $GLOBALS["enforce_short_responses"] : false,
        "stop_narrator_context_leak" => isset($GLOBALS["stop_narrator_context_leak"]) ? $GLOBALS["stop_narrator_context_leak"] : true,
        "devious_narrator_eldritch_voice" => isset($GLOBALS["devious_narrator_eldritch_voice"]) ? $GLOBALS["devious_narrator_eldritch_voice"] : "dragon",
        "devious_narrator_telvanni_voice" => isset($GLOBALS["devious_narrator_telvanni_voice"]) ? $GLOBALS["devious_narrator_telvanni_voice"] : "TelvanniNarrator",
        "force_voice_type" => isset($GLOBALS["force_voice_type"]) ? $GLOBALS["force_voice_type"] : false,
        "self_narrator" => isset($GLOBALS["self_narrator"]) ? $GLOBALS["self_narrator"] : false,
        "disable_nsfw" => isset($GLOBALS["disable_nsfw"]) ? $GLOBALS["disable_nsfw"] : false,
        "restrict_nonfollower_functions" => isset($GLOBALS["restrict_nonfollower_functions"]) ? $GLOBALS["restrict_nonfollower_functions"] : true,
        "always_enable_functions" => isset($GLOBALS["always_enable_functions"]) ? $GLOBALS["always_enable_functions"] : true,
        "force_aiff_name_to_ingame_name" => isset($GLOBALS["force_aiff_name_to_ingame_name"]) ? $GLOBALS["force_aiff_name_to_ingame_name"] : true,
        
        // Arrays
        "commands_to_purge" => isset($GLOBALS["commands_to_purge"]) ? $GLOBALS["commands_to_purge"] : Array("TakeASeat", "Folow"),
        "events_to_ignore" => isset($GLOBALS["events_to_ignore"]) ? $GLOBALS["events_to_ignore"] : Array("rpg_lvlup"),
        "voicetype_fallbacks" => isset($GLOBALS["voicetype_fallbacks"]) ? $GLOBALS["voicetype_fallbacks"] : array(),
        
        // Feature flags
        "use_defeat" => isset($GLOBALS["use_defeat"]) ? $GLOBALS["use_defeat"] : false,
        "disable_worn_equipment" => isset($GLOBALS["disable_worn_equipment"]) ? $GLOBALS["disable_worn_equipment"] : false,
        "strip_emotes_from_output" => isset($GLOBALS["strip_emotes_from_output"]) ? $GLOBALS["strip_emotes_from_output"] : false,
        "realnames_support" => isset($GLOBALS["realnames_support"]) ? $GLOBALS["realnames_support"] : false,
        "use_llm_fallback" => isset($GLOBALS["use_llm_fallback"]) ? $GLOBALS["use_llm_fallback"] : false,
        "enforce_single_json" => isset($GLOBALS["enforce_single_json"]) ? $GLOBALS["enforce_single_json"] : false,
        
        // Server settings
        "xtts_server_override" => isset($GLOBALS["xtts_server_override"]) ? $GLOBALS["xtts_server_override"] : "",
        "input_delay_for_radiance" => isset($GLOBALS["input_delay_for_radiance"]) ? $GLOBALS["input_delay_for_radiance"] : 15,
        
        // Action prompts
        "action_prompts" => isset($GLOBALS["action_prompts"]) ? $GLOBALS["action_prompts"] : array(
            "singing" => "Respond with a song from #player_name#. Be creative, and match the mood of the scene.",
            "self_narrator_explicit" => "Respond with #target#'s immediate thoughts, emotions, and internal reactions to the physical and emotional sensations they are experiencing right now. Focus particularly on any erotic elements happening within the scene. Respond in first person as #target#, staying fully in the present moment and focusing on their personal, subjective experience rather than describing the situation itself. Keep the response deeply personal and reflective of how #target# would genuinely react.",
            "self_narrator_normal" => "Respond as #target#, thinking privately to themselves about the current situation and recent events. Stay in first person, capturing their genuine thoughts, emotions, and internal conflicts. Focus on their personal perspective, biases, and feelings rather than an objective summary of events. Keep the response introspective and true to how #target# would process and react internally.",
            "explicit_scene" => "Choose the ACTION or TALK that best conveys your character's immediate physical and emotional responses. Emphasize conversation and verbal expression where appropriate, but also use ACTIONS that reflect #herika_name#'s sensations and feelings when interacting with #target#. Avoid narration and emoting.",
            "normal_scene" => "Choose the ACTION or TALK that best fits the current context and character mood when interacting with #target#. Whenever possible, engage in dialogue to express thoughts and intentions, but also use ACTIONS for tasks like interacting with items, trading, inspecting the world, interacting with other characters, attacking, or showing your character's needs. Avoid narration and emoting."
        )
    );

    // Return the config data as JSON
    echo json_encode($configData);
}

// Update config data and write it back to the config.php file (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Manually build the new config content string
    $newConfig = "<?php\n";
    
    // Basic settings
    $newConfig .= "\$GLOBALS['PROMPT_HEAD_OVERRIDE'] = \"" . ($input['PROMPT_HEAD_OVERRIDE']) . "\";\n";
    $newConfig .= "\$GLOBALS['use_narrator_profile'] = " . ($input['use_narrator_profile'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['enforce_short_responses'] = " . ($input['enforce_short_responses'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['stop_narrator_context_leak'] = " . ($input['stop_narrator_context_leak'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['devious_narrator_eldritch_voice'] = \"" . ($input['devious_narrator_eldritch_voice']) . "\";\n";
    $newConfig .= "\$GLOBALS['devious_narrator_telvanni_voice'] = \"" . ($input['devious_narrator_telvanni_voice']) . "\";\n";
    $newConfig .= "\$GLOBALS['force_voice_type'] = " . ($input['force_voice_type'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['self_narrator'] = " . ($input['self_narrator'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['disable_nsfw'] = " . ($input['disable_nsfw'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['restrict_nonfollower_functions'] = " . ($input['restrict_nonfollower_functions'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['always_enable_functions'] = " . ($input['always_enable_functions'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['force_aiff_name_to_ingame_name'] = " . ($input['force_aiff_name_to_ingame_name'] ? 'true' : 'false') . ";\n";
    
    // Arrays
    $newConfig .= "\$GLOBALS['commands_to_purge'] = " . buildArrayString($input['commands_to_purge']) . ";\n";
    $newConfig .= "\$GLOBALS['events_to_ignore'] = " . buildArrayString($input['events_to_ignore']) . ";\n";
    $newConfig .= "\$GLOBALS['voicetype_fallbacks'] = " . buildAssociativeArrayString($input['voicetype_fallbacks']) . ";\n";
    
    // Feature flags
    $newConfig .= "\$GLOBALS['use_defeat'] = " . ($input['use_defeat'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['disable_worn_equipment'] = " . ($input['disable_worn_equipment'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['strip_emotes_from_output'] = " . ($input['strip_emotes_from_output'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['realnames_support'] = " . ($input['realnames_support'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['use_llm_fallback'] = " . ($input['use_llm_fallback'] ? 'true' : 'false') . ";\n";
    $newConfig .= "\$GLOBALS['enforce_single_json'] = " . ($input['enforce_single_json'] ? 'true' : 'false') . ";\n";
    
    // Server settings
    $newConfig .= "\$GLOBALS['xtts_server_override'] = \"" . ($input['xtts_server_override']) . "\";\n";
    $newConfig .= "\$GLOBALS['input_delay_for_radiance'] = " . intval($input['input_delay_for_radiance']) . ";\n";
    
    // Action prompts
    $newConfig .= "\$GLOBALS['action_prompts'] = " . buildAssociativeArrayString($input['action_prompts']) . ";\n";

    // Save the new config to the config.php file
    $success = (file_put_contents($configFile, $newConfig) !== false);

    // Send response
    echo json_encode(['status' => $success?'success':'error']);
}

// Function to build a string for indexed arrays using Array("value1", "value2") format
function buildArrayString($array) {
    $arrayString = 'Array(';
    $values = array_map(function($value) {
        return '"' . ($value) . '"';
    }, $array);
    $arrayString .= implode(', ', $values);
    $arrayString .= ')';
    return $arrayString;
}

// Function to build a string for associative arrays using Array("key" => "value") format
function buildAssociativeArrayString($array) {
    $arrayString = 'Array(';
    $elements = [];
    foreach ($array as $key => $value) {
        // Escape only double quotes and backslashes in the value
        $escapedValue = str_replace(
            ['\\', '"'], 
            ['\\\\', '\\"'], 
            $value
        );
        $elements[] = '"' . $key . '" => "' . $escapedValue . '"';
    }
    $arrayString .= implode(', ', $elements);
    $arrayString .= ')';
    return $arrayString;
}
?>
