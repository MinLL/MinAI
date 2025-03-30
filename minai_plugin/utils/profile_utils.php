<?php


Function GetNarratorConfigPath() {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = "/var/www/html/HerikaServer/";
    $newConfFile=md5("Narrator");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function SetNarratorProfile() {
    static $narratorProfileCache = null;

    if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && $GLOBALS["use_narrator_profile"]) {
        if (!file_exists(GetNarratorConfigPath())) {
            minai_log("info", "Initializing Narrator Profile");
            createProfile("Narrator", [
                "HERIKA_NAME" => "The Narrator",
                "HERIKA_PERS" => "You are The Narrator in a Skyrim adventure. You will only talk to #PLAYER_NAME#. You refer to yourself as 'The Narrator'. Only #PLAYER_NAME# can hear you. Your goal is to comment on #PLAYER_NAME#'s playthrough, and occasionally, give some hints. NO SPOILERS. Talk about quests and last events."
            ], true);
        }

        // If we haven't loaded the narrator profile yet, load it from file
        if ($narratorProfileCache === null) {
            // First time load - get the profile from file
            // global $HERIKA_NAME; // Don't let users overwrite narrator profile name
            global $PROMPT_HEAD;
            global $PLAYER_BIOS;
            global $HERIKA_PERS;
            global $HERIKA_DYNAMIC;
            global $DYNAMIC_PROFILE;
            global $RECHAT_H;
            global $RECHAT_P;
            global $BORED_EVENT;
            global $CONTEXT_HISTORY;
            global $HTTP_TIMEOUT;
            global $CORE_LANG;
            global $MAX_WORDS_LIMIT;
            global $BOOK_EVENT_FULL;
            global $LANG_LLM_XTTS;
            global $HERIKA_ANIMATIONS;
            global $EMOTEMOODS;
            global $CURRENT_CONNECTOR;
            global $CONNECTORS;
            global $CONNECTORS_DIARY;
            global $CONNECTOR;
            global $TTSFUNCTION;
            global $TTS;
            global $STT;
            global $ITT;
            global $FEATURES;
            $path = GetNarratorConfigPath();
            $_GET["profile"] = md5("Narrator");
            require_once($path);

            // Store the loaded profile in cache
            $narratorProfileCache = [
                'HERIKA_NAME' => "The Narrator", // Always use "The Narrator"
                'PROMPT_HEAD' => $GLOBALS['PROMPT_HEAD'],
                'PLAYER_BIOS' => $GLOBALS['PLAYER_BIOS'],
                'HERIKA_PERS' => $GLOBALS['HERIKA_PERS'],
                'HERIKA_DYNAMIC' => $GLOBALS['HERIKA_DYNAMIC'],
                'DYNAMIC_PROFILE' => $GLOBALS['DYNAMIC_PROFILE'],
                'RECHAT_H' => $GLOBALS['RECHAT_H'],
                'RECHAT_P' => $GLOBALS['RECHAT_P'],
                'BORED_EVENT' => $GLOBALS['BORED_EVENT'],
                'CONTEXT_HISTORY' => $GLOBALS['CONTEXT_HISTORY'],
                'HTTP_TIMEOUT' => $GLOBALS['HTTP_TIMEOUT'],
                'CORE_LANG' => $GLOBALS['CORE_LANG'],
                'MAX_WORDS_LIMIT' => $GLOBALS['MAX_WORDS_LIMIT'],
                'BOOK_EVENT_FULL' => $GLOBALS['BOOK_EVENT_FULL'],
                'LANG_LLM_XTTS' => $GLOBALS['LANG_LLM_XTTS'],
                'HERIKA_ANIMATIONS' => $GLOBALS['HERIKA_ANIMATIONS'],
                'EMOTEMOODS' => $GLOBALS['EMOTEMOODS'],
                'CONNECTORS' => $GLOBALS['CONNECTORS'],
                'CONNECTORS_DIARY' => $GLOBALS['CONNECTORS_DIARY'],
                'CONNECTOR' => $GLOBALS['CONNECTOR'],
                'TTSFUNCTION' => $GLOBALS['TTSFUNCTION'],
                'TTS' => $GLOBALS['TTS'],
                'STT' => $GLOBALS['STT'],
                'ITT' => $GLOBALS['ITT'],
                'FEATURES' => $GLOBALS['FEATURES']
            ];
        }

        // Always restore from cache (both first time and subsequent times)
        foreach ($narratorProfileCache as $key => $value) {
            $GLOBALS[$key] = $value;
            //$logValue = is_array($value) ? json_encode($value) : $value;
            //minai_log("debug", "Narrator: Setting $key to $logValue");
        }
        
        // Always set these after restoring cache
        // Make sure the user doesn't change the narrator profile name
        // $GLOBALS["HERIKA_NAME"] = "The Narrator";
        $_GET["profile"] = md5("Narrator");
    }
}

Function GetFallbackConfigPath() {
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5("LLMFallback");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function CreateFallbackConfig() {
    if (!file_exists(GetFallbackConfigPath())) {
        minai_log("info", "Initializing LLM Fallback Profile");
        createProfile("LLMFallback", [
            "HERIKA_NAME" => "LLMFallback",
            "HERIKA_PERS" => "This is a LLM profile used for retrying when the primary LLM call fails. Only the connector settings will be used, and it will only work with openrouterjson."
        ], true);
    }
}

Function SetLLMFallbackProfile() {
    static $fallbackProfileCache = null;

    minai_log("info", "Setting LLM Fallback Profile");
    CreateFallbackConfig();

    // If we haven't loaded the fallback profile yet, load it from file
    if ($fallbackProfileCache === null) {
        // First time load - get the profile from file
        global $CONNECTORS;
        global $CONNECTORS_DIARY;
        global $CONNECTOR;

        // Load the fallback profile
        $path = GetFallbackConfigPath();
        // $_GET["profile"] = md5("LLMFallback");
        require_once($path);

        // Store the loaded profile in cache
        $fallbackProfileCache = [
            'CONNECTORS' => $CONNECTORS,
            'CONNECTORS_DIARY' => $CONNECTORS_DIARY,
            'CONNECTOR' => $CONNECTOR
        ];
    }

    // Always restore from cache (both first time and subsequent times)
    foreach ($fallbackProfileCache as $key => $value) {
        $GLOBALS[$key] = $value;
    }

    // Always set these after restoring cache
    // $_GET["profile"] = md5("LLMFallback");
}

Function GetActorConfigPath($actorName) {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = "/var/www/html/HerikaServer/";
    $newConfFile=md5($actorName);
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}