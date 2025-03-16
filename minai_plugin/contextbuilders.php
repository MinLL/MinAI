<?php
require_once("config.php");
require_once("util.php");
require_once("deviousfollower.php");
require_once("customintegrations.php");
require_once("weather.php");
require_once("reputation.php");
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

Function BuildContext($name) {
  if ($name == "The Narrator") {
    $name = $GLOBALS["PLAYER_NAME"];
  }
  $context = "";
  $context .= GetPhysicalDescription($name);
  $context .= GetUnifiedEquipmentContext($name);
  $context .= GetTattooContext($name);
  $context .= GetArousalContext($name);
  $context .= GetFollowingContext($name);
  $context .= GetFertilityContext($name);
  if (!isset($GLOBALS["HERIKA_TARGET"])) {
      $context .= GetDeviousFollowerContext($name);
      $context .= GetSubmissiveLolaContext($name);
  }
  $context .= GetSurvivalContext($name);
  // Add bounty information if talking to a guard
  if ($name == $GLOBALS["PLAYER_NAME"] || $GLOBALS["HERIKA_NAME"] == "The Narrator") {
    if (IsInFaction($GLOBALS["HERIKA_NAME"], "GuardFaction") || IsInFaction($GLOBALS["HERIKA_NAME"], "Guard Faction") || $GLOBALS["HERIKA_NAME"] == "The Narrator") {
      $context .= GetBountyContext($GLOBALS["PLAYER_NAME"]);
    }
  }
  // Add mind influence context for the narrator only
  if ($GLOBALS["HERIKA_NAME"] == "The Narrator") {
      $mindState = GetMindInfluenceState($name);
      if ($mindState != "normal") {
          $context .= GetMindInfluenceContext($mindState) . "\n";
      }
  }

  return $context;
}

Function GetArousalContext($name) {
  $ret = "";
  $arousal = GetActorValue($name, "arousal");
  if ($arousal != "" && (IsModEnabled("OSL") || IsModEnabled("Aroused"))) {
      $ret .= "{$name}'s sexual arousal level is {$arousal}/100, where 0 is not aroused at all, and 100 is desperate for sex.";
  }
  if ($ret != "")
        $ret .= "\n";
  return $ret;
}

Function GetPhysicalDescription($name) {
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
    $ret  .= GetPenisSize($name);
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}

Function GetPenisSize($name) {
    $tngsize = GetActorValue($name, "tngsize");
    $sizeDescription = "";
    if (HasKeyword($name, "TNG_XL") || ($tngsize == 4)) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_L") || ($tngsize == 3)) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize") || ($tngsize == 2)) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_S") || ($tngsize == 1)) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_XS") || ($tngsize == 0)) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
    if ($sizeDescription != "") {
        return "{$name} has {$sizeDescription}. ";
    }
    return "";
}

Function GetFollowingContext($name) {
  if (IsFollowing($name)) {
    return "{$name} is following, walking, or escorting ".$GLOBALS["PLAYER_NAME"];
  } else {
    return "";
  }
}


