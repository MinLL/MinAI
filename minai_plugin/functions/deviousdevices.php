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
        "desc" => "Place and lock a collar around #target_possessive# neck - marks ownership",
        "keyword" => "zad_DeviousCollar",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipCollar" => [
        "display" => "UnequipCollar",
        "desc" => "Remove the collar from #target_object# - releases #target_object# from this form of restraint",
        "keyword" => "zad_DeviousCollar",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipGag" => [
        "display" => "EquipGag",
        "desc" => "Place and secure a gag in #target_possessive# mouth - prevents clear speech and adds submission",
        "keyword" => "zad_DeviousGag",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipGag" => [
        "display" => "UnequipGag",
        "desc" => "Remove the gag from #target_possessive# mouth - allows #target_object# to speak clearly again",
        "keyword" => "zad_DeviousGag",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipVibrator" => [
        "display" => "EquipVibrator",
        "desc" => "Insert and secure a vibrator into #target_object# - enables remote pleasure control",
        "keyword" => "zad_DeviousPlugVaginal",
        "keyword2" => "zad_DeviousPlugAnal",
        "equip" => true,
        "belt_blocks" => true
    ],
    "ExtCmdUnequipVibrator" => [
        "display" => "UnequipVibrator",
        "desc" => "Remove a vibrator from #target_object# - ends stimulation and releases control",
        "keyword" => "zad_DeviousPlugVaginal",
        "keyword2" => "zad_DeviousPlugAnal",
        "equip" => false,
        "belt_blocks" => true
    ],
    "ExtCmdEquipBelt" => [
        "display" => "EquipBelt",
        "desc" => "Lock a chastity belt onto #target_object# - prevents sexual access or the removal of vibrators and establishes control",
        "keyword" => "zad_DeviousBelt",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBelt" => [
        "display" => "UnequipBelt",
        "desc" => "Remove a chastity belt from #target_object# - grants access to genitals and the ability to remove vibrators again",
        "keyword" => "zad_DeviousBelt",
        "equip" => false,
        "belt_blocks" => false
    ],
    "ExtCmdEquipBinder" => [
        "display" => "EquipBinder",
        "desc" => "Lock an armbinder onto #target_object# - restricts arm movement and increases helplessness",
        "keyword" => "zad_DeviousArmbinder",
        "equip" => true,
        "belt_blocks" => false
    ],
    "ExtCmdUnequipBinder" => [
        "display" => "UnequipBinder",
        "desc" => "Remove an armbinder from #target_object# - restores arm mobility and freedom",
        "keyword" => "zad_DeviousArmbinder",
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


