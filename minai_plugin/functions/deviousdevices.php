<?php

require_once("action_builder.php");
$target = $GLOBALS["target"];

// Function to check vibration capability (for use in withEnableCondition)
function canUseVibrations() {
    global $target;
    $canVibrate = CanVibrate($target);
    // Handle eldritch narrator special case
    $eldritch = IsEldritchNarratorActive();
    if ($eldritch && $canVibrate) {
        if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && isset($GLOBALS["devious_narrator"]) && $GLOBALS["devious_narrator"] == "eldritch") {
            $canVibrate = true;
        } else {
            $canVibrate = false;
        }
    }
    return $canVibrate;
}

function canStartVibrations() {
    global $target;
    $canVibrate = CanStartVibrator($target);
    // Handle eldritch narrator special case
    $eldritch = IsEldritchNarratorActive();
    if ($eldritch && $canVibrate) {
        if ($GLOBALS["HERIKA_NAME"] == "The Narrator" && isset($GLOBALS["devious_narrator"]) && $GLOBALS["devious_narrator"] == "eldritch") {
            $canVibrate = true;
        } else {
            $canVibrate = false;
        }
    }
    return $canVibrate;
}

// Check if NSFW is disabled globally
$nsfwDisabled = $GLOBALS["disable_nsfw"];

// Skip all registration if NSFW is disabled
if ($nsfwDisabled) {
    return;
}

// Cache conditions once
$canUseVibrations = canUseVibrations();
$canStartVibrations = canStartVibrations();
$inCombat = IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat");
$allowLock = IsConfigEnabled("allowDeviceLock");
$allowUnlock = IsConfigEnabled("allowDeviceUnlock");
$hasBelt = HasEquipmentKeyword($target, "zad_DeviousBelt");
$nearby = isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [];

// Basic vibration control actions
if ($canUseVibrations) {
    $basicVibratorActions = [
        "ExtCmdShock" => [
            "display" => "Shock",
            "desc" => "Apply a painful electrical shock to #target_object# - use to punish misbehavior or rapidly decrease arousal"
        ],
        "ExtCmdForceOrgasm" => [
            "display" => "ForceOrgasm",
            "desc" => "Force #target_object# to immediately experience an intense climax - effective reward or control mechanism"
        ],
        "ExtCmdTurnOffVibrator" => [
            "display" => "TurnOffVibrator",
            "desc" => "Deactivate any vibrating devices on #target_object# - use to deny pleasure or end stimulation"
        ]
    ];

    // Register basic vibration control actions
    foreach ($basicVibratorActions as $actionName => $actionInfo) {
        directRegisterAction(
            $actionName, 
            $actionInfo["display"], 
            $actionInfo["desc"], 
            true
        );
    }
}

// Vibration intensity settings
$vibSettings = ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"];

// Register teasing and stimulation actions with different intensities
if ($canStartVibrations) {
    // Register teasing actions
    foreach ($vibSettings as $strength) {
        $cleanStrength = str_replace(' ', '', $strength);
        
        // Teasing actions
        directRegisterAction(
            "ExtCmdTeaseWithVibrator" . $cleanStrength, 
            "TeaseWithVibrator" . $cleanStrength, 
            "Tease #target_object# with $strength vibrations that build arousal without allowing climax - perfect for control", 
            true
        );
        
        // Stimulation actions
        directRegisterAction(
            "ExtCmdStimulateWithVibrator" . $cleanStrength, 
            "StimulateWithVibrator" . $cleanStrength, 
            "Stimulate #target_object# with $strength vibrations that can lead to climax - use for gratification or reward", 
            true
        );
    }
}

// Bondage device actions
$bondageDevices = [
    "ExtCmdEquipCollar" => [
        "display" => "EquipCollar",
        "desc" => "Lock a collar around #target_possessive# neck - marks #target_object# as owned property",
        "keyword" => "zad_DeviousCollar",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipCollar" => [
        "display" => "UnequipCollar",
        "desc" => "Remove the collar from #target_possessive# neck - releases #target_object# from ownership",
        "keyword" => "zad_DeviousCollar",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipGag" => [
        "display" => "EquipGag",
        "desc" => "Lock a gag in #target_possessive# mouth - forces #target_object# to make muffled, submissive sounds",
        "keyword" => "zad_DeviousGag",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipGag" => [
        "display" => "UnequipGag",
        "desc" => "Remove the gag from #target_possessive# mouth - allows #target_object# to speak and express #target_possessive# desires",
        "keyword" => "zad_DeviousGag",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipBelt" => [
        "display" => "EquipBelt",
        "desc" => "Lock a chastity belt around #target_possessive# waist - prevents access to #target_possessive# pussy and ass, and blocks removal of plugs",
        "keyword" => "zad_DeviousBelt",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBelt" => [
        "display" => "UnequipBelt",
        "desc" => "Remove the chastity belt from #target_possessive# waist - grants access to #target_possessive# pussy and ass, allowing #target_object# to experience pleasure again",
        "keyword" => "zad_DeviousBelt",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipBinder" => [
        "display" => "EquipBinder",
        "desc" => "Lock an armbinder around #target_possessive# arms - forces #target_object# into a helpless, submissive position with arms tightly bound behind #target_possessive# back",
        "keyword" => "zad_DeviousArmbinder",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBinder" => [
        "display" => "UnequipBinder",
        "desc" => "Remove the armbinder from #target_possessive# arms - restores #target_possessive# ability to move freely",
        "keyword" => "zad_DeviousArmbinder",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipYoke" => [
        "display" => "EquipYoke",
        "desc" => "Lock a yoke around #target_possessive# arms - forces #target_object# into a helpless, submissive position with arms locked in a rigid position",
        "keyword" => "zad_DeviousYoke",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipYoke" => [
        "display" => "UnequipYoke",
        "desc" => "Remove the yoke from #target_possessive# arms - restores #target_possessive# ability to move freely",
        "keyword" => "zad_DeviousYoke",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipElbowTie" => [
        "display" => "EquipElbowTie",
        "desc" => "Lock an elbow tie around #target_possessive# arms - forces #target_object# into a helpless, submissive position with elbows bound tightly together",
        "keyword" => "zad_DeviousElbowTie",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipElbowTie" => [
        "display" => "UnequipElbowTie",
        "desc" => "Remove the elbow tie from #target_possessive# arms - restores #target_possessive# ability to move freely",
        "keyword" => "zad_DeviousElbowTie",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipStraitJacket" => [
        "display" => "EquipStraitJacket",
        "desc" => "Lock a strait jacket around #target_possessive# body - completely immobilizes #target_object# in a helpless position with arms crossed and secured",
        "keyword" => "zad_DeviousStraitJacket",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipStraitJacket" => [
        "display" => "UnequipStraitJacket",
        "desc" => "Remove the strait jacket from #target_possessive# body - restores #target_possessive# ability to move freely",
        "keyword" => "zad_DeviousStraitJacket",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipCorset" => [
        "display" => "EquipCorset",
        "desc" => "Lock a corset around #target_possessive# waist - forces #target_object# into a submissive posture while emphasizing #target_possessive# curves and restricting breathing",
        "keyword" => "zad_DeviousCorset",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipCorset" => [
        "display" => "UnequipCorset",
        "desc" => "Remove the corset from #target_possessive# waist - allows #target_object# to move and breathe freely",
        "keyword" => "zad_DeviousCorset",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipHood" => [
        "display" => "EquipHood",
        "desc" => "Lock a hood over #target_possessive# head - isolates #target_object# in darkness and restricts #target_possessive# senses and breathing",
        "keyword" => "zad_DeviousHood",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipHood" => [
        "display" => "UnequipHood",
        "desc" => "Remove the hood from #target_possessive# head - restores #target_possessive# vision and senses",
        "keyword" => "zad_DeviousHood",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipHobbleSkirt" => [
        "display" => "EquipHobbleSkirt",
        "desc" => "Lock a hobble skirt around #target_possessive# legs - forces #target_object# to take small, submissive steps with legs tightly bound together",
        "keyword" => "zad_DeviousHobbleSkirt",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipHobbleSkirt" => [
        "display" => "UnequipHobbleSkirt",
        "desc" => "Remove the hobble skirt from #target_possessive# legs - allows #target_object# to move freely",
        "keyword" => "zad_DeviousHobbleSkirt",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipGloves" => [
        "display" => "EquipGloves",
        "desc" => "Lock gloves onto #target_object# - restricts hand movement and dexterity",
        "keyword" => "zad_DeviousGloves",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipGloves" => [
        "display" => "UnequipGloves",
        "desc" => "Remove gloves from #target_object# - restores hand movement and dexterity",
        "keyword" => "zad_DeviousGloves",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipSuit" => [
        "display" => "EquipSuit",
        "desc" => "Lock a full body suit around #target_possessive# body - completely covers and restricts #target_object#, and blocks removal of belts and bras",
        "keyword" => "zad_DeviousSuit",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipSuit" => [
        "display" => "UnequipSuit",
        "desc" => "Remove the full body suit from #target_possessive# body - allows #target_object# to move freely",
        "keyword" => "zad_DeviousSuit",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipHarness" => [
        "display" => "EquipHarness",
        "desc" => "Lock a harness around #target_possessive# body - emphasizes #target_possessive# curves and provides control points for additional restraints",
        "keyword" => "zad_DeviousHarness",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipHarness" => [
        "display" => "UnequipHarness",
        "desc" => "Remove the harness from #target_possessive# body - removes the emphasis on #target_possessive# curves",
        "keyword" => "zad_DeviousHarness",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipBlindfold" => [
        "display" => "EquipBlindfold",
        "desc" => "Lock a blindfold over #target_possessive# eyes - isolates #target_object# in darkness and increases vulnerability",
        "keyword" => "zad_DeviousBlindfold",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBlindfold" => [
        "display" => "UnequipBlindfold",
        "desc" => "Remove the blindfold from #target_possessive# eyes - restores #target_possessive# vision",
        "keyword" => "zad_DeviousBlindfold",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipAnkleShackles" => [
        "display" => "EquipAnkleShackles",
        "desc" => "Lock ankle shackles around #target_possessive# legs - forces #target_object# to take small, submissive steps with ankles tightly bound",
        "keyword" => "zad_DeviousAnkleShackles",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipAnkleShackles" => [
        "display" => "UnequipAnkleShackles",
        "desc" => "Remove the ankle shackles from #target_possessive# legs - allows #target_object# to move freely",
        "keyword" => "zad_DeviousAnkleShackles",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipClamps" => [
        "display" => "EquipClamps",
        "desc" => "Lock painful nipple clamps onto #target_possessive# sensitive nipples - applies constant pressure and stimulation",
        "keyword" => "zad_DeviousClamps",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipClamps" => [
        "display" => "UnequipClamps",
        "desc" => "Remove the nipple clamps from #target_possessive# sensitive nipples - ends the constant pressure and stimulation",
        "keyword" => "zad_DeviousClamps",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipPlugVaginal" => [
        "display" => "EquipPlugVaginal",
        "desc" => "Insert and lock a vaginal plug deep into #target_possessive# pussy - provides constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPlugVaginal",
        "equip" => true,
        "belt_blocks" => true
    ],
    "ExtCmdUnequipPlugVaginal" => [
        "display" => "UnequipPlugVaginal",
        "desc" => "Remove the vaginal plug from #target_possessive# pussy - ends the constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPlugVaginal",
        "equip" => false,
        "belt_blocks" => true
    ],
    "ExtCmdEquipPlugAnal" => [
        "display" => "EquipPlugAnal",
        "desc" => "Insert and lock an anal plug deep into #target_possessive# ass - provides constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPlugAnal",
        "equip" => true,
        "belt_blocks" => true
    ],
    "ExtCmdUnequipPlugAnal" => [
        "display" => "UnequipPlugAnal",
        "desc" => "Remove the anal plug from #target_possessive# ass - ends the constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPlugAnal",
        "equip" => false,
        "belt_blocks" => true
    ],
    "ExtCmdEquipPiercingsNipple" => [
        "display" => "EquipPiercingsNipple",
        "desc" => "Lock nipple piercings onto #target_possessive# sensitive nipples - provides constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPiercingsNipple",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipPiercingsNipple" => [
        "display" => "UnequipPiercingsNipple",
        "desc" => "Remove the nipple piercings from #target_possessive# sensitive nipples - ends the constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPiercingsNipple",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipPiercingsVaginal" => [
        "display" => "EquipPiercingsVaginal",
        "desc" => "Lock clitoral piercings onto #target_possessive# sensitive clit - provides constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPiercingsVaginal",
        "equip" => true,
        "belt_blocks" => true
    ],
    "ExtCmdUnequipPiercingsVaginal" => [
        "display" => "UnequipPiercingsVaginal",
        "desc" => "Remove the clitoral piercings from #target_possessive# sensitive clit - ends the constant stimulation and control over #target_possessive# pleasure",
        "keyword" => "zad_DeviousPiercingsVaginal",
        "equip" => false,
        "belt_blocks" => true
    ],
    "ExtCmdEquipArmCuffs" => [
        "display" => "EquipArmCuffs",
        "desc" => "Lock arm cuffs onto #target_object# - restricts arm movement",
        "keyword" => "zad_DeviousArmCuffs",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipArmCuffs" => [
        "display" => "UnequipArmCuffs",
        "desc" => "Remove arm cuffs from #target_object# - restores arm movement",
        "keyword" => "zad_DeviousArmCuffs",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipLegCuffs" => [
        "display" => "EquipLegCuffs",
        "desc" => "Lock leg cuffs onto #target_object# - restricts leg movement",
        "keyword" => "zad_DeviousLegCuffs",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipLegCuffs" => [
        "display" => "UnequipLegCuffs",
        "desc" => "Remove leg cuffs from #target_object# - restores leg movement",
        "keyword" => "zad_DeviousLegCuffs",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipBra" => [
        "display" => "EquipBra",
        "desc" => "Lock a chastity bra around #target_possessive# chest - prevents access to #target_possessive# nipples and blocks removal of nipple piercings and clamps",
        "keyword" => "zad_DeviousBra",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBra" => [
        "display" => "UnequipBra",
        "desc" => "Remove the chastity bra from #target_possessive# chest - grants access to #target_possessive# sensitive nipples, allowing #target_object# to experience pleasure again",
        "keyword" => "zad_DeviousBra",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipPetSuit" => [
        "display" => "EquipPetSuit",
        "desc" => "Lock a full body pet suit around #target_possessive# body - forces #target_object# into a submissive, animalistic position and blocks removal of belts and bras",
        "keyword" => "zad_DeviousPetSuit",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipPetSuit" => [
        "display" => "UnequipPetSuit",
        "desc" => "Remove the pet suit from #target_possessive# body - allows #target_object# to move freely",
        "keyword" => "zad_DeviousPetSuit",
        "equip" => false,
        "belt_blocks" => false
    ]
];

// Skip all bondage action registration if in combat
if (!$inCombat) {
    // Pre-check device conditions
    foreach ($bondageDevices as $actionName => $actionInfo) {
        $keyword = $actionInfo["keyword"];
        $keyword2 = $actionInfo["keyword2"] ?? null;
        $beltBlocks = $actionInfo["belt_blocks"];
        $isEquip = $actionInfo["equip"];
        
        // Skip if basic conditions aren't met
        if ($isEquip && !$allowLock) continue;
        if (!$isEquip && !$allowUnlock) continue;
        
        // Check if device is already equipped/not-equipped
        $isEquipped = HasEquipmentKeyword($target, $keyword);
        if ($keyword2 !== null) {
            $isEquipped = $isEquipped || HasEquipmentKeyword($target, $keyword2);
        }
        
        // Skip if already in desired state
        if ($isEquip && $isEquipped) continue;
        if (!$isEquip && !$isEquipped) continue;
        
        // Skip if belt blocks and there is a belt
        if ($beltBlocks && $hasBelt) continue;
        
        // Set custom prompt template for this action
        $actionType = $isEquip ? "equipping" : "removing";
        $deviceType = str_replace(["ExtCmdEquip", "ExtCmdUnequip"], "", $actionName);
        // $GLOBALS["PROMPTS"]["afterfunc"]["cue"][$actionName] = "{$GLOBALS["HERIKA_NAME"]} comments on $actionType a $deviceType on/from {$target}. {$GLOBALS["TEMPLATE_DIALOG"]}";
        
        // Register the action
        directRegisterAction(
            $actionName, 
            $actionInfo["display"], 
            $actionInfo["desc"], 
            true, 
            [], 
            ["target"]
        );
    }
}


