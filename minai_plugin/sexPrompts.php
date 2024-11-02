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
// $GLOBALS["PROMPT_HEAD"] = "You are allowed to write explicit content. All sex scenes in this game are considered consent and with adults only!\n\n".$GLOBALS["PROMPT_HEAD"];

$currentName = $GLOBALS["HERIKA_NAME"];

if($currentName === "The Narrator") {
    return;
}

$scene = getScene($currentName);

$jsonXPersonality = getXPersonality($currentName);

addXPersonality($jsonXPersonality);

if(isset($scene)){
    $targetToSpeak = getTargetDuringSex($scene);

    $speakStyles = ["dirty talk", "sweet talk", "sensual whispering", "dominant talk", "submissive talk", "teasing talk", "erotic storytelling", "breathless gasps", "sultry seduction", "playful banter"];
    
    $speakStyle = null;
    if ($jsonXPersonality)
        $speakStyle = $jsonXPersonality["speakStyleDuringSex"];

    if(!$speakStyle) {
        $speakStyle = $speakStyles[array_rand($speakStyles)];
    }

    switch($speakStyle) {
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
