<?php
require_once("config.php");

$GLOBALS["TTS_FALLBACK_FNCT"] = function($responseTextUnmooded, $mood, $responseText) {

    if (!isset($GLOBALS["db"]))
        $GLOBALS["db"] = new Sql();
    require_once("config.php");
    require_once("util.php");
    $race = str_replace(" ", "", strtolower(GetActorValue($GLOBALS["speaker"], "Race")));
    $gender = strtolower(GetActorValue($GLOBALS["speaker"], "Gender"));
    $fallback = $GLOBALS["voicetype_fallbacks"][$gender.$race];
    if (!$fallback) {
        error_log("minai: Warning: Could not find fallback for {$GLOBALS["speaker"]}: {$gender}{$race}");
        return;
    }
    error_log("minai: Voice type fallback to {$fallback} for {$GLOBALS["speaker"]}");
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $fallback;
    $GLOBALS["TTS"]["MELOTTS"]["voiceid"] = $fallback;
    if(function_exists("tts")) {
        return tts($responseTextUnmooded, $mood, $responseText);
    }
    return null;
};

