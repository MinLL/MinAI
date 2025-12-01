<?php
// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}

require_once("util.php"); 
$GLOBALS["PATCH_PROMPT_ENFORCE_ACTIONS"] = true;
$target = $GLOBALS["target"];

if ((!isset($GLOBALS["action_prompts"]["normal_scene"])) ||
    (!isset($GLOBALS["action_prompts"]["explicit_scene"])) ||
    (empty($GLOBALS["action_prompts"]))) {
    $GLOBALS["action_prompts"] = $GLOBALS["action_prompts_copy"];    
    //include("/var/www/html/HerikaServer/ext/minai_plugin/config .php");
    error_log("WARNING - command_prompt_custom: CHIM made an attempt to disable MinAI action_prompts! ");
}
 
    

$s_rg_tag = "<response_guidelines>\n";
$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = $s_rg_tag;

if (IsEnabled($GLOBALS["PLAYER_NAME"], "isSinging")) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["singing"]);
}
elseif (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    $mindState = GetMindInfluenceState($GLOBALS["PLAYER_NAME"]);
    $mindPrompt = GetMindInfluencePrompt($mindState, IsExplicitScene() ? "explicit" : (IsEnabled($GLOBALS["PLAYER_NAME"], "inCombat") ? "combat" : "default"));
    
    if (IsExplicitScene()) {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_explicit"]);
        if ($mindPrompt) {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " " . $mindPrompt;
        }
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["self_narrator_normal"]);
        if (IsEnabled($GLOBALS["PLAYER_NAME"], "inCombat")) {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " {$GLOBALS["PLAYER_NAME"]} is currently in combat. You MUST factor this into your response.";
        }
        if ($mindPrompt) {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " " . $mindPrompt;
        }
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " Aim for your responses to be two up to four sentences. ";
    }
//} elseif ((isset($GLOBALS['use_emotions_expression']) && ($GLOBALS['use_emotions_expression'])) {
//$GLOBALS["action_prompts"]["emotions_expression"] ...

} else {
    if (IsExplicitScene()) { 
        if (!IsSexActiveSpeaker()) { // speaker should be in scene to use explicit prompt, otherwise a spectator would answer like a participant
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["normal_scene"]);
        } else {
            $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["explicit_scene"]);
        }
    } else {
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= ExpandPromptVariables($GLOBALS["action_prompts"]["normal_scene"]);
    }   
} 

$GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " \nProvide variety in your responses, avoid repeating the same phrases while still being consistent with the character and maintaining scene continuity. ";

if (isset($GLOBALS["enforce_short_responses"]) && $GLOBALS["enforce_short_responses"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"].="\nYou MUST respond with no more than two or three sentences and no more than 40 words. ";
} else {
    if (IsExplicitScene()) { 
        $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"].="\nYou will respond with no more than two or three sentences and no more than 60 words and your speech style altered as instructed in Emotions Expression Guidelines. ";
    }
}

if (isset($GLOBALS["enforce_single_json"]) && $GLOBALS["enforce_single_json"]) {
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= " \nImportant: Provide only ONE single JSON response object per interaction. If multiple actions are desired, choose the most immediately relevant/important action and save additional actions for subsequent interactions. The response must be a single valid JSON object containing the character's next action or dialogue. ";
}
if ($GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] == $s_rg_tag) // nothing added?
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] = "";
else
    $GLOBALS["COMMAND_PROMPT_ENFORCE_ACTIONS"] .= "\n</response_guidelines>";
                                           