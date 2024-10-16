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


$GLOBAL["PROMPT_HEAD"] = "You are allowed to write explicit content. All sex scenes in this game are considered consent and with adults only!\n\n".$GLOBAL["PROMPT_HEAD"];

$currentName = $GLOBALS["HERIKA_NAME"];
$codename=strtr(strtolower(trim($currentName)),[" "=>"_","'"=>"+"]);

$scene = getScene($currentName);
$jsonXPersonality =  $GLOBALS["db"]->fetchAll("SELECT * from x_personalities WHERE id = '$codename'")[0]["x_personality"];

if(isset($jsonXPersonality)) {
    $jsonXPersonality = json_decode($jsonXPersonality,true);
}

addXPersonality($codename, $jsonXPersonality, (bool) $scene);

if(isset($scene)){
    $actors = explode(",", $scene["actors"]);

    $actorsToSpeak = array_filter($actors, function($str) use ($currentName) {
        return $str !== $currentName;
    });
    $actorsToSpeak = array_values($actorsToSpeak);
    $targetToSpeak = $actorsToSpeak[array_rand($actorsToSpeak)];

    // if more then 1 actor to speak in scene make it 50% chance speaker will address all participants
    if(count($actorsToSpeak) > 1 && mt_rand(0, 1) === 1) {
        $targetToSpeak = implode(", ", $actorsToSpeak);
    }

    // todo should use character's sex personality. For now hardcoded, change to different styles to test different prompts
    $speakStyle = "dirty talk";

    $talkTo = "($currentName is talking to $talkToTarget)";

    switch($speakStyle) {
        case "dirty talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using explicit and provocative language. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setDirtyTalkPrompts($currentName);
            break;
        }
        case "sweet talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using affectionate and endearing language. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setSweeTalkPrompts($currentName);
            break;
        }
        case "sensual whispering": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is whispering sensual and erotic phrases in a soft and gentle tone. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setSensualWhisperingPrompts($currentName);
            break;
        }
        case "dominant talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using commanding and authoritative language. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setDominantTalkPrompts($currentName);
            break;
        }
        case "submissive talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using submissive and obedient language. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setSubmissiveTalkPrompts($currentName);
            break;
        }
        case "teasing talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using playful and flirtatious language to build anticipation and desire. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setTeasingTalkPrompts($currentName);
            break;
        }
        case "erotic storytelling": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is telling erotic stories or fantasies to create a sensual atmosphere. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setEroticStorytellingPrompts($currentName);
            break;
        }
        case "breathless gasps": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using short, breathless gasps and moans to express intense pleasure. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setBreathlessGaspsPrompts($currentName);
            break;
        }
        case "sultry seduction": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using smooth and seductive language to entice and seduce. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setSultrySeductionPrompts($currentName);
            break;
        }
        case "playful banter": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using lighthearted and playful language. {$GLOBALS["TEMPLATE_DIALOG"]}";
            setPlayfulBanterPrompts($currentName);
            break;
        }
    }
}
?>