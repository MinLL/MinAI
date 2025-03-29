<?php
$cleanedMessage = GetCleanedMessage();

// Register prompts only if specifically requested
if ($GLOBALS["gameRequest"][0] == "chatnf_minai_narrate") {
    SetNarratorProfile();
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["chatnf_minai_narrate"] = [
        "cue"=>[
        ],
        "player_request"=>[$narratePrompt]
    ];
    OverrideGameRequestPrompt($narratePrompt);
}

if ($GLOBALS["gameRequest"][0] == "minai_narrate") {
    SetNarratorProfile();
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["minai_narrate"] = [
        "cue"=>[],
        "player_request"=>[$narratePrompt]
    ];
    OverrideGameRequestPrompt($narratePrompt);
} 

if ($GLOBALS["gameRequest"][0] == "info_minai_narrate") {
    SetNarratorProfile();
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["info_minai_narrate"] = [
        "cue"=>[],
        "player_request"=>[$narratePrompt]
    ];
    OverrideGameRequestPrompt($narratePrompt);
} 