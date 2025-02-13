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

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "Respond with a song from {$GLOBALS["PLAYER_NAME"]}. Be creative, and match the mood of the scene.";
}
elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "Respond with {$target}'s immediate thoughts, emotions, and internal reactions to the physical and emotional sensations they are experiencing right now. Focus particularly on any erotic elements happening within the scene. Respond in first person as {$target}, staying fully in the present moment and focusing on their personal, subjective experience rather than describing the situation itself. Keep the response deeply personal and reflective of how {$target} would genuinely react.";

    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "Respond as {$target}, thinking privately to themselves about the current situation and recent events. Stay in first person, capturing their genuine thoughts, emotions, and internal conflicts. Focus on their personal perspective, biases, and feelings rather than an objective summary of events. Keep the response introspective and true to how {$target} would process and react internally.";
    }
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= "Aim for your responses to be 2-4 sentences. ";
}
else {
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "Choose the ACTION that best expresses your character's immediate physical and emotional responses. Focus on actions that reflect {$GLOBALS["HERIKA_NAME"]}'s current sensations and feelings when interacting with {$target}. Avoid narration and emoting.";
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "Choose the ACTION that best fits current context and character mood to interact with {$target}. You can also use an ACTION to interact with items, trade, inspect the world, attack or to express your characters needs. Avoid narration and emoting.";
    }
}

if (isset($GLOBALS["enforce_single_json"]) && $GLOBALS["enforce_single_json"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " Important: Provide only ONE single JSON response object per interaction. If multiple actions are desired, choose the most immediately relevant/important action and save additional actions for subsequent interactions. The response must be a single valid JSON object containing the character's next action or dialogue.";
}

if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"].=" You MUST respond with no more than 2-3 sentences and no more than 40 words.";
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
