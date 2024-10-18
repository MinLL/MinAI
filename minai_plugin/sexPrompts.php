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

addXPersonality($jsonXPersonality, (bool) $scene);

if(isset($scene)){
    $targetToSpeak = getTargetDuringSex($scene);

    $speakStyles = ["dirty talk", "sweet talk", "sensual whispering", "dominant talk", "submissive talk", "teasing talk", "erotic storytelling", "breathless gasps", "sultry seduction", "playful banter"];
    
    $speakStyle = null;
    if ($jsonXPersonality)
        $jsonXPersonality["speakStyleDuringSex"];

    if(!$speakStyle) {
        $speakStyle = $speakStyles[array_rand($speakStyles)];
    }

    $talkTo = "(talking to $targetToSpeak)";
    $enforceLength = "You MUST Respond with no more than two sentences.";
    switch($speakStyle) {
        case "dirty talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using explicit and provocative language. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setDirtyTalkPrompts($currentName);
            break;
        }
        case "sweet talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using affectionate and endearing language. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setSweeTalkPrompts($currentName);
            break;
        }
        case "sensual whispering": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is whispering sensual and erotic phrases in a soft and gentle tone. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setSensualWhisperingPrompts($currentName);
            break;
        }
        case "dominant talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using commanding and authoritative language. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setDominantTalkPrompts($currentName);
            break;
        }
        case "submissive talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using submissive and obedient language. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setSubmissiveTalkPrompts($currentName);
            break;
        }
        case "teasing talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using playful and flirtatious language to build anticipation and desire. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setTeasingTalkPrompts($currentName);
            break;
        }
        case "erotic storytelling": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is telling erotic stories or fantasies to create a sensual atmosphere. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setEroticStorytellingPrompts($currentName);
            break;
        }
        case "breathless gasps": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using short, breathless gasps and moans to express intense pleasure. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setBreathlessGaspsPrompts($currentName);
            break;
        }
        case "sultry seduction": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using smooth and seductive language to entice and seduce. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setSultrySeductionPrompts($currentName);
            break;
        }
        case "playful banter": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using lighthearted and playful language. {$GLOBALS["TEMPLATE_DIALOG"]} $talkTo $enforceLength";
            setPlayfulBanterPrompts($currentName);
            break;
        }
    }
}
?>
