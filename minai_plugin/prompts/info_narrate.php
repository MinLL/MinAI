<?php
$cleanedMessage = GetCleanedMessage();

// Register prompts only if specifically requested
if ($GLOBALS["gameRequest"][0] == "chatnf_minai_narrate") {
    
    //The Narrator: Betrid Silver-Blood tried to take Restore Health Potion Recipe from Aeter, but Aeter doesn't have any. (Talking to Betrid Silver-Blood)
    $b_not_have = (stripos($cleanedMessage, " tried to take ") !== false ) && (stripos($cleanedMessage, "doesn't have any") !== false );
    if ($b_not_have) {
        $cleanedMessage = str_ireplace(["tried to take",", but","doesn't have any"],["took"," and", "told them it was the last one"], $cleanedMessage);
    } else {
        //The Narrator: Lydia tried to take an item from Aeter, but couldn't find it. (Talking to Lydia)
        $b_not_found = (stripos($cleanedMessage, " tried to take an item from ") !== false ) && (stripos($cleanedMessage, "but couldn't find it") !== false );
        if ($b_not_found) {
            $cleanedMessage = str_ireplace([" tried to take ",", but couldn't find it"],[" took ", ""], $cleanedMessage);
        }
    }

    SetNarratorProfile();
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["chatnf_minai_narrate"] = [
        "cue"=>[],
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