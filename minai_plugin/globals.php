<?php
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
    $race = str_replace(" ", "", strtolower(GetActorValue($GLOBALS["speaker"], "Race")));
    $gender = strtolower(GetActorValue($GLOBALS["speaker"], "Gender"));
    $fallback = $GLOBALS["voicetype_fallbacks"][$gender.$race];
    if (!$fallback) {
        minai_log("info", "Warning: Could not find fallback for {$GLOBALS["speaker"]}: {$gender}{$race}. Using last resort fallback: malecommoner");
        $fallback = "malecommoner";
    }
    minai_log("info", "Voice type fallback to {$fallback} for {$GLOBALS["speaker"]}");
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

