<?php
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
    return $GLOBALS["target_gender"] === "female" || $GLOBALS["herika_gender"] === "female";
}

// Function to check if at least one male actor is present (target or Herika)
function hasAtLeastOneMale() {
    return $GLOBALS["target_gender"] === "male" || $GLOBALS["herika_gender"] === "male";
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


// Check if NSFW is disabled globally
$nsfwDisabled = $GLOBALS["disable_nsfw"];

// Skip all registration if NSFW is disabled
if ($nsfwDisabled) {
    return;
}

// Start timer for conditions evaluation
minai_start_timer('sex_conditions', 'load_module_sex.php');

// Cache enable conditions results to avoid recalculation
$sexEnabled = shouldEnableSexAction();
$activeSexEnabled = $sexEnabled && IsSexActive();
$femaleActionsEnabled = $sexEnabled && hasAtLeastOneFemale();
$maleActionsEnabled = $sexEnabled && hasAtLeastOneMale();
$ostimEnabled = $activeSexEnabled && IsModEnabled("Ostim");

minai_stop_timer('sex_conditions');

// If no sex actions are enabled, skip everything
if (!$sexEnabled) {
    return;
    //error_log("->sex not enabled: " . implode(' . ', $GLOBALS["ENABLED_FUNCTIONS"])); // debug

}

// COMMON SEX ACTIONS
if ($sexEnabled) {
    minai_start_timer('common_sex_actions', 'load_module_sex.php');
    
    // Masturbate
    directRegisterAction(
        "ExtCmdMasturbate", 
        "Masturbate", 
        "Begin pleasuring yourself without a partner - use when alone or to arouse others", 
        true
    );
    
    // StartVaginal
    directRegisterAction(
        "ExtCmdStartVaginal", 
        "StartVaginal", 
        "Initiate vaginal intercourse with the target", 
        true,
        [
            "male-female" => "Initiate vaginal intercourse with #target_possessive# - one of the primary sex actions",
            "female-male" => "Have #target_subject# initiate vaginal intercourse with you - one of the primary sex actions"
        ]
    );
    
    // StartAnal
    directRegisterAction(
        "ExtCmdStartAnal", 
        "StartAnal", 
        "Initiate anal intercourse with the target - an intimate and intense sexual action", 
        true,
        [
            "male-female" => "Initiate anal intercourse with #target_object# - an intimate and intense sexual action",
            "female-male" => "Have #target_subject# initiate anal intercourse with you - an intimate and intense sexual action",
            "male-male" => "Initiate anal intercourse with #target_object# - an intimate and intense sexual action"
        ]
    );
    
    // StartThreesome
    directRegisterAction(
        "ExtCmdStartThreesome", 
        "StartThreesome", 
        "Initiate sexual activity involving three persons simultaneously - use for three-person encounters as a very desirable intimate entertainment. Should chose a target partner as third person. Use this action when The Dragonborn is not involved in another intimate activity", 
        true
    );
    
    // StartOrgy
    directRegisterAction(
        "ExtCmdStartOrgy", 
        "StartOrgy", 
        "Begin group sexual activity with multiple willing participants in the vicinity in groups up to five participants. Should chose 2 or 3 extra partners that are not already involved in intimate activities as targets. Ultimate group bonding intimate activity", 
        true
    );
    
    // PutOnClothes
    directRegisterAction(
        "ExtCmdPutOnClothes", 
        "PutOnClothes", 
        "Dress yourself in available clothing and armor - restores modesty", 
        true
    );
    
    // RemoveClothes
    directRegisterAction(
        "ExtCmdRemoveClothes", 
        "RemoveClothes", 
        "Take off all clothing and armor for intimate activities, unwinding, sleeping, swimming or just get naked to show off the beauty of your body", 
        true
    );
    
    minai_stop_timer('common_sex_actions');
}

// MALE-SPECIFIC ACTIONS
if ($maleActionsEnabled) {
    minai_start_timer('male_sex_actions', 'load_module_sex.php');
    
    // StartBlowjob
    directRegisterAction(
        "ExtCmdStartBlowjob", 
        "StartBlowjob", 
        "Begin oral sex involving the target's or your genitals", 
        true,
        [
            "male-female" => "Have #target_object# perform oral sex on your penis",
            "female-male" => "Perform oral sex on #target_possessive# penis",
            "male-male" => "Perform or receive oral sex on the penis"
        ]
    );
    
    // StartHandjob
    directRegisterAction(
        "ExtCmdStartHandjob", 
        "StartHandjob", 
        "Stimulate genitals using hands - a common foreplay or sex act", 
        true,
        [
            "male-female" => "Have #target_object# stimulate your penis with #target_possessive# hands",
            "female-male" => "Stimulate #target_possessive# penis with your hands",
            "male-male" => "Stimulate #target_possessive# penis with your hands or have #target_object# stimulate yours"
        ]
    );
    
    minai_stop_timer('male_sex_actions');
}

// FEMALE-SPECIFIC ACTIONS
if ($femaleActionsEnabled) {
    minai_start_timer('female_sex_actions', 'load_module_sex.php');
    
    // StartFingering
    directRegisterAction(
        "ExtCmdStartFingering", 
        "StartFingering", 
        "Digitally penetrate and stimulate the vagina - common as foreplay or main act", 
        true,
        [],
        ["target"],
        "Digitally penetrate and stimulate #target_possessive# vagina - common as foreplay or main act"
    );
    
    // StartCunnilingus
    directRegisterAction(
        "ExtCmdStartCunnilingus", 
        "StartCunnilingus", 
        "Perform oral sex on female genitalia - focuses on partner's pleasure", 
        true,
        [],
        ["target"],
        "Perform oral sex on #target_possessive# female genitalia - focuses on #target_possessive# pleasure"
    );
    
    // StartRubbingclitoris
    directRegisterAction(
        "ExtCmdStartRubbingclitoris", 
        "StartRubbingclitoris", 
        "Manually stimulate the clitoris - focuses on female pleasure", 
        $activeSexEnabled,
        [
            "male-female" => "Manually stimulate #target_possessive# clitoris - focuses on #target_possessive# pleasure",
            "female-female" => "Manually stimulate #target_possessive# clitoris - focuses on #target_possessive# pleasure"
        ]
    );
    
    minai_stop_timer('female_sex_actions');
}

// ACTIVE SEX SCENE ACTIONS
if ($activeSexEnabled) {
    minai_start_timer('active_sex_actions', 'load_module_sex.php');
    
    // StartCuddleSex
    directRegisterAction(
        "ExtCmdStartCuddleSex", 
        "StartCuddleSex", 
        "Begin intimate, gentle sex with close body contact - emphasizes emotional connection", 
        true
    );
    
    // StartKissingSex
    directRegisterAction(
        "ExtCmdStartKissingSex", 
        "StartKissingSex", 
        "Begin passionate kissing as foreplay or during sex - builds intimacy and arousal", 
        true
    );
    
    // Start69Sex
    directRegisterAction(
        "ExtCmdStart69Sex", 
        "Start69Sex", 
        "Begin mutual oral sex simultaneously - provides pleasure to both partners", 
        true
    );
    
    // StartGrindingSex
    directRegisterAction(
        "ExtCmdStartGrindingSex", 
        "StartGrindingSex", 
        "Begin rubbing body against genitals - provides stimulation without penetration", 
        true
    );
    
    // StartAggressive
    directRegisterAction(
        "ExtCmdStartAggressive", 
        "StartAggressive", 
        "Transition to more forceful, intense sexual activity - adds intensity to the scene", 
        true
    );
    
    // EndSex
    directRegisterAction(
        "ExtCmdEndSex", 
        "EndSex", 
        "Stop all sexual activity immediately and disengage - use when the scene should conclude", 
        true
    );
    
    // StartRimjob
    directRegisterAction(
        "ExtCmdStartRimjob", 
        "StartRimjob", 
        "Perform oral stimulation of the anus - an intimate and taboo act", 
        true
    );
    
    // StartMissionarySex
    directRegisterAction(
        "ExtCmdStartMissionarySex", 
        "StartMissionarySex", 
        "Begin face-to-face sex with partner on back - the most common position", 
        true,
        [
            "male-female" => "Begin face-to-face sex with #target_object# on #target_possessive# back - the most common position",
            "female-male" => "Begin face-to-face sex with #target_object# on #target_possessive# back - the most common position"
        ]
    );
    
    // StartCowgirlSex
    directRegisterAction(
        "ExtCmdStartCowgirlSex", 
        "StartCowgirlSex", 
        "Begin sex with partner on top, facing forward - gives them control", 
        true,
        [
            "male-female" => "Begin sex with #target_object# on top, facing forward - gives #target_object# control",
            "female-male" => "Begin sex with you on top of #target_object#, facing forward - gives you control"
        ]
    );
    
    // StartReverseCowgirl
    directRegisterAction(
        "ExtCmdStartReverseCowgirl", 
        "StartReverseCowgirl", 
        "Begin sex with partner on top, facing away - a visually exciting position", 
        true,
        [
            "male-female" => "Begin sex with #target_object# on top, facing away from you - a visually exciting position",
            "female-male" => "Begin sex with you on top, facing away from #target_object# - a visually exciting position"
        ]
    );
    
    // StartDoggystyle
    directRegisterAction(
        "ExtCmdStartDoggystyle", 
        "StartDoggystyle", 
        "Begin sex from behind with partner on hands and knees - allows deep penetration", 
        true,
        [
            "male-female" => "Begin sex from behind with #target_object# on hands and knees - allows deep penetration",
            "female-male" => "Begin sex with #target_object# entering you from behind while you're on hands and knees"
        ]
    );
    
    // StartFacesitting
    directRegisterAction(
        "ExtCmdStartFacesitting", 
        "StartFacesitting", 
        "Begin oral sex with partner sitting on your face - demonstrates submission or dominance", 
        true,
        [
            "male-female" => "Begin oral sex with #target_object# sitting on your face",
            "female-male" => "Begin oral sex by sitting on #target_possessive# face"
        ]
    );
    
    minai_stop_timer('active_sex_actions');
}

// MALE + ACTIVE ACTIONS
if ($maleActionsEnabled && $activeSexEnabled) {
    minai_start_timer('male_active_sex_actions', 'load_module_sex.php');
    
    // StartFootjob
    directRegisterAction(
        "ExtCmdStartFootjob", 
        "StartFootjob", 
        "Stimulate genitals using feet - adds variety to sexual encounters", 
        true,
        [
            "male-female" => "Have #target_object# stimulate your penis with #target_possessive# feet",
            "female-male" => "Stimulate #target_possessive# penis with your feet"
        ]
    );
    
    // StartBoobjob
    directRegisterAction(
        "ExtCmdStartBoobjob", 
        "StartBoobjob", 
        "Stimulate genitals between breasts - an intimate non-penetrative act", 
        true,
        [
            "male-female" => "Have #target_object# stimulate your penis between #target_possessive# breasts",
            "female-male" => "Stimulate #target_possessive# penis between your breasts"
        ]
    );
    
    // StartFacial
    directRegisterAction(
        "ExtCmdStartFacial", 
        "StartFacial", 
        "Ejaculate on partner's face - a dominant finishing act", 
        true,
        [
            "male-female" => "Ejaculate on #target_possessive# face - a dominant finishing act",
            "male-male" => "Ejaculate on #target_possessive# face - a dominant finishing act"
        ]
    );
    
    // StartCumonchest
    directRegisterAction(
        "ExtCmdStartCumonchest", 
        "StartCumonchest", 
        "Ejaculate on partner's chest - a common way to finish sexual activity", 
        true,
        [
            "male-female" => "Ejaculate on #target_possessive# chest - a common way to finish sexual activity",
            "male-male" => "Ejaculate on #target_possessive# chest - a common way to finish sexual activity"
        ]
    );
    
    // StartDeepthroat
    directRegisterAction(
        "ExtCmdStartDeepthroat", 
        "StartDeepthroat", 
        "Perform or receive deep oral penetration - an intense sexual act", 
        true,
        [
            "male-female" => "Have #target_object# perform deep oral penetration on your penis",
            "female-male" => "Perform deep oral penetration on #target_possessive# penis",
            "male-male" => "Perform or receive deep oral penetration - an intense sexual act"
        ]
    );
    
    // StartThighjob
    directRegisterAction(
        "ExtCmdStartThighjob", 
        "StartThighjob", 
        "Begin stimulating genitals between thighs - non-penetrative alternative", 
        true,
        [
            "male-female" => "Begin stimulating your penis between #target_possessive# thighs",
            "female-male" => "Begin stimulating #target_possessive# penis between your thighs"
        ]
    );
    
    minai_stop_timer('male_active_sex_actions');
}

// OSTIM ACTIONS
if ($ostimEnabled) {
    minai_start_timer('ostim_actions', 'load_module_sex.php');
    
    // SpeedUpSex
    directRegisterAction(
        "ExtCmdSpeedUpSex", 
        "SpeedUpSex", 
        "Increase the intensity and pace of the current sexual activity - use when you want to escalate", 
        true
    );
    
    // SlowDownSex
    directRegisterAction(
        "ExtCmdSlowDownSex", 
        "SlowDownSex", 
        "Reduce the intensity and pace of the current sexual activity - use when you want a gentler pace", 
        true
    );
    
    minai_stop_timer('ostim_actions');
}
