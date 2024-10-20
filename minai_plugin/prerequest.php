<?php
require_once("config.php");
require_once("util.php");

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
        global $HERIKA_NAME;
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

if (IsNewRadiantConversation()) {
    error_log("minai: Initial radiant conversation, overriding rechat parameters");
    $GLOBALS["db"]->delete("conf_opts", "id='_minai_RADIANT//initial'");
    SetRadiance(2, 0); // Always rechat at least once
}
elseif (IsRadiant()) {
    SetRadiance($GLOBALS["radiance_rechat_h"], $GLOBALS["radiance_rechat_p"]);
}

SetNarratorProfile();

require_once("deviousnarrator.php");
if (ShouldUseDeviousNarrator()) {
    SetDeviousNarrator();
}

?>
