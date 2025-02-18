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

function ExpandPromptVariables($prompt) {
    // Get pronouns for target, Herika, and player
    $targetPronouns = GetActorPronouns($GLOBALS["target"]);
    $herikaPronouns = GetActorPronouns($GLOBALS["HERIKA_NAME"]);
    $playerPronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
    
    $variables = array(
        '#target#' => $GLOBALS["target"],
        '#player_name#' => $GLOBALS["PLAYER_NAME"],
        '#herika_name#' => $GLOBALS["HERIKA_NAME"],
        // Add target pronoun variables
        '#target_subject#' => $targetPronouns["subject"],
        '#target_object#' => $targetPronouns["object"], 
        '#target_possessive#' => $targetPronouns["possessive"],
        // Add Herika pronoun variables
        '#herika_subject#' => $herikaPronouns["subject"],
        '#herika_object#' => $herikaPronouns["object"],
        '#herika_possessive#' => $herikaPronouns["possessive"],
        // Add player pronoun variables
        '#player_subject#' => $playerPronouns["subject"],
        '#player_object#' => $playerPronouns["object"],
        '#player_possessive#' => $playerPronouns["possessive"]
    );
    
    return str_replace(array_keys($variables), array_values($variables), $prompt);
}

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["singing"]);
}
elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_explicit"]);
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_normal"]);
    }
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= "Aim for your responses to be 2-4 sentences. ";
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


