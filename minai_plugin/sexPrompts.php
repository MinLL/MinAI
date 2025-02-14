<?php

require_once("util.php");
require_once("speakStylesPrompts/dirtyTalk.php");
require_once("speakStylesPrompts/sweetTalk.php");
require_once("speakStylesPrompts/sensualWhispering.php");
require_once("speakStylesPrompts/playfulBanter.php");
require_once("speakStylesPrompts/sultrySeduction.php");
require_once("speakStylesPrompts/breathlessGasps.php");
require_once("speakStylesPrompts/eroticStorytelling.php");
require_once("speakStylesPrompts/teasingTalk.php");
require_once("speakStylesPrompts/dominantTalk.php");
require_once("speakStylesPrompts/submissiveTalk.php");
require_once("speakStylesPrompts/victimTalk.php");
require_once("speakStylesPrompts/aggressorTalk.php");

$currentName = $GLOBALS["HERIKA_NAME"];

if($currentName === "The Narrator") {
    return;
}


$currentName = strtolower($currentName);
$scene = getScene($currentName);
// Add debug logging for scene data
error_log("minai: Scene data: " . json_encode($scene));

// Initialize sex scene context with scene data
$GLOBALS["SEX_SCENE_CONTEXT"] = [
    "victimActors" => isset($scene["victim_actors"]) ? $scene["victim_actors"] : null,
    "femaleActors" => isset($scene["female_actors"]) ? $scene["female_actors"] : null,
    "maleActors" => isset($scene["male_actors"]) ? $scene["male_actors"] : null,
    "framework" => isset($scene["framework"]) ? $scene["framework"] : "",
    "threadId" => isset($scene["thread_id"]) ? $scene["thread_id"] : null,
    "scene" => isset($scene["curr_scene_id"]) ? $scene["curr_scene_id"] : "",
    "description" => isset($scene["description"]) ? $scene["description"] : "",
    "fallback" => isset($scene["fallback"]) ? $scene["fallback"] : ""
];

$jsonXPersonality = getXPersonality($currentName);
addXPersonality($jsonXPersonality);

if(isset($scene)){
    $targetToSpeak = getTargetDuringSex($scene);
    
    $speakStyleInfo = determineSpeakStyle($currentName, $scene, $jsonXPersonality);
    $speakStyle = $speakStyleInfo["style"];
    
    error_log("minai: Setting sex speak style: $speakStyle. Role: {$speakStyleInfo["role"]}");
    
    switch($speakStyle) {
        case "victim talk": {
            setVictimTalkPrompts($currentName);
            break;
        }
        case "aggressor talk": {
            setAggressorTalkPrompts($currentName);
            break;
        }
        case "dirty talk": {
            setDirtyTalkPrompts($currentName);
            break;
        }
        case "sweet talk": {
            setSweeTalkPrompts($currentName);
            break;
        }
        case "sensual whispering": {
            setSensualWhisperingPrompts($currentName);
            break;
        }
        case "dominant talk": {
            setDominantTalkPrompts($currentName);
            break;
        }
        case "submissive talk": {
            setSubmissiveTalkPrompts($currentName);
            break;
        }
        case "teasing talk": {
            setTeasingTalkPrompts($currentName);
            break;
        }
        case "erotic storytelling": {
            setEroticStorytellingPrompts($currentName);
            break;
        }
        case "breathless gasps": {
            setBreathlessGaspsPrompts($currentName);
            break;
        }
        case "sultry seduction": {
            setSultrySeductionPrompts($currentName);
            break;
        }
        case "playful banter": {
            setPlayfulBanterPrompts($currentName);
            break;
        }
    }
}
?>
