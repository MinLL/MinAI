<?php

header('Content-Type: application/json');

$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";

// Always load the base configuration first
require_once("$pluginPath/config.base.php");
require_once("../logger.php");

// Then load the custom config if it exists, otherwise create it from base
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
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
    if (isset($_GET['defaults']) && $_GET['defaults'] === 'true') {
        // Return default configuration
        require_once("$pluginPath/config.base.php");
        // Merge NSFW prompts if NSFW is not disabled
        if (!$GLOBALS['disable_nsfw'] && isset($GLOBALS['action_prompts_nsfw'])) {
            $GLOBALS['action_prompts'] = array_merge($GLOBALS['action_prompts'], $GLOBALS['action_prompts_nsfw']);
        }
    }
    else {
        require_once("$pluginPath/config.base.php");
        // Merge NSFW prompts if NSFW is not disabled
        if (!$GLOBALS['disable_nsfw'] && isset($GLOBALS['action_prompts_nsfw'])) {
            $GLOBALS['action_prompts'] = array_merge($GLOBALS['action_prompts'], $GLOBALS['action_prompts_nsfw']);
        }
        require_once("$pluginPath/config.php");
    }
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
        "enable_prompt_slop_cleanup" => $GLOBALS["enable_prompt_slop_cleanup"],
        
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
        "CHIM_NO_EXAMPLES" => $GLOBALS["CHIM_NO_EXAMPLES"],
        
        // Server settings
        "input_delay_for_radiance" => intval($GLOBALS["input_delay_for_radiance"]),
        
        // Inventory settings
        "inventory_items_limit" => intval($GLOBALS["inventory_items_limit"]),
        "use_item_relevancy_scoring" => $GLOBALS["use_item_relevancy_scoring"],
        
        // Metrics settings
        "minai_metrics_enabled" => $GLOBALS["minai_metrics_enabled"],
        "minai_metrics_sampling_rate" => floatval($GLOBALS["minai_metrics_sampling_rate"]),
        
        // Action prompts
        "action_prompts" => array(
            "singing" => $GLOBALS["action_prompts"]["singing"],
            "player_diary" => $GLOBALS["action_prompts"]["player_diary"],
            "follower_diary" => $GLOBALS["action_prompts"]["follower_diary"],
            "self_narrator_explicit" => $GLOBALS["action_prompts"]["self_narrator_explicit"],
            "self_narrator_normal" => $GLOBALS["action_prompts"]["self_narrator_normal"],
            "explicit_scene" => $GLOBALS["action_prompts"]["explicit_scene"],
            "normal_scene" => $GLOBALS["action_prompts"]["normal_scene"]
        ),
        
        // Add roleplay settings to GET response
        "roleplay_settings" => $GLOBALS["roleplay_settings"],
        
        // Add context builder settings
        "minai_context" => $GLOBALS["minai_context"],
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
            throw new Exception("Failed to decode JSON input: " . json_last_error_msg());
        }

        // Validate required fields
        $requiredFields = [
            'PROMPT_HEAD_OVERRIDE',
            'use_narrator_profile',
            'enforce_short_responses',
            // ... add all required fields ...
        ];

        foreach ($requiredFields as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        minai_log("info", "Building new configuration file...");

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
        $newConfig .= "\$GLOBALS['enable_prompt_slop_cleanup'] = " . ($input['enable_prompt_slop_cleanup'] ? 'true' : 'false') . ";\n";
        
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
        $newConfig .= "\$GLOBALS['CHIM_NO_EXAMPLES'] = " . ($input['CHIM_NO_EXAMPLES'] ? 'true' : 'false') . ";\n";
        
        // Server settings
        $newConfig .= "\$GLOBALS['input_delay_for_radiance'] = " . (intval($input['input_delay_for_radiance']) ?: 15) . ";\n";
        
        // Inventory settings
        $newConfig .= "\$GLOBALS['inventory_items_limit'] = " . (intval($input['inventory_items_limit']) ?: 5) . ";\n";
        $newConfig .= "\$GLOBALS['use_item_relevancy_scoring'] = " . ($input['use_item_relevancy_scoring'] ? 'true' : 'false') . ";\n";
        
        // Metrics settings
        $newConfig .= "\$GLOBALS['minai_metrics_enabled'] = " . ($input['minai_metrics_enabled'] ? 'true' : 'false') . ";\n";
        $newConfig .= "\$GLOBALS['minai_metrics_sampling_rate'] = " . (floatval($input['minai_metrics_sampling_rate']) ?: 0.1) . ";\n";
        
        // Action prompts
        $newConfig .= "\$GLOBALS['action_prompts'] = " . buildAssociativeArrayString(array(
            'singing' => $input['action_prompts']['singing'],
            'player_diary' => $input['action_prompts']['player_diary'],
            'follower_diary' => $input['action_prompts']['follower_diary'],
            'self_narrator_explicit' => $input['action_prompts']['self_narrator_explicit'],
            'self_narrator_normal' => $input['action_prompts']['self_narrator_normal'],
            'explicit_scene' => $input['action_prompts']['explicit_scene'],
            'normal_scene' => $input['action_prompts']['normal_scene']
        )) . ";\n";
        
        // Save roleplay settings
        $newConfig .= "\$GLOBALS['roleplay_settings'] = " . var_export($input['roleplay_settings'], true) . ";\n";
        
        // Save context builder settings
        $newConfig .= "\$GLOBALS['minai_context'] = " . var_export($input['minai_context'], true) . ";\n";

        // Write configuration
        $configFile = "$pluginPath/config.php";
        minai_log("info", "Writing configuration to $configFile");
        
        if (!is_writable($pluginPath)) {
            throw new Exception("Plugin directory is not writable");
        }
        
        if (file_exists($configFile) && !is_writable($configFile)) {
            throw new Exception("Configuration file exists but is not writable");
        }

        $success = (file_put_contents($configFile, $newConfig) !== false);
        if (!$success) {
            throw new Exception("Failed to write configuration file");
        }

        // Clear the configuration cache
        clearstatcache(true, $configFile);
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        minai_log("info", "Configuration saved successfully");
        echo json_encode([
            'status' => 'success',
            'message' => 'Configuration saved successfully',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        minai_log("error", "Configuration save error: " . $e->getMessage());
        http_response_code(500);
        
        $errorDetails = [
            'status' => 'error',
            'message' => $e->getMessage(),
            'details' => '',
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Add detailed error information
        if (!is_writable("$pluginPath/config.php")) {
            $errorDetails['details'] .= "Config file is not writable\n";
            $errorDetails['details'] .= "Current permissions: " . decoct(fileperms("$pluginPath/config.php") & 0777) . "\n";
            $errorDetails['details'] .= "Current owner: " . posix_getpwuid(fileowner("$pluginPath/config.php"))['name'] . "\n";
        }
        
        if (!is_writable($pluginPath)) {
            $errorDetails['details'] .= "Plugin directory is not writable\n";
            $errorDetails['details'] .= "Directory permissions: " . decoct(fileperms($pluginPath) & 0777) . "\n";
            $errorDetails['details'] .= "Directory owner: " . posix_getpwuid(fileowner($pluginPath))['name'] . "\n";
        }
        
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

