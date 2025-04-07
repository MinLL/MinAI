<?php
// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

require_once("config.php");

/*
if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $moods=explode(",",$GLOBALS["EMOTEMOODS"]);
    shuffle($moods);
    $pronouns = $GLOBALS["player_pronouns"];
    $GLOBALS["responseTemplate"] = [
        "character"=>$GLOBALS["PLAYER_NAME"],
        "listener"=>"{$GLOBALS['PLAYER_NAME']} is singing to those around {$pronouns['object']}",
        "message"=>"lines of dialogue",
        "mood"=>implode("|",$moods),
        "action"=>implode("|",$GLOBALS["FUNC_LIST"]),
        "target"=>"action's target|destination name",
        "lang"=>"en|es",
        "response_tone_happiness"=>"Value from 0-1",
        "response_tone_sadness"=>"Value from 0-1",
        "response_tone_disgust"=>"Value from 0-1",
        "response_tone_fear"=>"Value from 0-1",
        "response_tone_surprise"=>"Value from 0-1",
        "response_tone_anger"=>"Value from 0-1",
        "response_tone_other"=>"Value from 0-1",
        "response_tone_neutral"=>"Value from 0-1"
    ];
}
else*/
if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $pronouns = $GLOBALS["player_pronouns"];
    $GLOBALS["responseTemplate"]["character"] = IsExplicitScene() ? $GLOBALS["PLAYER_NAME"] . "'s body" : $GLOBALS["PLAYER_NAME"] . "'s subconscious";
    $GLOBALS["responseTemplate"]["listener"] = IsExplicitScene()
        ? "{$GLOBALS['PLAYER_NAME']} is reacting to physical sensations"
        : "{$GLOBALS['PLAYER_NAME']} is thinking to {$pronouns['object']}self";
    
    // Only include response tones if TTSFUNCTION is zonos_gradio
    if (zonosIsActive()) {
        $GLOBALS["responseTemplate"]["response_tone_happiness"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_sadness"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_disgust"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_fear"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_surprise"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_anger"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_other"] = "Value from 0-1";
        $GLOBALS["responseTemplate"]["response_tone_neutral"] = "Value from 0-1";
    }
}
