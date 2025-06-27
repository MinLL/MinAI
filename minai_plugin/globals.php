<?php
// Start metrics for this entry point
require_once("utils/metrics_util.php");
// $globalTimer = new MinAITimerScope('globals_php', 'MinAI');

require_once("config.base.php");
require_once("logger.php");
$pluginPath = "/var/www/html/HerikaServer/ext/minai_plugin";
if (!file_exists("$pluginPath/config.php")) {
    copy("$pluginPath/config.base.php", "$pluginPath/config.php");
}
require_once("config.php");
$GLOBALS["TTS_FALLBACK_FNCT"] = function($responseTextUnmooded, $mood, $responseText) {

    if (!isset($GLOBALS["db"]))
        $GLOBALS["db"] = new sql();
    require_once("config.php");
    require_once("util.php");
    if ($GLOBALS["HERIKA_NAME"] == "Player")
        return;
    if (!isset($GLOBALS["speaker"]))
        $GLOBALS["speaker"] = $GLOBALS["HERIKA_NAME"];
    
    // Special handling for The Narrator to prevent voice inheritance from nearby NPCs
    if ($GLOBALS["speaker"] == "The Narrator" || $GLOBALS["HERIKA_NAME"] == "The Narrator") {
        if (isset($GLOBALS['devious_narrator_eldritch_voice'])) {
            $fallback = $GLOBALS['devious_narrator_eldritch_voice'];
        } else {
            $fallback = "dragon"; // Default narrator voice
        }
        minai_log("info", "Using narrator voice: {$fallback}");
    } else {
        $race = str_replace(" ", "", strtolower(GetActorValue($GLOBALS["speaker"], "Race")));
        $gender = strtolower(GetActorValue($GLOBALS["speaker"], "Gender"));
        if ($gender.$race) {
            $fallback = $GLOBALS["voicetype_fallbacks"][$gender.$race];
        }
        if (!isset($fallback)) {
            minai_log("info", "Warning: Could not find fallback for {$GLOBALS["speaker"]}: {$gender}{$race}. Using last resort fallback: malecommoner");
            $fallback = "malecommoner";
        }
        minai_log("info", "Voice type fallback to {$fallback} for {$GLOBALS["speaker"]}");
    }
    
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $fallback;
    $GLOBALS["TTS"]["MELOTTS"]["voiceid"] = $fallback;
    
    if(isset($GLOBALS["TTS_IN_USE"])) {
        return $GLOBALS["TTS_IN_USE"]($responseTextUnmooded, $mood, $responseText);
    }
    else {
        minai_log("info", "Not retrying, No TTS function enabled");
    }
    return null;
};




$GLOBALS["external_fast_commands"] = [
    // Events that set $MUST_DIE=true in customintegrations.php
    "minai_init",             // Initialization event
    "storecontext",           // Store custom context
    "registeraction",         // Register custom action
    "updatethreadsdb",        // Update threads database
    "storetattoodesc",        // Store tattoo description
    "minai_storeitem",        // Store single item
    "minai_storeitem_batch",  // Store multiple items
    "minai_diary",            // Diary request
    "minai_updateprofile"     // Profile update request
    // "minai_clearinventory"    // Clear inventory
];

$GLOBALS["CHIM_NO_EXAMPLES"] = true;