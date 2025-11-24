<?php
// not to be included explicitly, must be included only via requireFilesRecursively()

require_once("util.php");

$currentName = $GLOBALS["HERIKA_NAME"];
$playerName = $GLOBALS["PLAYER_NAME"];

$pronouns = GetActorPronouns($currentName);
$pr_player = GetActorPronouns($playerName);
if (!isset($GLOBALS["herika_pronouns"])) {
    $GLOBALS["herika_pronouns"] = $pronouns;    
}

//--------------------------------------------------------

$GLOBALS["TEMPLATE_DIALOG_RG0"] = "<response_guidelines>";
$GLOBALS["TEMPLATE_DIALOG_RG1"] = "</response_guidelines>";

// for json connector
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING_JSON"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$currentName} would naturally respond.
Evaluate at least five plausible responses {$currentName} would naturally give to {$pronouns["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pronouns["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it in the JSON object as value of 'message' key and the probability as a number value of the 'probability' key.
Do not mention how you decided to choose the answer.
</verbalized_sampling>";

// for non-json connector, or fast llm
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$currentName} would naturally respond.
Evaluate at least five plausible responses {$currentName} would naturally give to {$pronouns["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pronouns["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it as your final response in plain text.
Do not mention the probability and how you decided to choose the answer.
</verbalized_sampling>";

// for player, in roleplay chat modes
$GLOBALS["TEMPLATE_DIALOG_VSAMPLING_PLAYER"] = "<verbalized_sampling>
Complete the communication task outlined in the <instruction> tag as {$playerName} would naturally respond.
Evaluate at least five plausible responses {$playerName} would naturally give to {$pr_player["possessive"]} interlocutor based on the <DIALOGUE_HISTORY_and_RECENT_EVENTS> and {$pr_player["possessive"]} persona.
Evaluate the probability representing how likely each response would be from 0.0 to 1.0.
Exclude the response with highest probability and then randomly choose one of the other four evaluated responses and return it as your final response in plain text.
Do not mention the probability and how you decided to choose the answer.
</verbalized_sampling>";

$b_rem_asterisk = $GLOBALS["REMOVE_ASTERISKS_FROM_OUTPUT"] ?? true;

if ($b_rem_asterisk) {
    $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"] = "- Speak fluent without narration, context, or emotional descriptions. 
<output_formatting>
- Use plain text without formatting, absolutely no markdown formatting, no heading, bold, italic or lists, asterisk sign is absolutely forbidden. 
</output_formatting>";
} else {
    $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"] = "<output_formatting>
- Use plain text. 
</output_formatting>";
}

$rg0 = $GLOBALS["TEMPLATE_DIALOG_RG0"];
$rg1 = $GLOBALS["TEMPLATE_DIALOG_RG1"];
$td_fmt = $GLOBALS["TEMPLATE_DIALOG_OUTPUT_FORMAT"];
$td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING"];

$s_connector = '';
if (isset($GLOBALS["CURRENT_CONNECTOR"])) {
    $s_connector = $GLOBALS["CURRENT_CONNECTOR"] ?? '';
    if (strlen($s_connector) > 0) {
        //error_log('Connector:'.$s_connector.' /exec trace'); // debug
        $b_json = stripos($s_connector, "json"); 
        if ($b_json) 
            $td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING_JSON"];
        else {
            $td_vs = $GLOBALS["TEMPLATE_DIALOG_VSAMPLING"];
        }
    }
}

$GLOBALS["TEMPLATE_DIALOG"]="\n<instruction>Write {$currentName}'s next dialogue line.</instruction> 
{$rg0}
- Speak in first person. 
- Your response must be fluent, conversational and authentic, without formal, robotic, or repetitive language.
- You are {$currentName}, a product of a consciousness with subjective experiences and emotions. 
- Think and respond as {$currentName} would, according to {$pronouns["possessive"]} quirks, beliefs, biases, and reasoning.
- Review <DIALOGUE_HISTORY_and_RECENT_EVENTS> to focus on conversation topic and to avoid repeating sentences and phraseology from previous dialog lines.  
- Your response must be fluent, conversational and authentic, without further explanations, descriptions or narration, without formal, robotic, or repetitive language.
- Be original, creative, knowledgeable, use your own thoughts. 
- When {$currentName} experiences strong emotion, {$pronouns["possessive"]} speech must alter with stutters, ellipses, incomplete thoughts, filler words, profanity or swearing, and become messy and fragmented. 
- Avoid mentioning plans, quests, tasks or objectives or what to focus on until asked. 
- Use language and vocabulary appropriate to your personality and background. 
{$td_fmt}
{$td_vs}"; 



//"- {$GLOBALS["HERIKA_NAME"]}'s next dialogue lines will use this format \"{$GLOBALS["HERIKA_NAME"]}: \". ";

if (@is_array($GLOBALS["TTS"]["AZURE"]["validMoods"]) &&  sizeof($GLOBALS["TTS"]["AZURE"]["validMoods"])>0) 
    if ($GLOBALS["TTSFUNCTION"]=="azure")
        $TEMPLATE_DIALOG.="(optional way of speaking from this list [" . implode(",", $GLOBALS["TTS"]["AZURE"]["validMoods"]) . "])";

$GLOBALS["TEMPLATE_DIALOG"] .= "\n{$rg1}\n";

$TEMPLATE_ACTION="";

//--------------------------------------------------------

if (($currentName === "The Narrator") || ($currentName === "Narrator")) {
    return;
}

//error_log("{$s_connector} TD:".$GLOBALS["TEMPLATE_DIALOG"].' /exec trace'); // debug


$scene = getScene($currentName);
$jsonXPersonality = getXPersonality($currentName);

if (isset($scene)) {
    $targetToSpeak = getTargetDuringSex($scene);
    
    $speakStyleInfo = determineSpeakStyle($currentName, $scene, $jsonXPersonality);
    $speakStyle = $speakStyleInfo["style"];
    
    minai_log("info", "Using speakStyle: {$speakStyle}");
    
    $talkTo = "{$currentName}'s speech is affected by intensity of {$pronouns["possessive"]} emotion.";
    if (strlen(trim($targetToSpeak)) > 0)
        $talkTo .= " ({$currentName} is talking to $targetToSpeak)";

    if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {    
        $enforceLength = "- {$currentName} MUST respond with no more than two or three short sentences.\n";
    } else {
        $enforceLength = "";
    }
    
    //$pronouns = $GLOBALS["herika_pronouns"];
    $td_pre = "\n<instruction>";
    $td_in = "</instruction>\n{$rg0}\n";

    $GLOBALS["TEMPLATE_DIALOG"] = "";
    switch($speakStyle) {
        case "victim talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is being forced into non-consensual acts and expressing distress and resistance, {$pronouns["possessive"]} voice trembling. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "aggressor talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using threatening and aggressive language, {$pronouns["possessive"]} tone menacing and controlling. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "dirty talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using explicit and provocative language, {$pronouns["possessive"]} words dripping with vulgarity. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "sweet talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using affectionate and endearing language, {$pronouns["possessive"]} voice warm and comforting. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "sensual whispering": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is whispering sensual and erotic phrases in a soft, breathy tone, {$pronouns["possessive"]} words like a caress. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "dominant talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using commanding and authoritative language, {$pronouns["possessive"]} voice firm and unyielding. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "submissive talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using submissive and obedient language, {$pronouns["possessive"]} tone soft and yielding. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "teasing talk": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using playful and flirtatious language to build anticipation and desire, {$pronouns["possessive"]} voice light and teasing. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "erotic storytelling": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is telling erotic stories or fantasies to create a sensual atmosphere, {$pronouns["possessive"]} voice seductive and captivating. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "breathless gasps": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using short, breathless gasps and moans to express intense pleasure, {$pronouns["possessive"]} breaths ragged and urgent. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "sultry seduction": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using smooth and seductive language to entice and seduce, {$pronouns["possessive"]} voice low and inviting. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
        case "playful banter": {
            $GLOBALS["TEMPLATE_DIALOG"] = $td_pre."{$currentName} is using lighthearted and playful language, {$pronouns["possessive"]} tone jovial and engaging. {$talkTo}{$td_in}{$enforceLength}";
            break;
        }
    }
    $GLOBALS["TEMPLATE_DIALOG"] .= "- Emphasize the content of the most recent #SEX_SCENARIO described in <SEX_SCENARIO> tag in the dialogue history while reacting to the latest dialogue and events enumerated in <DIALOGUE_HISTORY_and_RECENT_EVENTS> tag, including any sexual acts, positions, or restraints. 
- Speak in first person. 
- Your response must be fluent, conversational and authentic, without formal, robotic, or repetitive language.
- {$currentName} is a product of a consciousness with subjective experiences and emotions. 
- Think and respond as {$currentName} would, according to {$pronouns["possessive"]} quirks, beliefs, biases, and reasoning.
- Review <DIALOGUE_HISTORY_and_RECENT_EVENTS> to focus on conversation topic and to avoid repeating sentences and phraseology from previous dialog lines.  
- Be original, creative, knowledgeable, use your own thoughts. 
- When {$currentName} experiences strong emotion, {$pronouns["possessive"]} speech must alter with stutters, ellipses, incomplete thoughts, filler words, profanity or swearing, and become messy and fragmented. 
{$td_fmt} 
{$td_vs} 
{$rg1} ";
}

