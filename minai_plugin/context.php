<?php
require_once("config.php");
require_once("util.php");
require_once("deviousfollower.php");
require_once("wornequipment.php");
require_once("customintegrations.php");
require_once("weather.php");
require_once("reputation.php");
require_once("submissivelola.php");
require_once("dirtandblood.php");
require_once("fertilitymode.php");

Function BuildContext($name) {
  if ($name == "The Narrator") {
      // The narrator is always talking to the player, don't need to do this
      //return BuildContext($GLOBALS["PLAYER_NAME"]);
      return "";
  }
  $context = "";
  $context .= GetPhysicalDescription($name);
  $context .= GetClothingContext($name);
  $context .= GetDDContext($name);
  $context .= GetArousalContext($name);
  $context .= GetFollowingContext($name);
  $context .= GetFertilityContext($name);
  if (!isset($GLOBALS["HERIKA_TARGET"])) {
      $context .= GetDeviousFollowerContext($name);
      $context .= GetSubmissiveLolaContext($name);
  }
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
  $cuirass = GetActorValue($name, "cuirass", false, true);
  $ret = "";
  
  $eqContext = GetAllEquipmentContext($name);

  $tmp = GetRevealedStatus($name);
  $wearingBottom = $tmp["wearingBottom"];
  $wearingTop = $tmp["wearingTop"];
  $isNarrator = ($GLOBALS["HERIKA_NAME"]  == "The Narrator");
  
  // if $eqContext["context"] not empty, then will set ret
  if (!empty($eqContext["context"])) {
    $ret .= "{$name} is wearing {$eqContext["context"]}";
  } elseif (IsEnabled($name, "isNaked")) {
    $ret .= "{$name} is naked and exposed.\n";
  } elseif (!empty($cuirass)) {
    $ret .= "{$name} is wearing {$cuirass}.\n";
  }

  // Only show detailed clothing info if narrator or the area is revealed
  if ($isNarrator || !$wearingTop) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";
    
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Brabikini")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a bra underneath her other equipment.\n";
    }
  }

  if ($isNarrator || !$wearingBottom) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";
    
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Thong")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a thong underneath her other equipment.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantiesNormal")) {
      $ret .= "{$concealedPrefix}{$name} is wearing plain panties underneath her other equipment.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PantsNormal")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of ordinary pants.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PelvicCurtain")) {
      $ret .= "{$concealedPrefix}{$name}'s pussy is covered only by a sheer curtain of fabric.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_FullSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a full length skirt that goes down to her knees.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MiniSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a short mini-skirt that barely covers her ass. Her underwear or panties are sometimes visible underneath when she moves.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_MicroHotPants")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of short hot-pants that accentuate her ass.\n";
    }
  }

  // Full body outfits only show if narrator or both top and bottom are revealed
  if ($isNarrator || (!$wearingTop && !$wearingBottom)) {
    $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";

    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHarness")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a form-fitting body harness.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_HalfNakedBikini")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of revealing bikini armor.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorHalfNaked")) {
      $ret .= "{$concealedPrefix}{$name} is wearing very revealing attire, leaving them half naked.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "EroticArmor")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a sexy revealing outfit.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorSpendex")) {
      $ret .= "{$concealedPrefix}{$name}'s outfit is made out of latex (Referred to as Ebonite).\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorTransparent")) {
      $ret .= "{$concealedPrefix}{$name}'s outfit is transparent, leaving nothing to the imagination.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorLewdLeotard")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a sheer, revealing leotard leaving very little to the imagination.\n";
    }
    if (HasKeywordAndNotSkip($name, $eqContext, "SLA_ArmorRubber")) {
      $ret .= "{$concealedPrefix}{$name}'s outfit is made out of tight form-fitting rubber (Referred to as Ebonite).\n";
    }
  }

  // Always visible accessories
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_Heels")) {
    $ret .= "{$name} is wearing a set of high-heels.\n";
  }

  // Rest of the piercings code remains unchanged...
  if (!$wearingBottom && HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingVulva")) {
    $ret .= "{$name} has labia piercings.\n";
  }
  if (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingBelly")) {
      $ret .= "{$name} has a navel piercing.\n";
  }
  if (!$wearingTop) {
      if (HasKeyword($name, "zad_DeviousPiercingsNipple")) {
          $ret .= "{$name} is wearing remotely controlled nipple piercings capable of powerful vibration.\n";
      }
      elseif  (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingNipple")) {
          $ret .= "{$name} has nipple piercings.\n";
      }
  }
  if (!$wearingBottom) {
      if (HasKeyword($name, "zad_DeviousPiercingsVaginal")) {
          $ret .= "{$name} is wearing a remotely controlled clitoral ring capable of powerful vibration.\n";
      }
      elseif (HasKeywordAndNotSkip($name, $eqContext, "SLA_PiercingClit")) {
          $ret .= "{$name} has a clit piercing.\n";
      }
  }
  if ($ret != "")
      $ret .= "\n";
  return $ret;
}


Function GetDDContext($name) {
  $ret = "";
  $tmp = GetRevealedStatus($name);
  $wearingBottom = $tmp["wearingBottom"];
  $wearingTop = $tmp["wearingTop"];
  $isNarrator = ($GLOBALS["HERIKA_NAME"] == "The Narrator");
  $cuirass = GetActorValue($name, "cuirass", false, true);
  $concealedPrefix = !empty($cuirass) ? "Concealed by {$cuirass}, " : "";

  // Items that require bottom area to be visible
  if ($isNarrator || !$wearingBottom) {
    if (HasKeyword($name, "zad_DeviousPlugVaginal")) {
      $ret .= "{$concealedPrefix}{$name} has a remotely controlled plug in her pussy capable of powerful vibrations.\n";
    }
    if (HasKeyword($name, "zad_DeviousPlugAnal")) {
      $ret .= "{$concealedPrefix}{$name} has a remotely controlled plug in her ass capable of powerful vibrations.\n";
    }
    if (HasKeyword($name, "zad_DeviousBelt")) {
      $ret .= "{$concealedPrefix}{$name}'s pussy is locked away by a chastity belt, preventing her from touching it or having sex.\n";
    }
  }

  // Items that require top area to be visible
  if ($isNarrator || !$wearingTop) {
    if (HasKeyword($name, "zad_DeviousBra")) {
      $ret .= "{$concealedPrefix}{$name}'s breasts are locked away in a chastity bra.\n";
    }
  }

  // Always visible items (or items that show even with clothing)
  if (HasKeyword($name, "zad_DeviousCollar")) {
    $ret .= "{$name} is wearing a collar marking her as someone's property.\n";
  }
  if (HasKeyword($name, "zad_DeviousArmCuffs")) {
    $ret .= "{$name} is wearing an arm cuff on each arm.\n";
  }
  if (HasKeyword($name, "zad_DeviousLegCuffs")) {
    $ret .= "{$name} is wearing a leg cuff on each leg.\n";
  }

  // Full body restraints - only show if narrator or fully revealed
  if ($isNarrator || (!$wearingTop && !$wearingBottom)) {
    if (HasKeyword($name, "zad_DeviousArmbinder")) {
      $ret .= "{$concealedPrefix}{$name}'s hands are secured behind her back by an armbinder, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousYoke")) {
      $ret .= "{$concealedPrefix}{$name}'s hands and neck are locked in an uncomfortable yoke, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousElbowTie")) {
      $ret .= "{$concealedPrefix}{$name}'s arms are tied behind her back in-a strict elbow tie, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousPetSuit")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a full-body suit made out of shiny latex (Referred to as Ebonite) leaving nothing to the imagination.\n";
    }
    if (HasKeyword($name, "zad_DeviousStraitJacket")) {
      $ret .= "{$concealedPrefix}{$name}'s arms are secured by a strait jacket, leaving her helpless.\n";
    }
    if (HasKeyword($name, "zad_DeviousCorset")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a corset around her waist.\n";
    }
    if (HasKeyword($name, "zad_DeviousHobbleSkirt")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a confining hobble-skirt that is restricting her movements.\n";
    }
    if (HasKeyword($name, "zad_DeviousGloves")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a a pair of locking gloves.\n";
    }
    if (HasKeyword($name, "zad_DeviousSuit")) {
      $ret .= "{$concealedPrefix}{$name} is wearing skin tight body-suit.\n";
    }
    if (HasKeyword($name, "zad_DeviousHarness")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a form-fitting leather harness.\n";
    }
  }

  // Always visible head items
  if (HasKeyword($name, "zad_DeviousHood")) {
    $ret .= "{$name} is wearing a hood over her head.\n";
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
  if (HasKeyword($name, "zad_DeviousBlindfold")) {
    $ret .= "{$name} is blindfolded and cannot see where she is going.\n";
  }
  if (HasKeyword($name, "zad_DeviousAnkleShackles")) {
    $ret .= "{$name} is wearing a set of ankle shackles, restricting her ability to move quickly.\n";
  }

  // Items requiring exposed chest
  if ($isNarrator || !$wearingTop) {
    if (HasKeyword($name, "zad_DeviousClamps")) {
      $ret .= "{$concealedPrefix}{$name} is wearing a set of painful nipple clamps.\n";
    }
  }

  // Vibration status - only show if relevant areas are exposed
  if (CanVibrate($name) && ($isNarrator || (!$wearingTop || !$wearingBottom))) {
    if (IsInFaction($name, "Vibrator Effect Faction")) {
      $ret .= "{$concealedPrefix}{$name}'s vibrator is currently on, and is actively stimulating her.\n";
    } else {
      $ret .= "{$concealedPrefix}{$name}'s vibrator is currently off.\n";
    }
  }

  if ($ret != "")
    $ret .= "\n";
  return $ret;
}

$nearbyActors = GetActorValue("PLAYER", "nearbyActors", true);
// Build context
$new_content = "";

if (!$GLOBALS["disable_nsfw"]) {
  $new_content .= BuildContext(GetTargetActor()) . "\n";
  $new_content .= BuildContext($GLOBALS["HERIKA_NAME"]);
  $new_content .= BuildNSFWReputationContext($GLOBALS["HERIKA_NAME"]) . "\n";
}

// SFW Descriptions
// We're going to scan everyone who is nearby
// for highly visible traits of people, like 
// they reek or are filthy.
bundleSFWContext($new_content);
function bundleSFWContext(&$nc) {
  $utilities = new Utilities();
  // list of local npcs (sans narrator)
  $nc .= "\n";
  $localActors = $utilities->beingsInCloseRange();
  // send localActors list to GetDirtAndBlood so as to make comma seperated lists
  $nc .= GetDirtAndBloodContext($localActors);
  $nc .= BuildSFWReputationContext($GLOBALS["HERIKA_NAME"]);
  $nc .= GetThirdPartyContext();
  $nc .= GetWeatherContext() . "\n";
}

$GLOBALS["HERIKA_PERS"] = $GLOBALS["HERIKA_PERS"] . "\n" . $new_content;

// Clean up context
$locaLastElement=[];
$narratorElements=[];
$sexInfoElements=[];
$physicsInfoElements=[];
foreach ($GLOBALS["contextDataFull"] as $n=>$ctxLine) {
    if (strpos($ctxLine["content"],"#SEX_SCENARIO")!==false) {
        preg_match('/#ID_(\d+)/', $ctxLine["content"], $matches);
        if (!empty($matches)) {
          $threadId = $matches[1];
        } else {
          $threadId = "other";
        }

        if (!isset($locaLastElement[$threadId])) {
          $locaLastElement[$threadId] = []; // Initialize as an array if it doesn't exist
        }
        array_push($locaLastElement[$threadId], $n);
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
// Add support for multithread scenes to keep scene descriptions for all threads
foreach ($locaLastElement as $thredId => $threadCtxLines) {
  if(is_array($threadCtxLines) && !empty($threadCtxLines)) {
    // try to find context #SEX_SCENARIO scene among currently running scenes
    $scene = getScene("", $thredId);
    // We want to keep last context line from 'other' category(if any), or for active scenes. If scene stopeed and not playing anymore we don't want to put it into context
    if($thredId === "other" || isset($scene)) {
      array_pop($threadCtxLines);
    }
    foreach ($threadCtxLines as $n) {
      unset($GLOBALS["contextDataFull"][$n]); 
    }
  }
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


// Cleanup self narrator dialogue to avoid contaminating general context
if ($GLOBALS["minai_processing_input"]) {
    error_log("minai: Cleaning up player input");
    DeleteLastPlayerInput();
}
$GLOBALS["contextDataFull"] = array_values($GLOBALS["contextDataFull"]);

require "/var/www/html/HerikaServer/ext/minai_plugin/command_prompt_custom.php";
?>
