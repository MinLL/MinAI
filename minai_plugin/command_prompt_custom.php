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
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["singing"]);
}
elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $mindState = GetMindInfluenceState($GLOBALS["PLAYER_NAME"]);
    $mindPrompt = GetMindInfluencePrompt($mindState, IsExplicitScene() ? "explicit" : (IsEnabled($GLOBALS["PLAYER_NAME"], "inCombat") ? "combat" : "default"));
    
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_explicit"]);
        if ($mindPrompt) {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " " . $mindPrompt;
        }
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_normal"]);
        if ($mindPrompt) {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " " . $mindPrompt;
        }
    }
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " Aim for your responses to be 2-4 sentences. ";
}
else {
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["explicit_scene"]);
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["normal_scene"]);
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


