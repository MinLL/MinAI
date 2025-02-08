<?php
require_once("config.php");
require_once("util.php");
$GLOBALS["speaker"] = $GLOBALS["HERIKA_NAME"];
$GLOBALS["minai_processing_input"] = false;

Function SetRadiance($rechat_h, $rechat_p) {
    // error_log("minai: Setting Rechat Parameters (h={$rechat_h}, p={$rechat_p})");
    $GLOBALS["RECHAT_H"] = $rechat_h;
    $GLOBALS["RECHAT_P"] = $rechat_p;
}

Function GetNarratorConfigPath() {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5("Narrator");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function GetFallbackConfigPath() {
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5("LLMFallback");
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

Function CreateFallbackConfig() {
    if (!file_exists(GetFallbackConfigPath())) {
        error_log("minai: Initializing LLM Fallback Profile");
        createProfile("LLMFallback", [
            "HERIKA_NAME" => "LLMFallback",
            "HERIKA_PERS" => "This is a LLM profile used for retrying when the primary LLM call fails. Only the connector settings will be used, and it will only work with openrouterjson."
        ], true);
    }
}

Function SetLLMFallbackProfile() {
    CreateFallbackConfig();
    $path = GetFallbackConfigPath();
    global $CONNECTOR;
    global $CONNECTORS;
    require_once($path);
}
Function SetNarratorProfile() {
    if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && $GLOBALS["use_narrator_profile"]) {
        if (!file_exists(GetNarratorConfigPath())) {
            error_log("minai: Initializing Narrator Profile");
            createProfile("Narrator", [
                "HERIKA_NAME" => "The Narrator",
                "HERIKA_PERS" => "You are The Narrator in a Skyrim adventure. You will only talk to #PLAYER_NAME#. You refer to yourself as 'The Narrator'. Only #PLAYER_NAME# can hear you. Your goal is to comment on #PLAYER_NAME#'s playthrough, and occasionally, give some hints. NO SPOILERS. Talk about quests and last events."
            ], true);
        }
        $path = GetNarratorConfigPath();
        // error_log("minai: Overwriting profile with narrator profile ($path).");
        // Ignore narrator name
        // global $HERIKA_NAME;
        global $PROMPT_HEAD;
        global $HERIKA_PERS;
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
        global $CONNECTORS;
        global $CONNECTORS_DIARY;
        global $CONNECTOR;
        global $TTSFUNCTION;
        global $TTS;
        global $STT;
        global $ITT;
        global $FEATURES;
        require_once($path);
    }
}

if (IsRadiant()) {
    SetRadiance(0, 0); // Disable rechat during radiant conversations, as this is handled by MinAI's controller in-game
}

SetNarratorProfile();

// If talking to the narrator, force it to respond.
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator") && (in_array($GLOBALS["gameRequest"][0],["inputtext","inputtext_s","ginputtext","ginputtext_s"])) ) {
    error_log("minai: Forcing herika_name to the narrator: Is talking to narrator");
    SetEnabled($GLOBALS["PLAYER_NAME"], "isTalkingToNarrator", false);
    $GLOBALS["HERIKA_NAME"] = "The Narrator";
    $GLOBALS["minai_processing_input"] = true;
    SetNarratorProfile();
}

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    error_log("minai: Forcing herika_name to the narrator: Is singing");
    // $GLOBALS["HERIKA_NAME"] = "The Narrator";
    // SetNarratorProfile();
}

require_once("deviousnarrator.php");
if (ShouldUseDeviousNarrator()) {
    SetDeviousNarrator();
}


Function GetConfigPath($npcName) {
    // If use symlink, php code is actually in repo folder but included in wsl php server
    // with just dirname((__FILE__)) it was getting directory of repo not php server 
    $path = getcwd().DIRECTORY_SEPARATOR;
    $newConfFile=md5($npcName);
    return $path . "conf".DIRECTORY_SEPARATOR."conf_$newConfFile.php";
}

if (isset($GLOBALS["realnames_support"]) && $GLOBALS["realnames_support"]) {
    $matches = [];
    if (preg_match('/^(.+?) \[(.+)\]$/', $GLOBALS["HERIKA_NAME"], $matches)) {
        $fullPath = GetConfigPath($matches[0]);
        $badPers = "Roleplay as {$matches[0]}";
        if (!file_exists($fullPath) || $GLOBALS["HERIKA_PERS"] == $badPers) {
            $npcName = $matches[2];
            $codename=addslashes(strtr(strtolower(trim($npcName)),[" "=>"_","'"=>"+"]));
            error_log("minai: Detected generic NPC, seeding profile. Original: {$matches[0]}, new: {$matches[2]}, codename: $codename");
            $npcTemlate=$GLOBALS["db"]->fetchAll("SELECT npc_pers FROM npc_templates where npc_name='$codename'");
            $personality = 'Roleplay as '.addslashes(trim($matches[1])) . ", who is a " . addslashes(trim($matches[2]));;
            if (is_array($npcTemlate[0]))
                $personality = addslashes(trim($npcTemlate[0]["npc_pers"]));
            else {
                $npcTemlate=$GLOBALS["db"]->fetchAll("SELECT npc_pers FROM npc_templates_custom where npc_name='$codename'");
                if (is_array($npcTemlate[0]))
                    $personality = addslashes(trim($npcTemlate[0]["npc_pers"]));
            }
            // Swap out the generic name for the new name
            $personality = str_replace("Roleplay as {$matches[2]}", "Roleplay as {$matches[0]}", $personality);
            error_log("minai: Initializing generic NPC {$matches[0]} with personality: $personality");
            createProfile($matches[0],
                          ["HERIKA_PERS" => $personality],
                          true
            );
            global $HERIKA_PERS;
            include($fullPath);
                
        }
    }
}

$GLOBALS["LLM_RETRY_FNCT"] = function() {
    if (isset($GLOBALS['use_llm_fallback']) && !$GLOBALS['use_llm_fallback']) {
        error_log("MinAI: LLM fallback is disabled - skipping retry");
        return false;
    }
    
    error_log("MinAI: Retrying LLM...");
    SetLLMFallbackProfile();
    $outputWasValid = call_llm();   
    if (!$outputWasValid) {
        error_log("Warning: LLM returned invalid output after retry.");
    }
    return $outputWasValid;
};

// Only create the fallback config if the feature is enabled
if (isset($GLOBALS['use_llm_fallback']) && $GLOBALS['use_llm_fallback']) {
    CreateFallbackConfig();
}
?>
