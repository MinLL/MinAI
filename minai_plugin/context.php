<?php
require_once("config.php");
require_once("util.php");
require_once("deviousfollower.php");
require_once("wornequipment.php");
require_once("customintegrations.php");
require_once("weather.php");
Function BuildContext($name) {
  if ($name == "The Narrator") {
    return "";
  }
  $context = "";
  $context .= GetPhysicalDescription($name);
  $context .= GetClothingContext($name);
  $context .= GetDDContext($name);
  $context .= GetArousalContext($name);
  $context .= GetFollowingContext($name);
  if (!isset($GLOBALS["HERIKA_TARGET"]))
      $context .= GetDeviousFollowerContext($name);
  $context .= GetSurvivalContext($name);

  return $context;
}

Function GetSurvivalContext($name) {
    $ret = "";
    if (!IsPlayer($name) || !IsModEnabled("Sunhelm")) {
        return $ret;
    }
    $hunger = GetActorValue($name, "hunger");
    $thirst = GetActorValue($name, "thirst");
    $fatigue = GetActorValue($name, "fatigue");
    if ($hunger > 0) {
        $hunger = (floatval($hunger)/5) * 100;
        $ret .= "{$name}'s hunger level is at {$hunger}%. ";
    }
    if ($thirst > 0) {
        $thirst = (floatval($thirst)/5) * 100;
        $ret .= "{$name}'s thirst level is at {$thirst}%. ";
    }
    if ($fatigue > 0) {
        $fatigue = (floatval($fatigue)/5) * 100;
        $ret .= "{$name}'s fatigue level is at {$fatigue}%. ";
    }
    if ($ret != "")
        $ret .= "\n";
    return $ret;
}

Function GetArousalContext($name) {
  $ret = "";
  $arousal = GetActorValue($name, "arousal");
  if ($arousal != "") {
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
    $sizeDescription = "";
    if (HasKeyword($name, "TNG_ActorAddnAuto:05")) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_ActorAddnAuto:04")) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:03")) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:02")) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_ActorAddnAuto:01")) {
        $sizeDescription = "an embarrassingly tiny prick";
    }
    elseif (HasKeyword($name, "TNG_XL")) {
        $sizeDescription = "one of the biggest cocks you've ever seen";
    }
    elseif(HasKeyword($name, "TNG_L")) {
        $sizeDescription = "a large cock";
    }
    elseif (HasKeyword($name, "TNG_M") || HasKeyword($name, "TNG_DefaultSize")) {
        $sizeDescription = "an average sized cock";
    }
    elseif (HasKeyword($name, "TNG_S")) {
        $sizeDescription = "a very small cock";
    }        
    elseif (HasKeyword($name, "TNG_XS")) {
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

Function HasKeywordAndNotSkip($name, $eqContext, $keyword) {
  return HasKeyword($name, $keyword) && !IsSkipKeyword($keyword, $eqContext["skipKeywords"]);
}

Function GetClothingContext($name) {
  $cuirass = GetActorValue($name, "cuirass");
  $ret = "";
  
  $eqContext = GetAllEquipmentContext($name);

  // if $eqContext["context"] not empty, then will set ret
  if (!empty($eqContext["context"])) {
    $ret .= "{$name} is wearing {$eqContext["context"]}";
  } elseif (!empty($cuirass)) {
    $ret .= "{$name} is wearing {$cuirass}.\n";
  }
  elseif (IsEnabled($name, "isNaked")) {
    $ret .= "{$name} is naked and exposed.\n";
  }

  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_HalfNakedBikini")) {
    $ret .= "{$name} is wearing a set of revealing bikini armor.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHalfNaked")) {
    $ret .= "{$name} is wearing very revealing attire, leaving them half naked.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Brabikini" )) {
    $ret .= "{$name} is wearing a bra underneath her other equipment.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Thong")) {
    $ret .= "{$name} is wearing a thong underneath her other equipment.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantiesNormal")) {
    $ret .= "{$name} is wearing plain panties underneath her other equipment.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Heels")) {
    $ret .= "{$name} is wearing a set of high-heels.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantsNormal")) {
    $ret .= "{$name} is wearing a set of ordinary pants.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MicroHotPants")) {
    $ret .= "{$name} is wearing a set of short hot-pants that accentuate her ass.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHarness")) {
    $ret .= "{$name} is wearing a form-fitting body harness.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorSpendex")) {
    $ret .= "{$name}'s outfit is made out of latex (Referred to as Ebonite).\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorTransparent")) {
    $ret .= "{$name}'s outfit is transparent, leaving nothing to the imagination.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorLewdLeotard")) {
    $ret .= "{$name} is wearing a sheer, revealing leotard leaving very little to the imagination.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PelvicCurtain")) {
    $ret .= "{$name}'s pussy is covered only by a sheer curtain of fabric.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_FullSkirt")) {
    $ret .= "{$name} is wearing a full length skirt that goes down to her knees.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MiniSkirt")) {
    $ret .= "{$name} is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorRubber")) {
    $ret .= "{$name}'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "EroticArmor")) {
      $ret .= "{$name} is wearing a sexy revealing outfit.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingVulva")) {
      $ret .= "{$name} has labia piercings.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingBelly")) {
      $ret .= "{$name} has a navel piercing.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingNipple")) {
      $ret .= "{$name} has nipple piercings.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingClit")) {
      $ret .= "{$name} has a clit piercing.\n";
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}


Function GetDDContext($name) {
  $ret = "";
  if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
    $ret .= "{$name} has a remotely controlled plug in her pussy capable of powerful vibrations.\n";
  }
  if (HasKeyword($name, "zad_DeviousPlugAnal")) {
    $ret .= "{$name} has a remotely controlled plug in her ass capable of powerful vibrations.\n";
  }
  if (HasKeyword($name, "zad_DeviousBelt")) {
    $ret .= "{$name}'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.\n";
  }
  if (HasKeyword($name, "zad_DeviousCollar")) {
    $ret .= "{$name} is wearing a collar marking her as someone's property.\n";
  }
  if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
    $ret .= "{$name} is wearing remotely controlled nipple piercings capable of powerful vibration.\n";
  }
  if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
    $ret .= "{$name} is wearing a remotely controlled clitoral ring capable of powerful vibration.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmCuffs")) {
    $ret .= "{$name} is wearing an arm cuff on each arm.\n";
  }
  if (HasKeyword($name, "zad_DeviousLegCuffs")) {
    $ret .= "{$name} is wearing a leg cuff on each leg.\n";
  }
  if (HasKeyword($name, "zad_DeviousBra")) {
    $ret .= "{$name}'s breasts are locked away in a chastity bra.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmbinder")) {
    $ret .= "{$name}'s hands are secured behind her back by an armbinder, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousYoke")) {
    $ret .= "{$name}'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousElbowTie")) {
    $ret .= "{$name}'s arms are tied behind her back in-a strict elbow tie, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousPetSuit")) {
    $ret .= "{$name} is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.\n";
  }
  if (HasKeyword($name, "zad_DeviousStraitJacket")) {
    $ret .= "{$name}'s arms are secured by a strait jacket, leaving her helpless.\n";
  }
  if (HasKeyword($name, "zad_DeviousCorset")) {
    $ret .= "{$name} is wearing a corset around her waist.\n";
  }
  if (HasKeyword($name, "zad_DeviousHood")) {
    $ret .= "{$name} is wearing a hood over her head.\n";
  }
  if (HasKeyword($name, "zad_DeviousHobbleSkirt")) {
    $ret .= "{$name} is wearing a confining hobble-skirt that is restricting her movements.\n";
  }
  if (HasKeyword($name, "zad_DeviousGloves")) {
    $ret .= "{$name} is wearing a a pair of locking gloves.\n";
  }
  if (HasKeyword($name, "zad_DeviousSuit")) {
    $ret .= "{$name} is wearing skin tight body-suit.\n";
  }
  if (HasKeyword($name, "zad_DeviousGag")) {
    $ret .= "{$name} is gagged and is drooling.\n";
  }
  if (HasKeyword($name, "zad_DeviousGagPanel")) {
    $ret .= "{$name} is gagged with a panel-gag that leaves her tongue exposed and is unable to close their mouth.\n";
  }
  if (HasKeyword($name, "zad_DeviousGagLarge")) {
    $ret .= "{$name} is gagged with a large gag and cannot speak clearly.\n";
  }
  if (HasKeyword($name, "zad_DeviousHarness")) {
    $ret .= "{$name} is wearing a form-fitting leather harness.\n";
  }
  if (HasKeyword($name, "zad_DeviousBlindfold")) {
    $ret .= "{$name} is blindfolded and cannot see where she is going.\n";
  }
  if (HasKeyword($name, "zad_DeviousAnkleShackles")) {
    $ret .= "{$name} is wearing a set of ankle shackles, restricting her ability to move quickly.\n";
  }
  if (HasKeyword($name, "zad_DeviousClamps")) {
    $ret .= "{$name} is wearing a set of painful nipple clamps.\n";
  }
  if (CanVibrate($name)) {
      if (IsInFaction($name, "Vibrator Effect Faction")) {
          $ret .= "{$name}'s vibrator is currently on, and is actively stimulating her.\n";
      }
      else {
          $ret .= "{$name}'s vibrator is currently off.\n";
      }
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}


// Build context
if (!$GLOBALS["disable_nsfw"]) {
    $GLOBALS["COMMAND_PROMPT"].= BuildContext(GetTargetActor());
    $GLOBALS["COMMAND_PROMPT"].= BuildContext($GLOBALS["HERIKA_NAME"]);
    $GLOBALS["COMMAND_PROMPT"].= GetThirdPartyContext();
    $GLOBALS["COMMAND_PROMPT"].= GetWeatherContext();
    $nearbyActors = GetActorValue("PLAYER", "nearbyActors", true);
    // This does work, I just need to figure out how to get a bit of the bio + relevant context to insert into the full context for this to work properly. TODO
    /*if ($nearbyActors) {
        $nearbyActors = explode(',', $nearbyActors);
        
        foreach ($nearbyActors as $actor) {
            if ($actor != $GLOBALS["HERIKA_NAME"] && $actor != $GLOBALS["PLAYER_NAME"]) {
                $profile = md5($GLOBALS["HERIKA_NAME"]);

                $GLOBALS["COMMAND_PROMPT"] .= BuildContext($actor);
            }
        }
        }*/
    $GLOBALS["COMMAND_PROMPT"].="

";
}

// Clean up context
$locaLastElement=[];
$narratorElements=[];
$sexInfoElements=[];
$physicsInfoElements=[];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    if (strpos($ctxLine["content"],"#SEX_SCENARIO")!==false) {
        $locaLastElement[]=$n;
    }
    if ($GLOBALS["stop_narrator_context_leak"] && $GLOBALS["HERIKA_NAME"] != "The Narrator") {
        if (strpos($ctxLine["content"],"The Narrator:")!==false && strpos($ctxLine["content"],"(talking to")!==false) {
            $narratorElements[]=$n;
        }
    }
    if (strpos($ctxLine["content"],"#SEX_INFO")!==false) {
        $sexInfoElements[]=$n;
    }
    if (strpos($ctxLine["content"],"#PHYSICS_INFO")!==false) {
        $physicsInfoElements[]=$n;
    }
}
// Remove all references to sex scene, and only keep the last one.
array_pop($locaLastElement);
foreach ($locaLastElement as $n) {
  unset($GLOBALS["contextDataFull"][$n]); 
}

// Cleanup narrator context for non-narrator actors
foreach ($narratorElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to sex scene info, and only keep the last one.
array_pop($sexInfoElements);
foreach ($sexInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

// Remove all references to physics / collision info, and only keep the last three.
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
array_pop($physicsInfoElements);
foreach ($physicsInfoElements as $n) {
    unset($GLOBALS["contextDataFull"][$n]); 
}

/*$nullValues = [];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    error_log("minai: Checking ({$n}) {$ctxLine["content"]}");
    if (!$ctxLine["content"] || $ctxLine["content"] == null || $ctxLine["content"]  == "") {
        $nullValues[] = $n;
        error_log("minai: Found null value in context ({$n})");
    }
}
foreach ($nullValues as $n) {
    error_log("minai: Unsetting null value $n");
    unset($GLOBALS["contextDataFull"][$n]); 
}*/

$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);


require "command_prompt_custom.php";
?>
