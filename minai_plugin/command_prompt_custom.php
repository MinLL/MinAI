<?php
require_once("util.php");
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
$target = GetTargetActor();

function SetPromptHead($override) {
    if (str_starts_with($GLOBALS["PROMPT_HEAD"], "#")) {
        // Don't replace it
    }
    else {
        $GLOBALS["PROMPT_HEAD"] = $override;
    }
}


if ($GLOBALS["self_narrator"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"]="Choose the ACTION that best fits current context and character mood to respond to {$target}. Respond in first person. You are {$target} thinking to themself. ";
}
else {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"]="Choose the ACTION that best fits current context and character mood to interact with {$target}. You can also use an ACTION to interact with items, trade, inspect the world, attack or to express your characters needs. Avoid narration and emoting. ";
}


if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"].="You MUST respond with no more than 2-3 sentences and no more than 40 words.";
}

$shouldOverride = ($GLOBALS["PROMPT_HEAD_OVERRIDE"] != "" && isset($GLOBALS["PROMPT_HEAD_OVERRIDE"]));

if (IsRadiant()) { // Is this npc -> npc?
    $GLOBALS["ADD_PLAYER_BIOS"]  = false;
    if ($shouldOverride) // Override prompt head
        SetPromptHead($GLOBALS["PROMPT_HEAD_OVERRIDE"]);
    else {
        // No need to do anything
    }
        
}
else {
    $GLOBALS["ADD_PLAYER_BIOS"]  = true; //must set true because once false for radiant, remains false for a long time 
    if ($shouldOverride)
        SetPromptHead($GLOBALS["PROMPT_HEAD_OVERRIDE"]);
    else
        SetPromptHead($GLOBALS["PROMPT_HEAD"]);
}

?>
