<?php


// Get global variables for target actor and the AI name
$target = $GLOBALS["target"];
$actorName = $GLOBALS["HERIKA_NAME"];

// Function to check if sex actions should be enabled
function shouldEnableSexAction() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']);
}

// Function to check if sex actions are enabled, extended for sex scene
function shouldEnableActiveSexAction() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && IsSexActive();
}

// Function to check if at least one female actor is present (target or Herika)
function hasAtLeastOneFemale() {
    return IsFemale(GetTargetActor()) || IsFemale($GLOBALS['HERIKA_NAME']);
}

// Function to check if at least one male actor is present (target or Herika)
function hasAtLeastOneMale() {
    return IsMale(GetTargetActor()) || IsMale($GLOBALS['HERIKA_NAME']);
}

// Function to check if female-specific sex actions should be enabled
function shouldEnableFemaleAction() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && hasAtLeastOneFemale();
}

// Function to check if male-specific sex actions should be enabled
function shouldEnableMaleAction() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && hasAtLeastOneMale();
}

// Function to check if OStim speed actions should be enabled
function shouldEnableOStimAction() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && IsSexActive() && IsModEnabled("Ostim");
}

// Function to check if the stop following action should be enabled
function shouldEnableStopFollowing() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && 
           !IsFollower($GLOBALS['HERIKA_NAME']) && 
           !IsRadiant() && 
           IsFollowing($GLOBALS['HERIKA_NAME']);
}

// Function to check if the follow target action should be enabled
function shouldEnableFollowTarget() {
    return ShouldEnableSexFunctions($GLOBALS['HERIKA_NAME']) && 
           !IsFollower($GLOBALS['HERIKA_NAME']) && 
           !IsRadiant() && 
           !IsFollowing($GLOBALS['HERIKA_NAME']);
}

// Map actions to their appropriate enable conditions based on gender requirements
$actionEnableConditions = [
    // Male-specific actions (need at least one male actor)
    "ExtCmdStartBlowjob" => "shouldEnableMaleAction",
    "ExtCmdStartHandjob" => "shouldEnableMaleAction",
    "ExtCmdStartBoobjob" => "shouldEnableMaleAction",
    "ExtCmdStartFootjob" => "shouldEnableMaleAction",
    "ExtCmdStartFacial" => "shouldEnableMaleAction",
    "ExtCmdStartCumonchest" => "shouldEnableMaleAction",
    "ExtCmdStartDeepthroat" => "shouldEnableMaleAction",
    "ExtCmdStartThighjob" => "shouldEnableMaleAction",
    
    // Female-specific actions (need at least one female actor)
    "ExtCmdStartFingering" => "shouldEnableFemaleAction",
    "ExtCmdStartCunnilingus" => "shouldEnableFemaleAction",
    "ExtCmdStartRubbingclitoris" => "shouldEnableFemaleAction",
    
    // General actions (no specific gender requirement)
    "ExtCmdMasturbate" => "shouldEnableSexAction",
    "ExtCmdStartVaginal" => "shouldEnableSexAction",
    "ExtCmdStartAnal" => "shouldEnableSexAction",
    "ExtCmdStartThreesome" => "shouldEnableSexAction",
    "ExtCmdStartOrgy" => "shouldEnableSexAction",
    "ExtCmdPutOnClothes" => "shouldEnableSexAction",
    "ExtCmdRemoveClothes" => "shouldEnableSexAction"
];

// Common sex actions that are always enabled when sex actions are allowed
$commonSexActions = [
    "ExtCmdMasturbate" => [
        "display" => "Masturbate",
        "desc" => "Begin pleasuring yourself without a partner - use when alone or to arouse others"
    ],
    "ExtCmdStartVaginal" => [
        "display" => "StartVaginal",
        "desc" => "Initiate vaginal intercourse with the target - one of the primary sex actions",
        "gender" => [
            "male-female" => "Initiate vaginal intercourse with #target_possessive# - one of the primary sex actions",
            "female-male" => "Have #target_subject# initiate vaginal intercourse with you - one of the primary sex actions"
        ]
    ],
    "ExtCmdStartAnal" => [
        "display" => "StartAnal",
        "desc" => "Initiate anal intercourse with the target - an intimate and intense sexual action",
        "gender" => [
            "male-female" => "Initiate anal intercourse with #target_object# - an intimate and intense sexual action",
            "female-male" => "Have #target_subject# initiate anal intercourse with you - an intimate and intense sexual action",
            "male-male" => "Initiate anal intercourse with #target_object# - an intimate and intense sexual action"
        ]
    ],
    "ExtCmdStartBlowjob" => [
        "display" => "StartBlowjob",
        "desc" => "Begin oral sex involving the target's or your genitals",
        "gender" => [
            "male-female" => "Have #target_object# perform oral sex on your penis",
            "female-male" => "Perform oral sex on #target_possessive# penis",
            "male-male" => "Perform or receive oral sex on the penis"
        ]
    ],
    "ExtCmdStartHandjob" => [
        "display" => "StartHandjob",
        "desc" => "Stimulate genitals using hands - a common foreplay or sex act",
        "gender" => [
            "male-female" => "Have #target_object# stimulate your penis with #target_possessive# hands",
            "female-male" => "Stimulate #target_possessive# penis with your hands",
            "male-male" => "Stimulate #target_possessive# penis with your hands or have #target_object# stimulate yours"
        ]
    ],
    "ExtCmdStartThreesome" => [
        "display" => "StartThreesome",
        "desc" => "Initiate sexual activity with multiple partners simultaneously - use for three-person encounters"
    ],
    "ExtCmdStartOrgy" => [
        "display" => "StartOrgy",
        "desc" => "Begin group sexual activity with multiple willing participants in the vicinity"
    ],
    "ExtCmdPutOnClothes" => [
        "display" => "PutOnClothes",
        "desc" => "Dress yourself in available clothing and armor - restores modesty"
    ],
    "ExtCmdRemoveClothes" => [
        "display" => "RemoveClothes",
        "desc" => "Take off all clothing and armor - necessary for intimate activities"
    ]
];

// Register all common sex actions
foreach ($commonSexActions as $actionName => $actionInfo) {
    $action = registerMinAIAction($actionName, $actionInfo["display"])
        ->withDescription($actionInfo["desc"])
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
        ->isNSFW()
        ->withEnableCondition(isset($actionEnableConditions[$actionName]) ? $actionEnableConditions[$actionName] : 'shouldEnableSexAction')
        ->withReturnFunction($GLOBALS["GenericFuncRet"]);
    
    // Add gender-specific descriptions if available
    if (isset($actionInfo["gender"])) {
        foreach ($actionInfo["gender"] as $genderCombo => $genderDesc) {
            list($speakerGender, $targetGender) = explode('-', $genderCombo);
            $action->withGenderDescription($speakerGender, $targetGender, $genderDesc);
        }
    }
    
    $action->register();
}

// Register "Stop Following" action using the action builder
registerMinAIAction("ExtCmdStopFollowing", "StopFollowing")
    ->withDescription("Cease following the target - use when you want to remain in current location")
    ->withParameter("target", "string", "Target Actor", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
    ->isNSFW()
    ->withEnableCondition('shouldEnableStopFollowing')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register "Follow Target" action using the action builder
registerMinAIAction("ExtCmdFollow", "FollowTarget")
    ->withDescription("Start following the target to a new location - use when you want to accompany them")
    ->withParameter("target", "string", "Target Actor", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
    ->isNSFW()
    ->withEnableCondition('shouldEnableFollowTarget')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register female-specific action: Fingering
registerMinAIAction("ExtCmdStartFingering", "StartFingering")
    ->withDescription("Digitally penetrate and stimulate the vagina - common as foreplay or main act")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
    ->isNSFW()
    ->withEnableCondition(isset($actionEnableConditions["ExtCmdStartFingering"]) ? $actionEnableConditions["ExtCmdStartFingering"] : 'shouldEnableFemaleAction')
    ->withDescription("Digitally penetrate and stimulate #target_possessive# vagina - common as foreplay or main act")
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Register female-specific action: Cunnilingus
registerMinAIAction("ExtCmdStartCunnilingus", "StartCunnilingus")
    ->withDescription("Perform oral sex on female genitalia - focuses on partner's pleasure")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [], true)
    ->isNSFW()
    ->withEnableCondition(isset($actionEnableConditions["ExtCmdStartCunnilingus"]) ? $actionEnableConditions["ExtCmdStartCunnilingus"] : 'shouldEnableFemaleAction')
    ->withDescription("Perform oral sex on #target_possessive# female genitalia - focuses on #target_possessive# pleasure")
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();

// Active sex scene actions
$activeSexActions = [
    "ExtCmdStartCuddleSex" => [
        "display" => "StartCuddleSex",
        "desc" => "Begin intimate, gentle sex with close body contact - emphasizes emotional connection"
    ],
    "ExtCmdStartKissingSex" => [
        "display" => "StartKissingSex",
        "desc" => "Begin passionate kissing as foreplay or during sex - builds intimacy and arousal"
    ],
    "ExtCmdStartFootjob" => [
        "display" => "StartFootjob",
        "desc" => "Stimulate genitals using feet - adds variety to sexual encounters",
        "gender" => [
            "male-female" => "Have #target_object# stimulate your penis with #target_possessive# feet",
            "female-male" => "Stimulate #target_possessive# penis with your feet"
        ]
    ],
    "ExtCmdStartBoobjob" => [
        "display" => "StartBoobjob",
        "desc" => "Stimulate genitals between breasts - an intimate non-penetrative act",
        "gender" => [
            "male-female" => "Have #target_object# stimulate your penis between #target_possessive# breasts",
            "female-male" => "Stimulate #target_possessive# penis between your breasts"
        ]
    ],
    "ExtCmdStartFacial" => [
        "display" => "StartFacial",
        "desc" => "Ejaculate on partner's face - a dominant finishing act",
        "gender" => [
            "male-female" => "Ejaculate on #target_possessive# face - a dominant finishing act",
            "male-male" => "Ejaculate on #target_possessive# face - a dominant finishing act"
        ]
    ],
    "ExtCmdStartCumonchest" => [
        "display" => "StartCumonchest",
        "desc" => "Ejaculate on partner's chest - a common way to finish sexual activity",
        "gender" => [
            "male-female" => "Ejaculate on #target_possessive# chest - a common way to finish sexual activity",
            "male-male" => "Ejaculate on #target_possessive# chest - a common way to finish sexual activity"
        ]
    ],
    "ExtCmdStartRubbingclitoris" => [
        "display" => "StartRubbingclitoris",
        "desc" => "Manually stimulate the clitoris - focuses on female pleasure",
        "gender" => [
            "male-female" => "Manually stimulate #target_possessive# clitoris - focuses on #target_possessive# pleasure",
            "female-female" => "Manually stimulate #target_possessive# clitoris - focuses on #target_possessive# pleasure"
        ]
    ],
    "ExtCmdStartDeepthroat" => [
        "display" => "StartDeepthroat",
        "desc" => "Perform or receive deep oral penetration - an intense sexual act",
        "gender" => [
            "male-female" => "Have #target_object# perform deep oral penetration on your penis",
            "female-male" => "Perform deep oral penetration on #target_possessive# penis",
            "male-male" => "Perform or receive deep oral penetration - an intense sexual act"
        ]
    ],
    "ExtCmdStartRimjob" => [
        "display" => "StartRimjob",
        "desc" => "Perform oral stimulation of the anus - an intimate and taboo act"
    ],
    "ExtCmdStartMissionarySex" => [
        "display" => "StartMissionarySex",
        "desc" => "Begin face-to-face sex with partner on back - the most common position",
        "gender" => [
            "male-female" => "Begin face-to-face sex with #target_object# on #target_possessive# back - the most common position",
            "female-male" => "Begin face-to-face sex with #target_object# on #target_possessive# back - the most common position"
        ]
    ],
    "ExtCmdStartCowgirlSex" => [
        "display" => "StartCowgirlSex",
        "desc" => "Begin sex with partner on top, facing forward - gives them control",
        "gender" => [
            "male-female" => "Begin sex with #target_object# on top, facing forward - gives #target_object# control",
            "female-male" => "Begin sex with you on top of #target_object#, facing forward - gives you control"
        ]
    ],
    "ExtCmdStartReverseCowgirl" => [
        "display" => "StartReverseCowgirl",
        "desc" => "Begin sex with partner on top, facing away - a visually exciting position",
        "gender" => [
            "male-female" => "Begin sex with #target_object# on top, facing away from you - a visually exciting position",
            "female-male" => "Begin sex with you on top, facing away from #target_object# - a visually exciting position"
        ]
    ],
    "ExtCmdStartDoggystyle" => [
        "display" => "StartDoggystyle",
        "desc" => "Begin sex from behind with partner on hands and knees - allows deep penetration",
        "gender" => [
            "male-female" => "Begin sex from behind with #target_object# on hands and knees - allows deep penetration",
            "female-male" => "Begin sex with #target_object# entering you from behind while you're on hands and knees"
        ]
    ],
    "ExtCmdStartFacesitting" => [
        "display" => "StartFacesitting",
        "desc" => "Begin oral sex with partner sitting on your face - demonstrates submission or dominance",
        "gender" => [
            "male-female" => "Begin oral sex with #target_object# sitting on your face",
            "female-male" => "Begin oral sex by sitting on #target_possessive# face"
        ]
    ],
    "ExtCmdStart69Sex" => [
        "display" => "Start69Sex",
        "desc" => "Begin mutual oral sex simultaneously - provides pleasure to both partners"
    ],
    "ExtCmdStartGrindingSex" => [
        "display" => "StartGrindingSex",
        "desc" => "Begin rubbing body against genitals - provides stimulation without penetration"
    ],
    "ExtCmdStartThighjob" => [
        "display" => "StartThighjob",
        "desc" => "Begin stimulating genitals between thighs - non-penetrative alternative",
        "gender" => [
            "male-female" => "Begin stimulating your penis between #target_possessive# thighs",
            "female-male" => "Begin stimulating #target_possessive# penis between your thighs"
        ]
    ],
    "ExtCmdStartAggressive" => [
        "display" => "StartAggressive",
        "desc" => "Transition to more forceful, intense sexual activity - adds intensity to the scene"
    ],
    "ExtCmdEndSex" => [
        "display" => "EndSex",
        "desc" => "Stop all sexual activity immediately and disengage - use when the scene should conclude"
    ]
];

// Register active sex scene actions
foreach ($activeSexActions as $actionName => $actionInfo) {
    // Determine the appropriate enable condition
    $enableCondition = 'shouldEnableActiveSexAction';
    
    // Check if this action has a gender-specific condition
    if (isset($actionEnableConditions[$actionName])) {
        // Custom function that combines active sex scene and gender conditions
        $enableCondition = function() use ($actionName, $actionEnableConditions) {
            $genderCondition = $actionEnableConditions[$actionName];
            return shouldEnableActiveSexAction() && call_user_func($genderCondition);
        };
    }
    
    $action = registerMinAIAction($actionName, $actionInfo["display"])
        ->withDescription($actionInfo["desc"])
        ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
        ->isNSFW()
        ->withEnableCondition($enableCondition)
        ->withReturnFunction($GLOBALS["GenericFuncRet"]);
    
    // Add gender-specific descriptions if available
    if (isset($actionInfo["gender"])) {
        foreach ($actionInfo["gender"] as $genderCombo => $genderDesc) {
            list($speakerGender, $targetGender) = explode('-', $genderCombo);
            $action->withGenderDescription($speakerGender, $targetGender, $genderDesc);
        }
    }
    
    $action->register();
}

// Speed control for OStim scenes
registerMinAIAction("ExtCmdSpeedUpSex", "SpeedUpSex")
    ->withDescription("Increase the intensity and pace of the current sexual activity - use when you want to escalate")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
    ->isNSFW()
    ->withEnableCondition('shouldEnableOStimAction')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();
    
registerMinAIAction("ExtCmdSlowDownSex", "SlowDownSex")
    ->withDescription("Reduce the intensity and pace of the current sexual activity - use when you want a gentler pace")
    ->withParameter("target", "string", "Target NPC, Actor, or being", isset($GLOBALS["nearby"]) ? $GLOBALS["nearby"] : [])
    ->isNSFW()
    ->withEnableCondition('shouldEnableOStimAction')
    ->withReturnFunction($GLOBALS["GenericFuncRet"])
    ->register();
