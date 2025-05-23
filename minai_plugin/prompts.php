<?php
// Avoid processing for fast / storage events
if (isset($GLOBALS["minai_skip_processing"]) && $GLOBALS["minai_skip_processing"]) {
    return;
}
minai_start_timer("prompts_php", "MinAI");

require_once("config.php");
require_once("util.php");
require_once("sexPrompts.php");
require_once("customintegrations.php");
require_once("functions/deviousnarrator.php");
// Custom command / third party integrations support
// Done here, as this is mounted early in main.php
ProcessIntegrations();
$cleanedMessage = GetCleanedMessage();
$enforceLength = "You MUST Respond with no more than two sentences.";

$GLOBALS["PROMPTS"]["radiant"]= [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} " ////'write' prefix lead to double answers, TEMPLATE_DIALOG already has a "Write ..." => the result is "write dialogue ... Write next line"
        "{$GLOBALS["TEMPLATE_DIALOG"]} "
    ],
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} starts a dialogue with {$GLOBALS["target"]} about a relevant topic",
    ]
];

$GLOBALS["PROMPTS"]["minai_force_rechat"]= [
    "cue"=>[
        "{$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  "
    ],
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} responds to {$GLOBALS["target"]} about the ongoing conversation",
    ]
];

$GLOBALS["PROMPTS"]["radiantsearchinghostile"]= [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a hostile, and concerned manner. {$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength" //lead to double answers 
        "{$GLOBALS["HERIKA_NAME"]} is responding in in a hostile and concerned manner. {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and asks who is there?",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts threatening what he's going to do when he finds them",
    ]
];
$GLOBALS["PROMPTS"]["radiantsearchingfriend"]= [
    "cue"=>[
        //"write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a concerned manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
        "{$GLOBALS["HERIKA_NAME"]} is responding in a concerned manner. {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts a dialogue with their ally {$GLOBALS["target"]} about this topic",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombathostile"]= [
    "cue"=>[
        "{$GLOBALS["HERIKA_NAME"]} is responding in a hostile and combative manner. {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and taunts them",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and trash-talks them",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and boasts about what they will do after {$GLOBALS["HERIKA_NAME"]} has defeated them ",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombatfriend"]= [
    "cue"=>[
        "{$GLOBALS["HERIKA_NAME"]} is responding in a tense, serious manner. {$GLOBALS["TEMPLATE_DIALOG"]} $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and talks about the battle",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and asks for help",
    ]
];

if ($GLOBALS["gameRequest"][0] == "minai_combatendvictory" || $GLOBALS["gameRequest"][0] == "info_minai_combatendvictory") {
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["minai_combatendvictory"]= [
        "cue"=>[
            "({$GLOBALS["HERIKA_NAME"]} comments about foes defeated) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} curses the defeated enemies.) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} insults the defeated enemies with anger) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} makes a joke about the defeated enemies) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} makes a comment about the type of enemies that was defeated) {$GLOBALS["TEMPLATE_DIALOG"]}",
            "({$GLOBALS["HERIKA_NAME"]} notes something peculiar about last enemy defeated) {$GLOBALS["TEMPLATE_DIALOG"]}"
        ],
        "player_request"=>[$narratePrompt],
        "extra"=>["dontuse"=>(time()%10!=0)]   //10% chance
    ];
}

if ($GLOBALS["gameRequest"][0] == "minai_bleedoutself" || $GLOBALS["gameRequest"][0] == "info_minai_bleedoutself") {
    $narratePrompt = "The Narrator: {$cleanedMessage}";
    $GLOBALS["PROMPTS"]["minai_bleedoutself"]= [
        "cue"=>[
            "{$GLOBALS["HERIKA_NAME"]} calls out for help after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$GLOBALS["HERIKA_NAME"]} cries out in pain after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
            "{$GLOBALS["HERIKA_NAME"]} expresses their resolve after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
        ],
        "player_request"=>[$narratePrompt]
    ];
}

$GLOBALS["PROMPTS"]["goodmorning"]=[
    "cue"=>[
        (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] ? 
            "({$GLOBALS["HERIKA_NAME"]} comment about {$GLOBALS["PLAYER_NAME"]}s time asleep. {$GLOBALS["TEMPLATE_DIALOG"]})" :
            "({$GLOBALS["HERIKA_NAME"]} comment about {$GLOBALS["PLAYER_NAME"]}s time asleep. {$GLOBALS["TEMPLATE_DIALOG"]})"
        )
    ],
    "player_request"=>[
        (ShouldUseDeviousNarrator() ? 
            (($questState = intval(GetActorValue($GLOBALS['PLAYER_NAME'], "deviouslyAccessibleGlobal"))) && 
             ($telvanniScore = ($questState % 10)) > 0 ? 
                (($eyescore = GetActorValue($GLOBALS['PLAYER_NAME'], "deviouslyAccessibleEyeScore")) > 10 ? 
                    "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had an intensely pleasurable dream in which she uncontrollably climaxed repeatedly. She looks content and relaxed." :
                    ($eyescore > 0 ? 
                        "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had a dream of constant stimulation without release. She looks aroused and frustrated." :
                        "The Narrator: {$GLOBALS["PLAYER_NAME"]} woke up from sleeping. She had a humiliating and degrading dream in which she was raped and humiliated. She looks ashamed and humiliated."
                    )
                ) : "{$GLOBALS["PLAYER_NAME"]} wakes up from sleeping. ahhhh"
            ) : "{$GLOBALS["PLAYER_NAME"]} wakes up from sleeping. ahhhh"
        )
    ],
    "extra" => (!empty($GLOBALS["RPG_COMMENTS"]) && in_array("sleep", $GLOBALS["RPG_COMMENTS"])) ? [] : ["dontuse" => true]
];

if (IsFollower($GLOBALS["HERIKA_NAME"])) {
    $GLOBALS["PROMPTS"]["minai_combatenddefeat"]= [
        "cue"=>[
            "({$GLOBALS["HERIKA_NAME"]} laments having been defeated in combat {$GLOBALS["TEMPLATE_DIALOG"]}"
        ]
    ];
} else {
    $GLOBALS["PROMPTS"]["minai_combatenddefeat"]= [
        "cue"=>[
            "({$GLOBALS["HERIKA_NAME"]} gloats about defeating {$GLOBALS["target"]} in combat and boasts about what they will do next {$GLOBALS["TEMPLATE_DIALOG"]}"
        ]
    ];
}

function SetInputPrompts($prompt) {
    minai_log("info", "Overriding input prompts for combat for {$GLOBALS["HERIKA_NAME"]}");
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
            "{$GLOBALS["TEMPLATE_ACTION"]} {$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat and replies to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
        // Prompt is implicit
    ];
    $hostilePrompt = [
        "cue"=>[
            "{$GLOBALS["TEMPLATE_ACTION"]} {$GLOBALS["HERIKA_NAME"]} is currently hostile to {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
        ]
        // Prompt is implicit
    ];
    $hostileCombatPrompt = [
        "cue"=>[
            "{$GLOBALS["TEMPLATE_ACTION"]} {$GLOBALS["HERIKA_NAME"]} is currently engaged in deadly combat against {$GLOBALS["PLAYER_NAME"]} and replies in a hostile manner to {$GLOBALS["PLAYER_NAME"]}. {$GLOBALS["TEMPLATE_DIALOG"]} {$GLOBALS["MAXIMUM_WORDS"]}"
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

if (isset($GLOBALS["self_narrator"]) && $GLOBALS["self_narrator"] && $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    // Only set diary prompt if one is provided
    if (isset($GLOBALS["action_prompts"]["player_diary"]) && !empty($GLOBALS["action_prompts"]["player_diary"])) {
        // Get mind influence state and prompts
        $mindState = GetMindInfluenceState($GLOBALS["PLAYER_NAME"]);
        $mindPrompt = GetMindInfluencePrompt($mindState);

        $diaryPrompt = ExpandPromptVariables($GLOBALS["action_prompts"]["player_diary"]);
        
        // Add mind influence context if any exists
        if (!empty($mindPrompt)) {
            $diaryPrompt .= " " . $mindPrompt . " Write your diary entry reflecting your current mental state.";
        }

        $GLOBALS["PROMPTS"]["diary"] = [
            "cue"=>[$diaryPrompt],
            "extra"=>["force_tokens_max"=>0]
        ];
    }
}
else {
    // Only set diary prompt if one is provided
    if (isset($GLOBALS["action_prompts"]["follower_diary"]) && !empty($GLOBALS["action_prompts"]["follower_diary"])) {
        $GLOBALS["PROMPTS"]["diary"] = [
            "cue"=>[ExpandPromptVariables($GLOBALS["action_prompts"]["follower_diary"])],
            "extra"=>["force_tokens_max"=>0]
        ];
    }
}


if (isset($GLOBALS["minai_processing_input"]) && $GLOBALS["minai_processing_input"]) {
    error_log("PROCESSING INPUT");
    $cue = [];

    if ($GLOBALS["using_self_narrator"]) {
        $pronouns = GetActorPronouns($GLOBALS["PLAYER_NAME"]);
        $cue[] = "Write a response as {$GLOBALS["PLAYER_NAME"]} thinking to {$pronouns["object"]}self about {$pronouns["object"]} most recent thought.";
    }
    $GLOBALS["PROMPTS"]["inputtext"] = [
        "cue"=>$cue,
        // Prompt is implicit
    ];
    $GLOBALS["PROMPTS"]["inputtext_s"] = [
        "cue"=>$cue,
        // Prompt is implicit
    ];
}

require_once("prompts/info_tntr_prompts.php");
require_once("prompts/info_fillherup_prompts.php");
require_once("prompts/info_vibrator_prompts.php");
require_once("prompts/info_narrate.php");

minai_stop_timer("prompts_php");