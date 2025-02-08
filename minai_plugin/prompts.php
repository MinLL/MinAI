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

$GLOBALS["PROMPTS"]["minai_bleedoutself"]= [
    "cue"=>[
        "{$GLOBALS["HERIKA_NAME"]} calls out for help after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$GLOBALS["HERIKA_NAME"]} cries out in pain after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
        "{$GLOBALS["HERIKA_NAME"]} expresses their resolve after being badly wounded! {$GLOBALS["TEMPLATE_DIALOG"]} ",
    ],
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
    error_log("minai: Overriding input prompts for combat for {$GLOBALS["HERIKA_NAME"]}");
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
    $GLOBALS["PROMPTS"]["diary"] = [ 
        "cue"=>["Please write a summary of {$GLOBALS["PLAYER_NAME"]}'s recent thoughts, feelings, and adventures. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings, emphasizing in detail any intimate encounters that may have occurred. WRITE AS IF YOU ARE {$GLOBALS["PLAYER_NAME"]}."],
        "extra"=>["force_tokens_max"=>0]
    ];
}
else {
    $GLOBALS["PROMPTS"]["diary"] = [ 
        "cue"=>["Please write a summary of {$GLOBALS["HERIKA_NAME"]}s recent thoughts, feelings, and adventures with {$GLOBALS["PLAYER_NAME"]}. Respond as if you are writing this into a private diary, including completely unfiltered thoughts and feelings, emphasizing in detail any intimate encounters that may have occurred. WRITE AS IF YOU ARE {$GLOBALS["HERIKA_NAME"]}."],
        "extra"=>["force_tokens_max"=>0]
    ];
}

// Early Mimic events - pure fear/resistance
$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervoreinstant"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest): The Mimic chest suddenly engulfs {$GLOBALS["target"]} into its dark interior! They scream in terror, desperately thrashing and fighting to escape!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest): Without warning, the chest springs its trap, dragging {$GLOBALS["target"]} inside! Their panicked cries echo as they struggle against its iron grip!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest): {$GLOBALS["target"]} is yanked into the Mimic's maw! They kick and fight in blind panic, their heart pounding with pure terror!) {$GLOBALS["TEMPLATE_DIALOG"]}"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervorestart"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest): Tentacles quickly extend from the chest, wrapping around {$GLOBALS["target"]}, pulling them closer! This chest is not what it seems! {$GLOBALS["target"]} struggles frantically, their eyes wide with fear!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest):Slick tentacles emerge from the chest, coiling tightly around {$GLOBALS["target"]}'s limbs! They thrash wildly, trying desperately to break free from the creature's grasp!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being assaulted by a Mimic Chest):{$GLOBALS["target"]} recoils in horror as the chest reveals its true nature, tentacles wrapping around their struggling form! Their heart pounds with terror as they're drawn closer!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_transvorelooptosuccess"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is bound and trapped by a Mimic Chest): The lid of the Mimic slams shut, trapping {$GLOBALS["target"]}'s upper body inside! They struggle frantically in the darkness, desperate to break free!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is bound and trapped by a Mimic Chest): {$GLOBALS["target"]} finds themselves trapped waist-deep in the creature's grasp! They thrash wildly, fighting against the tight confines with all their strength!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is bound and trapped by a Mimic Chest): The Mimic holds {$GLOBALS["target"]} firmly bent over its edge! Their muffled cries of fear echo inside as they desperately try to push themselves out!) {$GLOBALS["TEMPLATE_DIALOG"]}"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervorestage02fail"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just escaped from the Mimic Chest): {$GLOBALS["target"]} manages to tear free from the Mimic's grasp, scrambling away from the creature!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just escaped from the Mimic Chest): With a desperate surge of strength, {$GLOBALS["target"]} breaks loose from the Mimic's hold!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just escaped from the Mimic Chest): {$GLOBALS["target"]}'s fierce struggle pays off as they wrench themselves free from the Mimic's grip!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervorespit"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just released by the Mimic Chest after being thoroughly raped): {$GLOBALS["target"]} gasps for air as the Mimic finally releases them, collapsing to the ground with their body trembling from the intense ordeal!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just released by the Mimic Chest after being thoroughly raped): The Mimic expels {$GLOBALS["target"]}, who falls weakly to their knees, still shaking from their unwilling submission!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has just released by the Mimic Chest after being thoroughly raped): {$GLOBALS["target"]} is released from the Mimic's grasp, their defeated form quivering as they try to recover from the experience!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_transvoreendsuccessloop"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her): {$GLOBALS["target"]} has been completely swallowed by the Mimic! As tentacles begin slithering over their body, their fierce resistance starts to weaken against their will!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her): Deep within the Mimic's interior, {$GLOBALS["target"]} continues to struggle, though the constant caress of tentacles begins to affect their senses!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her): The Mimic's tentacles wrap tighter around {$GLOBALS["target"]}'s trapped form! Their determined struggles slowly give way to involuntary shivers!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggermimicthrowup"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her, and dose her with an aphrodisiac): The Mimic strips away {$GLOBALS["target"]}'s clothing! They gasp as strange fluids seep into their skin, making their body tingle against their will!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her, and dose her with an aphrodisiac): {$GLOBALS["target"]}'s armor and clothing are torn away by the Mimic! An alien warmth spreads through them as the creature's secretions take effect!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely swallowed by the Mimic, which begins to rape her, and dose her with an aphrodisiac): The Mimic efficiently disarms and strips {$GLOBALS["target"]} bare! They try to resist as the creature's fluids begin affecting their exposed skin!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggermimicburp"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The Mimic shudders with satisfaction, finished with its thoroughly conquered prey!",
        "The Narrator: #SEX_INFO Having claimed its victory, the Mimic settles back, satisfied with {$GLOBALS["target"]}'s complete submission!",
        "The Narrator: #SEX_INFO The Mimic rumbles contentedly, having dominated {$GLOBALS["target"]} entirely!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervorestage02start"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move with her ass up in the air, and has been dosed with an aphrodisiac):  The Mimic shifts its grip on {$GLOBALS["target"]}, tentacles sliding between their legs, and relentlessly stimulating their most sensitive spots! Their defiant struggles weaken as unwanted pleasure begins to build!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move with her ass up in the air, and has been dosed with an aphrodisiac): {$GLOBALS["target"]} gasps as tentacles find their most sensitive spots, and begin to vigorously stimulate them! Though they try to resist, their body begins responding to the intimate touches!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move with her ass up in the air, and has been dosed with an aphrodisiac):  Despite {$GLOBALS["target"]}'s continued resistance, they can't help but moan as the Mimic's tentacles explore their vulnerable form, rubbing against their most sensitive spots in a highly stimulating manner!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggervorestage02success"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): The Mimic forces {$GLOBALS["target"]} onto their back, roughly fucking their bound and helpless body! Their last resistance crumbles as pleasure overwhelms their senses!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): {$GLOBALS["target"]}'s will finally breaks as the Mimic takes full control, tentacles plunging deep inside their bound and helpless form!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): Unable to fight any longer, {$GLOBALS["target"]} surrenders completely as the Mimic's tentacles roughly plunge into them and fuck their bound and helpless body!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_transvorestage02successloop"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): Despite their best efforts to resist, {$GLOBALS["target"]}'s exhausted body finally surrenders to a powerful climax as the Mimic's tentacles continue their relentless assault!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): {$GLOBALS["target"]}'s last defenses crumble as the Mimic forces them over the edge, their body convulsing in unwilling pleasure!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} has been completely bound by the Mimics tentacles as it rapes her. The Mimic's lid is currently open. {$GLOBALS["PLAYER_NAME"]} is helplessly bound and unable to move on her back with her legs spread, and has been dosed with an aphrodisiac): Unable to fight any longer, {$GLOBALS["target"]} succumbs completely as the Mimic's tentacles drive them to an overwhelming peak!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_triggeropen"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The Mimic chest opens wide, its magical aura pulsing ominously as it awaits its prey!",
        "The Narrator: #SEX_INFO A sinister energy radiates from the Mimic as its lid creaks open, ready to trap its next victim!",
        "The Narrator: #SEX_INFO The disguised Mimic reveals its maw, an otherworldly glow emanating from its depths!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_mimic_transopenidle"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The Mimic remains perfectly still, its open form a deceptive invitation to the unwary!",
        "The Narrator: #SEX_INFO Looking like an ordinary chest, the Mimic waits patiently for someone to approach!",
        "The Narrator: #SEX_INFO The Mimic maintains its disguise, its true nature hidden as it lies in wait!"
    ]
];

// Death Worm Events - Complete List
$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trigger01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} encounters a Death Worm): A massive Death Worm erupts from the earth beneath {$GLOBALS["target"]}, its enormous form blocking out the light!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} encounters a Death Worm): The ground trembles violently before a Death Worm bursts forth, its massive body coiling menacingly around {$GLOBALS["target"]}!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} encounters a Death Worm): {$GLOBALS["target"]} barely has time to scream as a Death Worm explodes from the ground, its huge form rising up before them!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trigger02"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is suddenly attacked by a Death Worm): The Death Worm's coils surge upward, wrapping tightly around {$GLOBALS["target"]}'s waist and legs as it prepares to swallow them whole!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is suddenly attacked by a Death Worm): The massive Death Worm coils menacingly around {$GLOBALS["target"]}, its jaws unhinging as it readies to devour them!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is suddenly attacked by a Death Worm): The Death Worm's enormous body wraps around {$GLOBALS["target"]}, preparing to consume them entirely!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trigger02gulp"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being swallowed by a Death Worm): The Death Worm's massive jaws unhinge as it begins to swallow {$GLOBALS["target"]} whole!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being swallowed by a Death Worm): {$GLOBALS["target"]} watches in horror as the Death Worm's maw opens impossibly wide to devour them!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is being swallowed by a Death Worm): The Death Worm's jaws stretch open, ready to engulf {$GLOBALS["target"]} entirely!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trans02gulpdone"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is released from the Death Worm's gullet): The Death Worm suddenly releases {$GLOBALS["target"]} from its gullet, leaving them shaken but alive!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is released from the Death Worm's gullet): {$GLOBALS["target"]} is expelled from the Death Worm's maw, gasping for air as they're freed!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is released from the Death Worm's gullet): The Death Worm spits out {$GLOBALS["target"]}, who collapses to the ground in relief!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trigger02spit"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} watches as the Death Worm retreats): The Death Worm retreats back into the sand, disappearing beneath the surface!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} watches as the Death Worm retreats): With a final rumble, the Death Worm burrows back into the earth!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} watches as the Death Worm retreats): The Death Worm slides away, vanishing into the sandy depths!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_trigger03"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is instantly devoured by a Death Worm): The Death Worm suddenly lunges forward, instantly devouring {$GLOBALS["target"]} whole!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is instantly devoured by a Death Worm): Without warning, the Death Worm strikes, swallowing {$GLOBALS["target"]} in one swift motion!",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} is instantly devoured by a Death Worm): The Death Worm attacks with lightning speed, consuming {$GLOBALS["target"]} before they can react!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathwormvore_reset"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} observes the Death Worm's departure): The Death Worm returns to its waiting position beneath the sand.",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} observes the Death Worm's departure): The massive creature settles back into its sandy lair.",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} observes the Death Worm's departure): The Death Worm retreats to its hiding spot under the surface."
    ]
];

// Snare Trap Events - Complete List
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggera01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO Enchanted ropes spring from the ground, wrapping around {$GLOBALS["target"]}'s limbs! They struggle against the magical bindings, trying to break free!",
        "The Narrator: #SEX_INFO A magical snare activates, its glowing ropes quickly entangling {$GLOBALS["target"]}! They fight against the arcane trap's grip!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} is caught by surprise as mystical bindings emerge, the enchanted ropes pulling their limbs tight!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transa01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The enchanted ropes pull {$GLOBALS["target"]}'s arms behind their back, magical energy crackling along the bindings!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} is forced to their knees as the mystical snare tightens, the ropes glowing with arcane power!",
        "The Narrator: #SEX_INFO The magical trap secures {$GLOBALS["target"]}'s limbs with supernatural strength, leaving them bound and helpless!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerdisarm"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO Someone rushes to disarm the magical snare holding {$GLOBALS["target"]} captive!",
        "The Narrator: #SEX_INFO A rescuer attempts to deactivate the arcane trap binding {$GLOBALS["target"]}!",
        "The Narrator: #SEX_INFO Help arrives as someone works to disable the mystical bonds restraining {$GLOBALS["target"]}!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transtrapdisarm"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The magical snare's power fades as it's successfully disarmed!",
        "The Narrator: #SEX_INFO The enchanted trap's energy dissipates, its bindings loosening their hold!",
        "The Narrator: #SEX_INFO The arcane snare is neutralized, its mystical bonds losing their strength!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerrearm"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The magical energies begin to gather as the snare trap is reset!",
        "The Narrator: #SEX_INFO Arcane power flows back into the trap as someone prepares it for reuse!",
        "The Narrator: #SEX_INFO The mystical bindings recharge with energy as the snare is readied again!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transrearm"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The snare trap pulses with renewed magical energy, ready to capture its next victim!",
        "The Narrator: #SEX_INFO The enchanted trap lies in wait, its mystical bonds prepared to spring on the unwary!",
        "The Narrator: #SEX_INFO Fully recharged, the arcane snare awaits its next unfortunate target!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerd01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} fights desperately against the ropes as they're lifted higher, but each movement only makes the magical bindings constrict tighter!",
        "The Narrator: #SEX_INFO Suspended in the air, {$GLOBALS["target"]} struggles wildly against the enchanted bonds, but the trap only pulls them further from the ground!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} twists frantically in the snare's grasp, their efforts only causing the mystical ropes to hoist them higher!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transstruggle"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The enchanted ropes swing {$GLOBALS["target"]} through the air, their fierce struggles weakening as the magic drains their strength!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s resistance falters as they dangle helplessly, the mystical bonds slowly sapping their energy!",
        "The Narrator: #SEX_INFO Suspended by the magical snare, {$GLOBALS["target"]}'s determined struggles begin to fade as the arcane trap takes its toll!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerb01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} continues to fight as the magical ropes lift them higher, their limbs flailing uselessly in the air!",
        "The Narrator: #SEX_INFO The enchanted snare pulls {$GLOBALS["target"]} further from the ground, their desperate struggles growing weaker!",
        "The Narrator: #SEX_INFO Dangling in the trap's grip, {$GLOBALS["target"]} tries to break free as the mystical bonds hoist them skyward!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transb01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} twists weakly in the magical bonds, their strength failing as they hang suspended in the air!",
        "The Narrator: #SEX_INFO The enchanted ropes maintain their hold as {$GLOBALS["target"]}'s struggles grow feebler, leaving them dangling helplessly!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s resistance wanes, their form swaying gently in the mystical snare's unyielding grasp!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerc01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO With their last reserves of strength, {$GLOBALS["target"]} makes one final attempt to break free from the magical snare!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} summons their remaining energy for a desperate bid to escape the enchanted trap's hold!",
        "The Narrator: #SEX_INFO Suspended in the air, {$GLOBALS["target"]} gathers their willpower for one last struggle against the mystical bonds!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transc01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO Through sheer force of will, {$GLOBALS["target"]} breaks free from the magical snare! They drop to the ground, shaking from the ordeal!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s desperate effort succeeds as they tear loose from the enchanted ropes, landing roughly but free!",
        "The Narrator: #SEX_INFO With a final surge of strength, {$GLOBALS["target"]} escapes the mystical trap's grasp, falling back to solid ground!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transb02"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO The enchanted bindings constrict further, magical energy pulsing through the ropes as {$GLOBALS["target"]} struggles!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]} fights against the snare's grip, but the mystical ropes only tighten their supernatural hold!",
        "The Narrator: #SEX_INFO The magical trap strengthens its grasp, arcane energy coursing through the ropes as they restrain {$GLOBALS["target"]}!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transb03"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s determined resistance begins to fade as they hang suspended, their strength draining away in the magical bonds!",
        "The Narrator: #SEX_INFO The mystical ropes pulse with arcane energy as {$GLOBALS["target"]}'s struggles grow increasingly weak and uncoordinated!",
        "The Narrator: #SEX_INFO Dangling helplessly in the air, {$GLOBALS["target"]}'s fierce defiance slowly crumbles under the snare's relentless magic!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_triggerkillend"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO After an exhausting battle, {$GLOBALS["target"]}'s resistance finally shatters. The magical snare's power leaves them hanging limply in defeat!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s last reserves of strength fail them as the enchanted trap's magic proves too powerful to resist!",
        "The Narrator: #SEX_INFO The mystical bonds claim their final victory as {$GLOBALS["target"]}'s will to fight is completely drained away!"
    ]
];
$GLOBALS["PROMPTS"]["minai_tntr_snare_transtrapkill"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO Despite their valiant struggle, the magical snare claims another victim. {$GLOBALS["target"]} hangs motionless in its arcane grip!",
        "The Narrator: #SEX_INFO The enchanted trap proves too powerful, leaving {$GLOBALS["target"]} suspended and thoroughly subdued by its magic!",
        "The Narrator: #SEX_INFO {$GLOBALS["target"]}'s fierce resistance ends in defeat as the mystical bonds drain away the last of their strength!"
    ]
];

$GLOBALS["PROMPTS"]["minai_tntr_deathworm_trigger01"] = [
    "player_request"=>[
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} notices something strange about the ground): {$GLOBALS["target"]} feels a faint vibration beneath their feet. The ground seems to pulse with an unnatural rhythm!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} notices something strange about the ground): A subtle tremor runs through the earth, causing {$GLOBALS["target"]} to pause mid-step. Something feels wrong!) {$GLOBALS["TEMPLATE_DIALOG"]}",
        "The Narrator: #SEX_INFO (Scenario: {$GLOBALS["PLAYER_NAME"]} notices something strange about the ground): {$GLOBALS["target"]} notices small pebbles starting to shift and roll across the ground. An odd stillness fills the air!) {$GLOBALS["TEMPLATE_DIALOG"]}"
    ]
];
?>
