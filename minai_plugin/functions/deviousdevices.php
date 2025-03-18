<?php

require_once("action_builder.php");

// Function to check vibration capability (for use in withEnableCondition)
function canUseVibrations() {
    $target = GetTargetActor();
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

// Set prompt templates for return message generation
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdShock"] = "{$GLOBALS["HERIKA_NAME"]} comments on remotely shocking {$target}. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdForceOrgasm"] = "{$GLOBALS["HERIKA_NAME"]} comments on remotely forcing {$target} to have an orgasm. {$GLOBALS["TEMPLATE_DIALOG"]}";
$GLOBALS["PROMPTS"]["afterfunc"]["cue"]["ExtCmdTurnOffVibrator"] = "{$GLOBALS["HERIKA_NAME"]} comments on turning off {$target}'s vibrator. {$GLOBALS["TEMPLATE_DIALOG"]}";

// Basic vibration control actions
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
    registerMinAIAction($actionName, $actionInfo["display"])
        ->withDescription($actionInfo["desc"])
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
        ->isNSFW()
        ->withEnableCondition('canUseVibrations')
        ->withReturnFunction($GLOBALS["GenericFuncRet"])
        ->register();
}

// Vibration intensity settings
$vibSettings = ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"];

// Register teasing actions with different intensities
foreach ($vibSettings as $strength) {
    $cleanStrength = str_replace(' ', '', $strength);
    $actionName = "ExtCmdTeaseWithVibrator" . $cleanStrength;
    $displayName = "TeaseWithVibrator" . $cleanStrength;
    $description = "Tease #target_object# with $strength vibrations that build arousal without allowing climax - perfect for control";
    
    // Set prompt template for this tease action
    $GLOBALS["PROMPTS"]["afterfunc"]["cue"][$actionName] = "{$GLOBALS["HERIKA_NAME"]} comments on remotely teasing {$target} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
    
    registerMinAIAction($actionName, $displayName)
        ->withDescription($description)
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
        ->isNSFW()
        ->withEnableCondition('canUseVibrations')
        ->withReturnFunction($GLOBALS["GenericFuncRet"])
        ->register();
}

// Register stimulation actions with different intensities
foreach ($vibSettings as $strength) {
    $cleanStrength = str_replace(' ', '', $strength);
    $actionName = "ExtCmdStimulateWithVibrator" . $cleanStrength;
    $displayName = "StimulateWithVibrator" . $cleanStrength;
    $description = "Stimulate #target_object# with $strength vibrations that can lead to climax - use for gratification or reward";
    
    // Set prompt template for this stimulate action
    $GLOBALS["PROMPTS"]["afterfunc"]["cue"][$actionName] = "{$GLOBALS["HERIKA_NAME"]} comments on remotely stimulating {$target} with a $strength vibration. {$GLOBALS["TEMPLATE_DIALOG"]}";
    
    registerMinAIAction($actionName, $displayName)
        ->withDescription($description)
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
        ->isNSFW()
        ->withEnableCondition('canUseVibrations')
        ->withReturnFunction($GLOBALS["GenericFuncRet"])
        ->register();
}

// Function to check if devices can be modified
function canModifyDevices($isEquip, $keyword, $keyword2 = null, $beltBlocks = false) {
    global $target;
    
    // Don't equip/unequip during combat
    if (IsEnabled($GLOBALS["HERIKA_NAME"], "inCombat")) {
        return false;
    }
    
    // Check if device operation is allowed in config
    if ($isEquip) {
        if (!IsConfigEnabled("allowDeviceLock")) {
            return false;
        }
        
        // Check if device is already equipped
        if (HasKeyword($target, $keyword)) {
            return false;
        }
    } else {
        if (!IsConfigEnabled("allowDeviceUnlock")) {
            return false;
        }
        
        // Check if device is equipped
        $isEquipped = HasKeyword($target, $keyword);
        if ($keyword2 !== null) {
            $isEquipped = $isEquipped || HasKeyword($target, $keyword2);
        }
        
        if (!$isEquipped) {
            return false;
        }
    }
    
    // If we need to check for belt blocking
    if ($beltBlocks) {
        return !HasKeyword($target, "zad_DeviousBelt");
    }
    
    return true;
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

// Register bondage device actions
foreach ($bondageDevices as $actionName => $actionInfo) {
    $keyword = $actionInfo["keyword"];
    $keyword2 = $actionInfo["keyword2"] ?? null;
    $beltBlocks = $actionInfo["belt_blocks"];
    $isEquip = $actionInfo["equip"];
    
    // Set custom prompt template for this action
    $actionType = $isEquip ? "equipping" : "removing";
    $deviceType = str_replace(["ExtCmdEquip", "ExtCmdUnequip"], "", $actionName);
    $GLOBALS["PROMPTS"]["afterfunc"]["cue"][$actionName] = "{$GLOBALS["HERIKA_NAME"]} comments on $actionType a $deviceType on/from {$target}. {$GLOBALS["TEMPLATE_DIALOG"]}";
    
    registerMinAIAction($actionName, $actionInfo["display"])
        ->withDescription($actionInfo["desc"])
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
        ->isNSFW()
        ->withEnableCondition(function() use ($isEquip, $keyword, $keyword2, $beltBlocks) {
            return canModifyDevices($isEquip, $keyword, $keyword2, $beltBlocks);
        })
        ->withReturnFunction($GLOBALS["GenericFuncRet"])
        ->register();
}


