<?php

header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";

// Always load the base configuration first
require_once("$pluginPath/config.base.php");
require_once("../logger.php");

// Then load the custom config if it exists, otherwise create it from base
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
} else {
    require_once("$pluginPath/config.php");
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

// Read config data from the file (GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Prepare the response using the loaded configuration
    $configData = array(
        // Basic settings
        "PROMPT_HEAD_OVERRIDE" => $GLOBALS["PROMPT_HEAD_OVERRIDE"],
        "use_narrator_profile" => $GLOBALS["use_narrator_profile"],
        "enforce_short_responses" => $GLOBALS["enforce_short_responses"],
        "stop_narrator_context_leak" => $GLOBALS["stop_narrator_context_leak"],
        "devious_narrator_eldritch_voice" => $GLOBALS["devious_narrator_eldritch_voice"],
        "devious_narrator_telvanni_voice" => $GLOBALS["devious_narrator_telvanni_voice"],
        "force_voice_type" => $GLOBALS["force_voice_type"],
        "self_narrator" => $GLOBALS["self_narrator"],
        "disable_nsfw" => $GLOBALS["disable_nsfw"],
        "restrict_nonfollower_functions" => $GLOBALS["restrict_nonfollower_functions"],
        "always_enable_functions" => $GLOBALS["always_enable_functions"],
        "force_aiff_name_to_ingame_name" => $GLOBALS["force_aiff_name_to_ingame_name"],
        
        // Arrays
        "commands_to_purge" => $GLOBALS["commands_to_purge"],
        "events_to_ignore" => $GLOBALS["events_to_ignore"],
        "voicetype_fallbacks" => $GLOBALS["voicetype_fallbacks"],
        
        // Feature flags
        "use_defeat" => $GLOBALS["use_defeat"],
        "disable_worn_equipment" => $GLOBALS["disable_worn_equipment"],
        "strip_emotes_from_output" => $GLOBALS["strip_emotes_from_output"],
        "realnames_support" => $GLOBALS["realnames_support"],
        "use_llm_fallback" => $GLOBALS["use_llm_fallback"],
        "enforce_single_json" => $GLOBALS["enforce_single_json"],
        
        // Server settings
        "xtts_server_override" => $GLOBALS["xtts_server_override"],
        "input_delay_for_radiance" => $GLOBALS["input_delay_for_radiance"],
        
        // Action prompts
        "action_prompts" => $GLOBALS["action_prompts"],
        
        // Add roleplay settings to GET response
        "roleplay_settings" => $GLOBALS["roleplay_settings"],
    );

    // Return the config data as JSON
    echo json_encode($configData);
}

// Update config data and write it back to the config.php file (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Decode JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            throw new Exception("Failed to decode JSON input");
        }

        // Manually build the new config content string
        $newConfig = "<?php\n";
        $newConfig .= "// This file overrides values from config.base.php\n\n";
        
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
        
        // Save roleplay settings
        $newConfig .= "\$GLOBALS['roleplay_settings'] = " . var_export($input['roleplay_settings'], true) . ";\n";

        // Write only the overrides to config.php
        $configFile = "$pluginPath/config.php";
        minai_log("info", "Writing config to $configFile");
        minai_log("info", "Config contents: " . $newConfig);
        
        $success = (file_put_contents($configFile, $newConfig) !== false);
        if (!$success) {
            throw new Exception("Failed to write config file");
        }

        // Send response
        echo json_encode(['status' => 'success']);
        
    } catch (Exception $e) {
        minai_log("info", "Config save error: " . $e->getMessage());
        http_response_code(500);
        $errorDetails = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'details' => ''
        ];
        
        // Add file permission details
        if (!is_writable("$pluginPath/config.php")) {
            $errorDetails['details'] .= "Config file is not writable\n";
            $errorDetails['details'] .= "Current permissions: " . decoct(fileperms("$pluginPath/config.php") & 0777) . "\n";
            $errorDetails['details'] .= "Current owner: " . posix_getpwuid(fileowner("$pluginPath/config.php"))['name'] . "\n";
        }
        
        // Add directory permission details
        if (!is_writable($pluginPath)) {
            $errorDetails['details'] .= "Plugin directory is not writable\n";
            $errorDetails['details'] .= "Directory permissions: " . decoct(fileperms($pluginPath) & 0777) . "\n";
            $errorDetails['details'] .= "Directory owner: " . posix_getpwuid(fileowner($pluginPath))['name'] . "\n";
        }
        
        // Add PHP error details if any
        $error = error_get_last();
        if ($error !== null) {
            $errorDetails['details'] .= "\nPHP Error:\n";
            $errorDetails['details'] .= "Type: " . $error['type'] . "\n";
            $errorDetails['details'] .= "Message: " . $error['message'] . "\n";
            $errorDetails['details'] .= "File: " . $error['file'] . " (Line " . $error['line'] . ")\n";
        }
        
        echo json_encode($errorDetails);
    }
}

