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

$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"]="Choose a coherent ACTION that is available to you in order to obey or physically interact with {$target}. You can also use an ACTION to interact with the world, provide services, or indicate your arousal. ";
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
    if ($shouldOverride)
        SetPromptHead($GLOBALS["PROMPT_HEAD_OVERRIDE"]);
    else
        SetPromptHead($GLOBALS["PROMPT_HEAD"]);
}

?>
