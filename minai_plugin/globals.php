<?php

$GLOBALS["TTS_FALLBACK_FNCT"] = function($responseTextUnmooded, $mood, $responseText) {

    if (!isset($GLOBALS["db"]))
        $GLOBALS["db"] = new sql();
    require_once("config.php");
    require_once("util.php");
    $race = str_replace(" ", "", strtolower(GetActorValue($GLOBALS["HERIKA_NAME"], "race")));
    $gender = strtolower(GetActorValue($GLOBALS["HERIKA_NAME"], "gender"));
    $fallback = $GLOBALS["voicetype_fallbacks"][$gender.$race];
    if (!$fallback) {
        error_log("minai: Warning: Could not find fallback for {$gender}{$race}");
        return;
    }
    error_log("minai: Voice type fallback to {$fallback} for {$GLOBALS["HERIKA_NAME"]}");
    $GLOBALS["TTS"]["FORCED_VOICE_DEV"] = $fallback;
    $GLOBALS["TTS"]["MELOTTS"]["voiceid"] = $fallback;
    if(function_exists("tts")) {
        return tts($responseTextUnmooded, $mood, $responseText);
    }
    return null;
};
