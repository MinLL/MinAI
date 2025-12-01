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

$HerikaName = $GLOBALS["HERIKA_NAME"];
$currentName = strtolower($HerikaName);

if (($currentName === "the narrator") || ($currentName === "narrator"))  {
    return;
}

$scene = getScene($currentName);
// Add debug logging for scene data
// minai_log("info", "Scene data: " . json_encode($scene));

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
setDirtyTalkPrompts($HerikaName); //default prompts when scene or speak style is not found 

if(isset($scene)){
    $targetToSpeak = getTargetDuringSex($scene) ?? "";
    $gender = GetGender($HerikaName);
       
    $speakStyleInfo = determineSpeakStyle($HerikaName, $scene, $jsonXPersonality);
    $speakStyle = strtolower(trim($speakStyleInfo["style"]));

    minai_log("info", "Setting sex speak style: $speakStyle. Role: {$speakStyleInfo["role"]}");
    //error_log(" $HerikaName use $speakStyle with $targetToSpeak - exec trace "); // debug 
    
    switch($speakStyle) {
        case "victim talk": {
            setVictimTalkPrompts($HerikaName);
            break;
        }
        case "aggressor talk": {
            setAggressorTalkPrompts($HerikaName);
            break;
        }
        case "dirty talk": {
            setDirtyTalkPrompts($HerikaName);
            break;
        }
        case "sweet talk": {
            setSweeTalkPrompts($HerikaName);
            break;
        }
        case "sensual whispering": {
            setSensualWhisperingPrompts($HerikaName);
            break;
        }
        case "dominant talk": {
            setDominantTalkPrompts($HerikaName);
            break;
        }
        case "submissive talk": {
            setSubmissiveTalkPrompts($HerikaName);
            break;
        }
        case "teasing talk": {
            setTeasingTalkPrompts($HerikaName);
            break;
        }
        case "erotic storytelling": {
            setEroticStorytellingPrompts($HerikaName);
            break;
        }
        case "breathless gasps": {
            setBreathlessGaspsPrompts($HerikaName);
            break;
        }
        case "sultry seduction": {
            setSultrySeductionPrompts($HerikaName);
            break;
        }
        case "playful banter": {
            setPlayfulBanterPrompts($HerikaName);
            break;
        }
        default: {
            $i_rnd = rand(1, 4);
            if ($gender == 'female') {
                if ($i_rnd == 1) 
                    setSweeTalkPrompts($HerikaName);
                elseif ($i_rnd == 2)
                    setPlayfulBanterPrompts($HerikaName);
                elseif ($i_rnd == 3)
                    setSubmissiveTalkPrompts($HerikaName);
                else
                    setDirtyTalkPrompts($HerikaName);
            } elseif ($gender == 'male') { 
                if ($i_rnd == 1) 
                    setDominantTalkPrompts($HerikaName);
                elseif ($i_rnd == 2)
                    setBreathlessGaspsPrompts($HerikaName);
                else
                    setDirtyTalkPrompts($HerikaName);
            }
        }
    }
//} else {
//    minai_log("warn", "Setting sex speak style failed attempt: scene not found.");
}
