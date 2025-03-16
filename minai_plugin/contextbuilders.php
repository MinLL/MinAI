<?php
/**
 * Legacy Context Builders
 * 
 * This file contains the original context builder functions for backward compatibility.
 * New code should use the modular system in contextbuilders/system_prompt_context.php
 */

require_once("config.php");
require_once("util.php");

// Include dependencies
require_once("deviousfollower.php");
require_once("customintegrations.php");
require_once("weather.php");
// require_once("reputation.php");
require_once("relationship.php");
require_once("submissivelola.php");
require_once("dirtandblood.php");
require_once("exposure.php");
require_once("fertilitymode.php");

// Context builders
require_once("contextbuilders/wornequipment_context.php");
require_once("contextbuilders/crime_context.php");
require_once("contextbuilders/surival_context.php");
require_once("contextbuilders/equipment_context.php");
require_once("contextbuilders/tattoos_context.php");

/**
 * Legacy function for getting arousal context
 * 
 * @param string $name Character name
 * @return string Formatted arousal context
 */
function GetArousalContext($name) {
    $ret = "";
    $arousal = GetActorValue($name, "arousal");
    
    if ($arousal != "" && (IsModEnabled("OSL") || IsModEnabled("Aroused"))) {
        $ret .= "{$name}'s sexual arousal level is {$arousal}/100, where 0 is not aroused at all, and 100 is desperate for sex.";
    }
    
    if ($ret != "") {
        $ret .= "\n";
    }
    
    return $ret;
}

/**
 * Legacy function for getting physical description
 * 
 * @param string $name Character name
 * @return string Formatted physical description
 */
function GetPhysicalDescription($name) {
    $gender = GetActorValue($name, "gender");
    $race = GetActorValue($name, "race");
    $beautyScore = GetActorValue($name, "beautyScore");
    $breastsScore = GetActorValue($name, "breastsScore");
    $buttScore = GetActorValue($name, "buttScore");
    $isexposed = GetActorValue($name, "isexposed");
    $ret = "";
    $isWerewolf = false;
    
    if ($gender != "" && $race != "") {
        $ret .= "{$name} is a {$gender} {$race}. ";
        if ($race == "werewolf") {
            $isWerewolf = true;
            $ret .= "{$name} is currently transformed into a terrifying werewolf! ";
        }
    }
    
    if (!IsPlayer($name)) {
        return $ret;
    }
    
    if (!empty($beautyScore) && $beautyScore != "0" && !$isWerewolf) {
        $beautyScore = ceil(intval($beautyScore)/10);
        $ret .= "She is a {$beautyScore}/10 in terms of beauty ";
    }
    
    if((!empty($breastsScore) && $breastsScore != "0") && (!empty($buttScore) && $buttScore != "0") && !$isWerewolf) {
        $breastsScore = ceil(intval($breastsScore)/10);
        $buttScore = ceil(intval($buttScore)/10);
        $ret .= "with {$breastsScore}/10 tits and a {$buttScore}/10 ass. ";
    }
    
    if (IsEnabled($name, "isexposed")) {
        $ret .= GetPenisSize($name);
    }
    
    if ($ret != "") {
        $ret .= "\n";
    }
    
    return $ret;
}

/**
 * Legacy function for getting following context
 * 
 * @param string $name Character name
 * @return string Formatted following context
 */
function GetFollowingContext($name) {
    if (IsFollowing($name) && isset($GLOBALS["PLAYER_NAME"])) {
        return "{$name} is following, walking, or escorting ".$GLOBALS["PLAYER_NAME"];
    } else {
        return "";
    }
}