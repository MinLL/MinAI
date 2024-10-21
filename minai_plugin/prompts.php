<?php
require_once("config.php");
require_once("util.php");
require_once("sexPrompts.php");
require_once("customintegrations.php");

// Custom command / third party integrations support
// Done here, as this is mounted early in main.php
ProcessIntegrations();
$enforceLength = "You MUST Respond with no more than two sentences.";

$GLOBALS["PROMPTS"]["radiant"]= [
    "cue"=>[
        "write dialogue for {$GLOBALS["HERIKA_NAME"]}.{$GLOBALS["TEMPLATE_DIALOG"]}  "
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} starts a dialogue with {$GLOBALS["target"]} about a random topic",
    ]
];
$GLOBALS["PROMPTS"]["radiantsearchinghostile"]= [
    "cue"=>[
        "write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a hostile, and concerned manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and asks who is there?",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts threatening what he's going to do when he finds them",
    ]
];
$GLOBALS["PROMPTS"]["radiantsearchingfriend"]= [
    "cue"=>[
        "write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a concerned manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is currently searching the area for hostiles, and starts a dialogue with their ally {$GLOBALS["target"]} about this topic",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombathostile"]= [
    "cue"=>[
        "write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a hostile and combative manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and taunts them",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and trash-talks them",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is engaged in deadly combat with {$GLOBALS["target"]} and boasts about what they will do after {$GLOBALS["HERIKA_NAME"]} has defeated them ",
    ]
];
$GLOBALS["PROMPTS"]["radiantcombatfriend"]= [
    "cue"=>[
        "write dialogue for {$GLOBALS["HERIKA_NAME"]} who is responding in a tense, serious manner.{$GLOBALS["TEMPLATE_DIALOG"]}  $enforceLength"
    ], 
    "player_request"=>[    
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and talks about the battle",
        "The Narrator: {$GLOBALS["HERIKA_NAME"]} is teamed up with {$GLOBALS["target"]} in deadly combat against someone and asks for help",
    ]
];

$GLOBALS["PROMPTS"]["minai_combatendvictory"]= [
    "cue"=>[
        "({$GLOBALS["HERIKA_NAME"]} comments about foes defeated) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} curses the defeated enemies.) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} insults the defeated enemies with anger) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} makes a joke about the defeated enemies) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} makes a comment about the type of enemies that was defeated) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "({$GLOBALS["HERIKA_NAME"]} notes something peculiar about last enemy defeated) {$GLOBALS["TEMPLATE_DIALOG"]}"
    ],
    "extra"=>["force_tokens_max"=>"50","dontuse"=>(time()%10!=0)]   //10% chance
];
$GLOBALS["PROMPTS"]["minai_combatenddefeat"]= [
    "cue"=>[
        "({$GLOBALS["HERIKA_NAME"]} laments having been defeated in combat {$GLOBALS["TEMPLATE_DIALOG"]}"
    ]
];

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
