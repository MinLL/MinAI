<?php

require_once("util.php");

$currentName = $GLOBALS["HERIKA_NAME"];

if($currentName === "The Narrator") {
    return;
}

$scene = getScene($currentName);
$jsonXPersonality = getXPersonality($currentName);

if(isset($scene)){
    $targetToSpeak = getTargetDuringSex($scene);
    
    $speakStyleInfo = determineSpeakStyle($currentName, $scene, $jsonXPersonality);
    $speakStyle = $speakStyleInfo["style"];
    
    minai_log("info", "Using speakStyle: {$speakStyle}");

    $talkTo = "(talking to $targetToSpeak)";
    $enforceLength = "You MUST Respond with no more than two sentences.";
    
    $pronouns = $GLOBALS["herika_pronouns"];

    switch($speakStyle) {
        case "victim talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is being forced into non-consensual acts and expressing distress and resistance, {$pronouns["possessive"]} voice trembling with fear. {$talkTo} {$enforceLength}";
            break;
        }
        case "aggressor talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using threatening and aggressive language, {$pronouns["possessive"]} tone menacing and controlling. {$talkTo} {$enforceLength}";
            break;
        }
        case "dirty talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using explicit and provocative language, {$pronouns["possessive"]} words dripping with vulgarity. {$talkTo} {$enforceLength}";
            break;
        }
        case "sweet talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using affectionate and endearing language, {$pronouns["possessive"]} voice warm and comforting. {$talkTo} {$enforceLength}";
            break;
        }
        case "sensual whispering": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is whispering sensual and erotic phrases in a soft, breathy tone, {$pronouns["possessive"]} words like a caress. {$talkTo} {$enforceLength}";
            break;
        }
        case "dominant talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using commanding and authoritative language, {$pronouns["possessive"]} voice firm and unyielding. {$talkTo} {$enforceLength}";
            break;
        }
        case "submissive talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using submissive and obedient language, {$pronouns["possessive"]} tone soft and yielding. {$talkTo} {$enforceLength}";
            break;
        }
        case "teasing talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using playful and flirtatious language to build anticipation and desire, {$pronouns["possessive"]} voice light and teasing. {$talkTo} {$enforceLength}";
            break;
        }
        case "erotic storytelling": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is telling erotic stories or fantasies to create a sensual atmosphere, {$pronouns["possessive"]} voice seductive and captivating. {$talkTo} {$enforceLength}";
            break;
        }
        case "breathless gasps": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using short, breathless gasps and moans to express intense pleasure, {$pronouns["possessive"]} breaths ragged and urgent. {$talkTo} {$enforceLength}";
            break;
        }
        case "sultry seduction": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using smooth and seductive language to entice and seduce, {$pronouns["possessive"]} voice low and inviting. {$talkTo} {$enforceLength}";
            break;
        }
        case "playful banter": {
            $GLOBALS["TEMPLATE_DIALOG"] = "{$GLOBALS["HERIKA_NAME"]} is using lighthearted and playful language, {$pronouns["possessive"]} tone jovial and engaging. {$talkTo} {$enforceLength}";
            break;
        }
    }
    $GLOBALS["TEMPLATE_DIALOG"] .= " Emphasize the content of the #SEX_SCENARIO in the dialogue while reacting to the latest dialogue and events, including any sexual acts, positions, or restraints. ";
}

