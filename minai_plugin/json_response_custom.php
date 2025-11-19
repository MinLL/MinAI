<?php

// Avoid processing for fast / storage events
//if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
//    return;
//}

require_once("config.php");

// add emotions
if ($GLOBALS['use_emotions_expression']) {
    $GLOBALS["responseTemplate"] = array_merge($GLOBALS["responseTemplate"], [
        "emotion" => "calm|arousal|desire|love|joy|enthusiasm|gratitude|pride|fear|apprehension|panic|anxiety|grief|envy|jealousy|disappointment|shame|regret|embarrassment|anger|rage|resentment|disgust|aversion",
        "emotion intensity" => "low|moderate|strong"
    ]);

    $crt_moods = trim($GLOBALS["responseTemplate"]["mood"] ?? ""); 
    if ($crt_moods == "")
        $crt_moods = "default|neutral|calm|assisting|assertive|playful|delighted|sexy|amused|kindly|lovely|seductive|smug|sassy|sarcastic|sardonic|smirking|irritated|teasing|mocking|bored|curious|confident|courageous|content|angry|belligerent|anxious|fearful|sad|gloomy|drunk|high|sober";
    else {
        if (strpos($crt_moods, "default") === false) 
           $crt_moods .= "|default";
        if (strpos($crt_moods, "neutral") === false) 
           $crt_moods .= "|neutral";
        if (strpos($crt_moods, "calm") === false) 
           $crt_moods .= "|calm";
        if (strpos($crt_moods, "assisting") === false) 
           $crt_moods .= "|assisting";
        if (strpos($crt_moods, "assertive") === false) 
           $crt_moods .= "|assertive";
        if (strpos($crt_moods, "playful") === false) 
           $crt_moods .= "|playful";
        if (strpos($crt_moods, "delighted") === false) 
           $crt_moods .= "|delighted";
        if (strpos($crt_moods, "sexy") === false) 
           $crt_moods .= "|sexy";
        if (strpos($crt_moods, "amused") === false) 
           $crt_moods .= "|amused";
        if (strpos($crt_moods, "kindly") === false) 
           $crt_moods .= "|kindly";
        if (strpos($crt_moods, "lovely") === false) 
           $crt_moods .= "|lovely";
        if (strpos($crt_moods, "seductive") === false) 
           $crt_moods .= "|seductive";
        if (strpos($crt_moods, "smug") === false) 
           $crt_moods .= "|smug";
        if (strpos($crt_moods, "sassy") === false) 
           $crt_moods .= "|sassy";
        if (strpos($crt_moods, "sarcastic") === false) 
           $crt_moods .= "|sarcastic";
        if (strpos($crt_moods, "sardonic") === false) 
           $crt_moods .= "|sardonic";
        if (strpos($crt_moods, "smirking") === false) 
           $crt_moods .= "|smirking";
        if (strpos($crt_moods, "irritated") === false) 
           $crt_moods .= "|irritated";
        if (strpos($crt_moods, "teasing") === false) 
           $crt_moods .= "|teasing";
        if (strpos($crt_moods, "mocking") === false) 
           $crt_moods .= "|mocking";
        if (strpos($crt_moods, "bored") === false) 
           $crt_moods .= "|bored";
        if (strpos($crt_moods, "curious") === false) 
           $crt_moods .= "|curious";
        if (strpos($crt_moods, "confident") === false) 
           $crt_moods .= "|confident";
        if (strpos($crt_moods, "courageous") === false) 
           $crt_moods .= "|courageous";
        if (strpos($crt_moods, "content") === false) 
           $crt_moods .= "|content";
        if (strpos($crt_moods, "angry") === false) 
           $crt_moods .= "|angry";
        if (strpos($crt_moods, "belligerent") === false) 
           $crt_moods .= "|belligerent";
        if (strpos($crt_moods, "anxious") === false) 
           $crt_moods .= "|anxious";
        if (strpos($crt_moods, "fearful") === false) 
           $crt_moods .= "|fearful";
        if (strpos($crt_moods, "sad") === false) 
           $crt_moods .= "|sad";
        if (strpos($crt_moods, "gloomy") === false) 
           $crt_moods .= "|gloomy";
        if (strpos($crt_moods, "drunk") === false) 
           $crt_moods .= "|drunk";
        if (strpos($crt_moods, "high") === false) 
           $crt_moods .= "|high";
        if (strpos($crt_moods, "sober") === false) 
           $crt_moods .= "|sober";
    }
   
    $GLOBALS["responseTemplate"]["mood"] = $crt_moods;
}

$GLOBALS["responseTemplate"]["message"] = "lines of dialogue, plain text formatting";
$GLOBALS["responseTemplate"]["target"] = "the name of the character or entity who is the target of the action|the name of the location that is the destination of the action";
$GLOBALS["responseTemplate"] = array_merge($GLOBALS["responseTemplate"], [
    "probability" => "number in 0.0 - 1.0 interval"
]);


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

