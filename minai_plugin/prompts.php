<?php
require_once("config.php");
require_once("util.php");
require_once("sexPrompts.php");
require_once("customintegrations.php");

// Custom command / third party integrations support
// Done here, as this is mounted early in main.php
ProcessIntegrations();

function SetInputPrompts($prompt) {
    error_log("minai: Overriding input prompts for combat");
    $GLOBALS["PROMPTS"]["inputtext"]= $prompt;
    $GLOBALS["PROMPTS"]["inputtext_s"]= $prompt;
    $GLOBALS["PROMPTS"]["ginputtext"]= $prompt;
}
// Override default prompts for combat dialogue
if (isset($GLOBALS["gameRequest"]) && in_array(strtolower($GLOBALS["gameRequest"][0]), ["inputtext","inputtext_s","ginputtext"])) {
    $inCombat = IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat");
    $hostile = IsEnabled($GLOBALS["HERIKA_NAME"], "hostileToPlayer");
    $combatPrompt = [
        "cue"=>[
            "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat and replies to {$GLOBALS["PLAYER_NAME"]}. $TEMPLATE_DIALOG $MAXIMUM_WORDS"
        ]
        // Prompt is implicit
    ];
    $hostilePrompt = [
        "cue"=>[
            "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} is currently hostile to {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}. $TEMPLATE_DIALOG $MAXIMUM_WORDS"
        ]
        // Prompt is implicit
    ];
    $hostileCombatPrompt = [
        "cue"=>[
            "$TEMPLATE_ACTION {$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat against {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}. $TEMPLATE_DIALOG $MAXIMUM_WORDS"
        ]
        // Prompt is implicit
    ];
    if ($hostile && $inCombat)
        SetInputPrompts($hostileCombatPrompt);
    elseif ($inCombat)
        SetInputPrompts($combatPrompt);
    elseif ($hostile)
        SetInputPrompts($hostilePrompt);
}


?>
